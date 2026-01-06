-- =====================================================
-- FIX: Link Operators That Have License Details But No Machines
-- This will link operators to available machines
-- =====================================================

-- Step 1: Check which operators need to be linked
SELECT 
    o.operator_id,
    o.name,
    o.category_id,
    o.equipment_type,
    (SELECT COUNT(*) FROM machines m2 
     WHERE m2.category_id = o.category_id AND m2.operator_id IS NULL) AS available_machines
FROM operators o
LEFT JOIN machines m ON o.operator_id = m.operator_id
WHERE o.category_id IS NOT NULL
AND o.equipment_type IS NOT NULL
AND m.operator_id IS NULL
ORDER BY o.operator_id;

-- Step 2: Manually link operator 48 (Dozer, category_id = 3)
-- First check if there are available machines
SELECT * FROM machines WHERE category_id = 3 AND operator_id IS NULL;

-- If machines are available, link operator 48
UPDATE machines m
INNER JOIN operators o ON o.operator_id = 48
SET 
    m.operator_id = 48,
    m.phone = o.phone,
    m.address = o.address,
    m.equipment_type = o.equipment_type,
    m.machine_model = o.machine_model,
    m.machine_year = o.machine_year,
    m.machine_image_1 = o.machine_image_1,
    m.availability = o.availability,
    m.profile_image = o.profile_image,
    m.price_per_hour = CASE 
        WHEN o.category_id = 1 THEN 1250.00
        WHEN o.category_id = 2 THEN 1600.00
        WHEN o.category_id = 3 THEN 1200.00
    END,
    m.category_id = o.category_id
WHERE m.category_id = 3 
AND m.operator_id IS NULL
LIMIT 1;

-- Step 3: Manually link operator 51 (Excavator, category_id = 2)
UPDATE machines m
INNER JOIN operators o ON o.operator_id = 51
SET 
    m.operator_id = 51,
    m.phone = o.phone,
    m.address = o.address,
    m.equipment_type = o.equipment_type,
    m.machine_model = o.machine_model,
    m.machine_year = o.machine_year,
    m.machine_image_1 = o.machine_image_1,
    m.availability = o.availability,
    m.profile_image = o.profile_image,
    m.price_per_hour = 1600.00,
    m.category_id = o.category_id
WHERE m.category_id = 2 
AND m.operator_id IS NULL
LIMIT 1;

-- Step 4: Manually link operator 52 (Excavator, category_id = 2)
UPDATE machines m
INNER JOIN operators o ON o.operator_id = 52
SET 
    m.operator_id = 52,
    m.phone = o.phone,
    m.address = o.address,
    m.equipment_type = o.equipment_type,
    m.machine_model = o.machine_model,
    m.machine_year = o.machine_year,
    m.machine_image_1 = o.machine_image_1,
    m.availability = o.availability,
    m.profile_image = o.profile_image,
    m.price_per_hour = 1600.00,
    m.category_id = o.category_id
WHERE m.category_id = 2 
AND m.operator_id IS NULL
LIMIT 1;

-- Step 5: Verify all operators are now linked
SELECT 
    o.operator_id,
    o.name,
    o.category_id,
    m.machine_id,
    m.operator_id AS machine_operator_id,
    CASE 
        WHEN m.operator_id = o.operator_id THEN 'LINKED'
        ELSE 'NOT LINKED'
    END AS status
FROM operators o
LEFT JOIN machines m ON o.operator_id = m.operator_id
WHERE o.category_id IS NOT NULL
AND o.equipment_type IS NOT NULL
ORDER BY o.operator_id;

-- Step 6: If no machines are available, you need to create them first
-- Example: Create a machine for category_id = 2 (Excavator)
-- INSERT INTO machines (category_id, price_per_hour, specs, model_year, availability)
-- VALUES (2, 1600.00, 'Excavator', 2024, 'OFFLINE');












