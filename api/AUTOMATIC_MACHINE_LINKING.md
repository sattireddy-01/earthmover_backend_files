# Automatic Machine Linking Documentation

## Overview
When an operator submits license details, the system automatically links the operator to a machine in the `machines` table. This happens through a database trigger that fires automatically when the `operators` table is updated.

## How It Works

### 1. Database Trigger
The trigger `update_machine_from_operator` is defined on the `operators` table and fires **AFTER UPDATE**.

**Location:** Run the SQL file: `ensure_machine_linking_trigger.sql`

**Trigger Logic:**
1. Checks if `category_id` and `equipment_type` are NOT NULL (license details must be provided)
2. First, tries to update an existing machine already linked to this operator
3. If no machine is linked (`ROW_COUNT() = 0`), automatically links an available machine from the same category
4. Updates all machine fields with operator data:
   - `phone`
   - `address`
   - `equipment_type`
   - `machine_model`
   - `machine_year`
   - `machine_image_1`
   - `availability`
   - `profile_image`

### 2. PHP Code Flow
**File:** `api/operator/save_license_details.php`

**Process:**
1. Operator submits license details (license_no, rc_number, equipment_type, machine_model, etc.)
2. PHP code performs a **single UPDATE** on the `operators` table with all fields
3. The database trigger automatically fires and links/updates the machine
4. PHP code also performs a backup manual update (redundancy for safety)

### 3. Machine Linking Rules
- **Category Matching:** Machine is linked based on `category_id` matching
- **Availability:** Only machines with `operator_id IS NULL` are available for linking
- **Priority:** If operator already has a linked machine, it updates that machine instead of linking a new one
- **Limit:** Only one machine is linked per operator (LIMIT 1)

## Setup Instructions

### Step 1: Ensure Trigger Exists
Run the SQL script in phpMyAdmin:
```sql
-- File: ensure_machine_linking_trigger.sql
```

Or manually execute:
```sql
DELIMITER $$

DROP TRIGGER IF EXISTS `update_machine_from_operator`;

CREATE TRIGGER `update_machine_from_operator` 
AFTER UPDATE ON `operators`
FOR EACH ROW
BEGIN
    DECLARE rows_affected INT DEFAULT 0;
    
    IF NEW.category_id IS NOT NULL AND NEW.equipment_type IS NOT NULL THEN
        UPDATE `machines`
        SET 
            phone = NEW.phone,
            address = NEW.address,
            equipment_type = NEW.equipment_type,
            machine_model = NEW.machine_model,
            machine_year = NEW.machine_year,
            machine_image_1 = NEW.machine_image_1,
            availability = NEW.availability,
            profile_image = NEW.profile_image
        WHERE operator_id = NEW.operator_id;
        
        SET rows_affected = ROW_COUNT();
        
        IF rows_affected = 0 THEN
            UPDATE `machines`
            SET 
                operator_id = NEW.operator_id,
                phone = NEW.phone,
                address = NEW.address,
                equipment_type = NEW.equipment_type,
                machine_model = NEW.machine_model,
                machine_year = NEW.machine_year,
                machine_image_1 = NEW.machine_image_1,
                availability = NEW.availability,
                profile_image = NEW.profile_image
            WHERE category_id = NEW.category_id 
            AND operator_id IS NULL
            LIMIT 1;
        END IF;
    END IF;
END$$

DELIMITER ;
```

### Step 2: Verify Trigger
Check if trigger exists:
```sql
SHOW TRIGGERS LIKE 'operators';
```

You should see `update_machine_from_operator` listed.

## Testing

### Test Case 1: New Operator with License Details
1. Register a new operator (basic info only)
2. Submit license details with `equipment_type` and `category_id`
3. **Expected Result:** Machine is automatically linked in `machines` table

### Test Case 2: Operator Updates License Details
1. Operator already has linked machine
2. Update license details (e.g., change machine_model)
3. **Expected Result:** Linked machine is updated with new details

### Test Case 3: Verify Linking
```sql
-- Check if operator is linked to a machine
SELECT o.operator_id, o.name, o.equipment_type, o.category_id,
       m.machine_id, m.operator_id, m.category_id, m.machine_model
FROM operators o
LEFT JOIN machines m ON o.operator_id = m.operator_id
WHERE o.operator_id = [YOUR_OPERATOR_ID];
```

## Troubleshooting

### Issue: Machine Not Linking
**Possible Causes:**
1. Trigger doesn't exist - Run `ensure_machine_linking_trigger.sql`
2. `category_id` or `equipment_type` is NULL - Ensure both are set during license submission
3. No available machines in that category - Check `machines` table for `operator_id IS NULL` and matching `category_id`

### Issue: Multiple Machines Linked
**Solution:** The trigger uses `LIMIT 1`, so only one machine should be linked. If multiple are linked, check for:
- Manual updates bypassing the trigger
- Multiple triggers firing

### Verify Trigger is Working
```sql
-- Check trigger definition
SHOW CREATE TRIGGER update_machine_from_operator;

-- Test by updating an operator
UPDATE operators 
SET equipment_type = 'Excavator', category_id = 2 
WHERE operator_id = [TEST_ID];

-- Check if machine was linked
SELECT * FROM machines WHERE operator_id = [TEST_ID];
```

## Notes
- The trigger only fires on **UPDATE**, not on **INSERT** (operators don't have category_id during initial signup)
- The PHP code includes a backup manual update for redundancy
- Machine linking happens automatically - no manual intervention needed
- The trigger ensures data consistency between `operators` and `machines` tables













