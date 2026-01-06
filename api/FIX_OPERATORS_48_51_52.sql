-- =====================================================
-- FIX: Create Machines for Operators 48, 51, 52
-- These operators have license details but no machines linked
-- =====================================================

-- Step 1: Check current status
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
WHERE o.operator_id IN (48, 51, 52)
ORDER BY o.operator_id;

-- Step 2: Create machine for Operator 48 (Harish - Dozer, category_id = 3)
INSERT INTO `machines` (
    operator_id,
    phone,
    address,
    equipment_type,
    machine_model,
    machine_year,
    machine_image_1,
    availability,
    profile_image,
    price_per_hour,
    category_id,
    specs,
    model_year,
    image
)
SELECT 
    o.operator_id,
    o.phone,
    o.address,
    o.equipment_type,
    o.machine_model,
    o.machine_year,
    o.machine_image_1,
    o.availability,
    o.profile_image,
    1200.00 AS price_per_hour,  -- category_id = 3 (Dozer)
    o.category_id,
    o.equipment_type AS specs,
    o.machine_year AS model_year,
    o.machine_image_1 AS image
FROM operators o
WHERE o.operator_id = 48
AND NOT EXISTS (
    SELECT 1 FROM machines m WHERE m.operator_id = 48
);

-- Step 3: Create machine for Operator 51 (Harsha - Excavator, category_id = 2)
INSERT INTO `machines` (
    operator_id,
    phone,
    address,
    equipment_type,
    machine_model,
    machine_year,
    machine_image_1,
    availability,
    profile_image,
    price_per_hour,
    category_id,
    specs,
    model_year,
    image
)
SELECT 
    o.operator_id,
    o.phone,
    o.address,
    o.equipment_type,
    o.machine_model,
    o.machine_year,
    o.machine_image_1,
    o.availability,
    o.profile_image,
    1600.00 AS price_per_hour,  -- category_id = 2 (Excavator)
    o.category_id,
    o.equipment_type AS specs,
    o.machine_year AS model_year,
    o.machine_image_1 AS image
FROM operators o
WHERE o.operator_id = 51
AND NOT EXISTS (
    SELECT 1 FROM machines m WHERE m.operator_id = 51
);

-- Step 4: Create machine for Operator 52 (Vardhan - Excavator, category_id = 2)
INSERT INTO `machines` (
    operator_id,
    phone,
    address,
    equipment_type,
    machine_model,
    machine_year,
    machine_image_1,
    availability,
    profile_image,
    price_per_hour,
    category_id,
    specs,
    model_year,
    image
)
SELECT 
    o.operator_id,
    o.phone,
    o.address,
    o.equipment_type,
    o.machine_model,
    o.machine_year,
    o.machine_image_1,
    o.availability,
    o.profile_image,
    1600.00 AS price_per_hour,  -- category_id = 2 (Excavator)
    o.category_id,
    o.equipment_type AS specs,
    o.machine_year AS model_year,
    o.machine_image_1 AS image
FROM operators o
WHERE o.operator_id = 52
AND NOT EXISTS (
    SELECT 1 FROM machines m WHERE m.operator_id = 52
);

-- Step 5: Verify all operators are now synced
SELECT 
    o.operator_id,
    o.name,
    o.category_id,
    o.equipment_type,
    m.machine_id,
    m.operator_id AS machine_operator_id,
    m.price_per_hour,
    CASE 
        WHEN m.operator_id = o.operator_id THEN '✅ SYNCED'
        ELSE '❌ NOT SYNCED'
    END AS status
FROM operators o
LEFT JOIN machines m ON o.operator_id = m.operator_id
WHERE o.operator_id IN (48, 51, 52)
ORDER BY o.operator_id;

-- Step 6: Final check - All operators with license details
SELECT 
    o.operator_id,
    o.name,
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












