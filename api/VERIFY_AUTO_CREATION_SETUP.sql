-- =====================================================
-- VERIFY: Auto-Creation Setup is Working
-- Check if trigger exists and is configured correctly
-- =====================================================

-- Step 1: Check if trigger exists
SHOW TRIGGERS LIKE 'operators';

-- Step 2: View trigger definition
SHOW CREATE TRIGGER update_machine_from_operator;

-- Step 3: Check current operators and their machines
SELECT 
    o.operator_id,
    o.name,
    o.category_id,
    o.equipment_type,
    m.machine_id,
    CASE 
        WHEN m.operator_id = o.operator_id THEN '✅ SYNCED'
        ELSE '❌ NOT SYNCED'
    END AS status
FROM operators o
LEFT JOIN machines m ON o.operator_id = m.operator_id
WHERE o.category_id IS NOT NULL
AND o.equipment_type IS NOT NULL
ORDER BY o.operator_id;

-- Step 4: Test the trigger (simulate operator update)
-- This will show if trigger would fire correctly
-- Don't run this on production, just for testing
/*
UPDATE operators 
SET equipment_type = 'Excavator', category_id = 2 
WHERE operator_id = [TEST_OPERATOR_ID];

-- Then check if machine was created/updated
SELECT * FROM machines WHERE operator_id = [TEST_OPERATOR_ID];
*/












