-- =====================================================
-- QUICK FIX: Remove duplicate machine link for operator_id 49
-- This will unlink machine_id 9, keeping machine_id 8 linked
-- =====================================================

-- Fix: Unlink machine_id 9 from operator_id 49
UPDATE machines
SET 
    operator_id = NULL,
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

-- Verify the fix
SELECT 
    machine_id,
    operator_id,
    category_id,
    price_per_hour,
    machine_model
FROM machines
WHERE operator_id = 49;

-- Should show only machine_id 8 now

-- Also check for any other duplicates
SELECT 
    operator_id,
    COUNT(*) AS machine_count,
    GROUP_CONCAT(machine_id) AS machine_ids
FROM machines
WHERE operator_id IS NOT NULL
GROUP BY operator_id
HAVING COUNT(*) > 1;

-- Should return no rows if no other duplicates exist













