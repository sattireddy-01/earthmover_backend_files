# Remove model_name Column and Set Automatic Pricing

## Overview
This update removes the `model_name` column from the `machines` table and implements automatic price estimation based on `category_id`.

## Changes Made

### 1. Database Changes
- **Removed Column:** `model_name` from `machines` table
- **Automatic Pricing:** Price is automatically set based on `category_id`:
  - `category_id = 1` (Backhoe Loader) → `price_per_hour = 1250.00`
  - `category_id = 2` (Excavator) → `price_per_hour = 1600.00`
  - `category_id = 3` (Dozer) → `price_per_hour = 1200.00`

### 2. Database Triggers Created
1. **`auto_set_machine_price`** (BEFORE INSERT)
   - Automatically sets price when a new machine is inserted
   
2. **`auto_update_machine_price`** (BEFORE UPDATE)
   - Automatically updates price when `category_id` changes

3. **`update_machine_from_operator`** (AFTER UPDATE on operators)
   - Updated to include automatic price setting when linking machines to operators

### 3. PHP API Updates
- **`admin/get_machines.php`**: Removed `model_name` from SELECT, now uses `machine_model`
- **`admin/create_machine.php`**: Removed `model_name` requirement, price is set automatically
- **`user/get_machines.php`**: Removed `model_name` from SELECT, now uses `machine_model`

## Setup Instructions

### Step 1: Run SQL Script
Execute the SQL script in phpMyAdmin:
```sql
-- File: remove_model_name_and_auto_price.sql
```

This will:
1. Remove the `model_name` column
2. Update existing prices based on `category_id`
3. Create triggers for automatic pricing

### Step 2: Verify Changes
```sql
-- Check table structure (model_name should be gone)
DESCRIBE machines;

-- Check prices are set correctly
SELECT machine_id, category_id, price_per_hour, machine_model 
FROM machines 
ORDER BY category_id;

-- Verify triggers exist
SHOW TRIGGERS LIKE 'machines';
SHOW TRIGGERS LIKE 'operators';
```

## API Changes

### Create Machine API
**Before:**
```json
{
  "category_id": 1,
  "model_name": "JCB 3DX",
  "price_per_hour": 1250.00,
  "specs": "Backhoe Loader",
  "model_year": 2024
}
```

**After:**
```json
{
  "category_id": 1,
  "specs": "Backhoe Loader",
  "model_year": 2024
}
```

**Note:** `price_per_hour` is automatically set based on `category_id` - no need to send it!

### Get Machines API
**Response now includes:**
- `machine_model` (instead of `model_name`)
- `price_per_hour` (automatically set)
- `equipment_type` (if available)

**Backward Compatibility:**
- Still includes `model_name` field (mapped from `machine_model`) for existing clients

## Testing

### Test 1: Create New Machine
```sql
INSERT INTO machines (category_id, specs, model_year) 
VALUES (1, 'Backhoe Loader', 2024);

-- Verify price is automatically set to 1250.00
SELECT machine_id, category_id, price_per_hour FROM machines WHERE machine_id = LAST_INSERT_ID();
```

### Test 2: Update Category
```sql
UPDATE machines 
SET category_id = 2 
WHERE machine_id = 1;

-- Verify price is automatically updated to 1600.00
SELECT machine_id, category_id, price_per_hour FROM machines WHERE machine_id = 1;
```

### Test 3: Operator License Details
1. Submit operator license details with `category_id = 2`
2. Machine should be automatically linked with `price_per_hour = 1600.00`

## Migration Notes

### Existing Data
- All existing machines will have their prices updated based on their `category_id`
- `model_name` data is lost (if you need to preserve it, export first)

### Backward Compatibility
- API responses still include `model_name` field (mapped from `machine_model`)
- This ensures existing Android/iOS apps don't break

## Troubleshooting

### Issue: Price Not Setting Automatically
**Solution:** Check if triggers exist:
```sql
SHOW TRIGGERS LIKE 'machines';
```
If missing, run `remove_model_name_and_auto_price.sql` again.

### Issue: Error "Column 'model_name' doesn't exist"
**Solution:** Make sure you've run the SQL script to remove the column. Check:
```sql
DESCRIBE machines;
```

### Issue: API Still Expects model_name
**Solution:** Update your API client to use `machine_model` instead, or rely on backward compatibility mapping.

## Files Modified
1. `remove_model_name_and_auto_price.sql` - Main migration script
2. `ensure_machine_linking_trigger.sql` - Updated with price logic
3. `api/admin/get_machines.php` - Updated SELECT query
4. `api/admin/create_machine.php` - Removed model_name requirement
5. `api/user/get_machines.php` - Updated SELECT query













