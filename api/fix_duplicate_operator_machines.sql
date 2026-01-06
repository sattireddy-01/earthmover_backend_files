-- =====================================================
-- Fix Duplicate Operator-Machine Links
-- This script finds and fixes cases where one operator
-- is linked to multiple machines
-- =====================================================

-- Step 1: Find operators with multiple machines linked
SELECT 
    operator_id,
    COUNT(*) AS machine_count,
    GROUP_CONCAT(machine_id) AS machine_ids
FROM machines
WHERE operator_id IS NOT NULL
GROUP BY operator_id
HAVING COUNT(*) > 1;

-- Step 2: Show details of duplicate links
SELECT 
    m.machine_id,
    m.operator_id,
    o.name AS operator_name,
    m.category_id,
    m.price_per_hour,
    m.machine_model,
    m.equipment_type
FROM machines m
INNER JOIN operators o ON m.operator_id = o.operator_id
WHERE m.operator_id IN (
    SELECT operator_id
    FROM machines
    WHERE operator_id IS NOT NULL
    GROUP BY operator_id
    HAVING COUNT(*) > 1
)
ORDER BY m.operator_id, m.machine_id;

-- Step 3: Fix duplicate - Keep the first machine, unlink the rest
-- For operator_id 49: Keep machine_id 8, unlink machine_id 9
UPDATE machines
SET operator_id = NULL,
    phone = NULL,
    address = NULL,
    equipment_type = NULL,
    machine_model = NULL,
    machine_year = NULL,
    machine_image_1 = NULL,
    availability = 'OFFLINE',
    profile_image = NULL
WHERE machine_id = 9
AND operator_id = 49;

-- Step 4: Verify the fix
SELECT 
    operator_id,
    COUNT(*) AS machine_count
FROM machines
WHERE operator_id IS NOT NULL
GROUP BY operator_id
HAVING COUNT(*) > 1;

-- Should return no rows if fix is successful

-- Step 5: General fix for ALL duplicate operator-machine links
-- This will keep the machine with the lowest machine_id and unlink others
UPDATE machines m1
INNER JOIN (
    SELECT 
        operator_id,
        MIN(machine_id) AS keep_machine_id
    FROM machines
    WHERE operator_id IS NOT NULL
    GROUP BY operator_id
    HAVING COUNT(*) > 1
) AS duplicates ON m1.operator_id = duplicates.operator_id
SET 
    m1.operator_id = NULL,
    m1.phone = NULL,
    m1.address = NULL,
    m1.equipment_type = NULL,
    m1.machine_model = NULL,
    m1.machine_year = NULL,
    m1.machine_image_1 = NULL,
    m1.availability = 'OFFLINE',
    m1.profile_image = NULL
WHERE m1.machine_id != duplicates.keep_machine_id;

-- Step 6: Final verification
SELECT 
    operator_id,
    COUNT(*) AS machine_count,
    GROUP_CONCAT(machine_id) AS machine_ids
FROM machines
WHERE operator_id IS NOT NULL
GROUP BY operator_id
HAVING COUNT(*) > 1;

-- Should return no rows - all duplicates fixed!













