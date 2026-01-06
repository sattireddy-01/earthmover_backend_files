# Verify Machine Linking When Operator Submits License Details

## Flow Verification

### 1. Android Activity Flow
**File:** `OperatorLicenseDetailsActivity.java`
- User fills form in `activity_operator_license_details.xml`
- Clicks submit button
- Calls `apiService.saveLicenseDetails(request)`
- **API Endpoint:** `POST operator/save_license_details.php`

### 2. API Endpoint Flow
**File:** `api/operator/save_license_details.php`

**Step 1:** Updates `operators` table with:
- `license_no`
- `rc_number`
- `equipment_type`
- `category_id` (calculated from equipment_type)
- `machine_model`
- `machine_year`
- `machine_image_1`
- `approve_status = 'PENDING'`

**Step 2:** Database Trigger Fires
- Trigger: `update_machine_from_operator` (AFTER UPDATE on operators)
- **Automatically:**
  - Updates existing linked machine OR
  - Links a new machine from the same category
  - Sets `price_per_hour` based on `category_id`
  - Updates all machine fields with operator data

**Step 3:** Backup PHP Code (Safety Net)
- Also updates machines table manually
- Ensures linking works even if trigger fails
- Includes `price_per_hour` and `category_id`

## Verification Checklist

### ✅ Database Trigger Exists
```sql
SHOW TRIGGERS LIKE 'operators';
```
Should show: `update_machine_from_operator`

### ✅ Trigger Logic
The trigger should:
1. Check if `category_id` and `equipment_type` are NOT NULL
2. Calculate price based on `category_id`:
   - category_id = 1 → 1250.00
   - category_id = 2 → 1600.00
   - category_id = 3 → 1200.00
3. Update existing machine OR link new machine
4. Set all fields including `price_per_hour` and `category_id`

### ✅ PHP Code Updates Machines
The backup code in `save_license_details.php` (lines 246-330) should:
1. Calculate price based on `category_id`
2. Update existing machine with all fields including `price_per_hour` and `category_id`
3. If no machine linked, link one from same category

## Testing Steps

### Test 1: New Operator Submits License Details
1. Register a new operator (basic info only)
2. Submit license details via Android app
3. **Check operators table:**
   ```sql
   SELECT operator_id, name, equipment_type, category_id, machine_model 
   FROM operators 
   WHERE operator_id = [NEW_OPERATOR_ID];
   ```
4. **Check machines table:**
   ```sql
   SELECT machine_id, operator_id, category_id, price_per_hour, machine_model, equipment_type
   FROM machines 
   WHERE operator_id = [NEW_OPERATOR_ID];
   ```
5. **Expected Result:**
   - Operator has `category_id` and `equipment_type` set
   - Machine is linked (`operator_id` matches)
   - `price_per_hour` is set correctly based on `category_id`
   - All machine fields match operator data

### Test 2: Verify Price is Set Correctly
```sql
SELECT 
    o.operator_id,
    o.name,
    o.category_id,
    o.equipment_type,
    m.machine_id,
    m.price_per_hour,
    CASE 
        WHEN m.category_id = 1 THEN 1250.00
        WHEN m.category_id = 2 THEN 1600.00
        WHEN m.category_id = 3 THEN 1200.00
    END AS expected_price
FROM operators o
LEFT JOIN machines m ON o.operator_id = m.operator_id
WHERE o.category_id IS NOT NULL
AND o.equipment_type IS NOT NULL;
```

**Expected:** `price_per_hour` should match `expected_price`

### Test 3: Check Trigger is Working
```sql
-- Update an operator to trigger the trigger
UPDATE operators 
SET equipment_type = 'Excavator', category_id = 2 
WHERE operator_id = [TEST_OPERATOR_ID];

-- Check if machine was updated/linked
SELECT * FROM machines WHERE operator_id = [TEST_OPERATOR_ID];
```

## Common Issues

### Issue 1: Machine Not Linking
**Possible Causes:**
- Trigger doesn't exist → Run `RUN_THIS_SQL.sql`
- `category_id` or `equipment_type` is NULL → Check form submission
- No available machines in that category → Check `machines` table

**Solution:**
```sql
-- Check if trigger exists
SHOW TRIGGERS LIKE 'operators';

-- Check available machines
SELECT * FROM machines 
WHERE category_id = [CATEGORY_ID] 
AND operator_id IS NULL;
```

### Issue 2: Price Not Set
**Possible Causes:**
- Trigger not updated with price logic
- Backup PHP code missing price calculation

**Solution:**
- Run `RUN_THIS_SQL.sql` to update trigger
- Verify PHP code includes price calculation (lines 249-253 in save_license_details.php)

### Issue 3: Multiple Machines Linked
**Solution:** Trigger uses `LIMIT 1`, so only one machine should be linked. If multiple are linked, check for:
- Manual updates bypassing trigger
- Multiple triggers firing

## Verification Query
Run this query to see all operators and their linked machines:

```sql
SELECT 
    o.operator_id,
    o.name,
    o.phone,
    o.equipment_type,
    o.category_id,
    o.machine_model,
    m.machine_id,
    m.operator_id AS machine_operator_id,
    m.category_id AS machine_category_id,
    m.price_per_hour,
    m.machine_model AS machine_machine_model,
    CASE 
        WHEN m.operator_id IS NULL THEN 'NOT LINKED'
        WHEN m.operator_id = o.operator_id THEN 'LINKED'
        ELSE 'LINKED TO OTHER OPERATOR'
    END AS link_status
FROM operators o
LEFT JOIN machines m ON o.operator_id = m.operator_id
WHERE o.category_id IS NOT NULL
ORDER BY o.operator_id;
```

**Expected Result:**
- All operators with `category_id` should have `link_status = 'LINKED'`
- `machine_category_id` should match `o.category_id`
- `price_per_hour` should match category pricing

## Summary

✅ **YES, when a new operator submits license details:**
1. The `operators` table is updated
2. The database trigger `update_machine_from_operator` fires automatically
3. The `machines` table is updated/linked with:
   - All operator data (phone, address, equipment_type, etc.)
   - Automatic `price_per_hour` based on `category_id`
   - `category_id` set correctly
4. Backup PHP code ensures it works even if trigger fails

The system has **double protection**: Database trigger (automatic) + PHP backup code (manual).













