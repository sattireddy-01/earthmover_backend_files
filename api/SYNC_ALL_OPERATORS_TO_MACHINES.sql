-- =====================================================
-- SYNC ALL EXISTING OPERATORS TO MACHINES TABLE
-- This creates machines for all operators who have license details
-- but don't have machines linked yet
-- =====================================================

-- Step 1: Find operators without machines
SELECT 
    o.operator_id,
    o.name,
    o.category_id,
    o.equipment_type,
    'NEEDS MACHINE' AS status
FROM operators o
LEFT JOIN machines m ON o.operator_id = m.operator_id
WHERE o.category_id IS NOT NULL
AND o.equipment_type IS NOT NULL
AND m.operator_id IS NULL
ORDER BY o.operator_id;

-- Step 2: Create machines for all operators without machines
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
    CASE 
        WHEN o.category_id = 1 THEN 1250.00
        WHEN o.category_id = 2 THEN 1600.00
        WHEN o.category_id = 3 THEN 1200.00
        ELSE NULL
    END AS price_per_hour,
    o.category_id,
    o.equipment_type AS specs,
    o.machine_year AS model_year,
    o.machine_image_1 AS image
FROM operators o
LEFT JOIN machines m ON o.operator_id = m.operator_id
WHERE o.category_id IS NOT NULL
AND o.equipment_type IS NOT NULL
AND m.operator_id IS NULL;

-- Step 3: Verify all operators now have machines
SELECT 
    o.operator_id,
    o.name,
    o.category_id,
    o.equipment_type,
    m.machine_id,
    m.operator_id AS machine_operator_id,
    CASE 
        WHEN m.operator_id = o.operator_id THEN 'SYNCED'
        ELSE 'NOT SYNCED'
    END AS sync_status
FROM operators o
LEFT JOIN machines m ON o.operator_id = m.operator_id
WHERE o.category_id IS NOT NULL
AND o.equipment_type IS NOT NULL
ORDER BY o.operator_id;

-- Step 4: Count synced vs unsynced
SELECT 
    COUNT(*) AS total_operators_with_license,
    SUM(CASE WHEN m.operator_id IS NOT NULL THEN 1 ELSE 0 END) AS operators_with_machines,
    SUM(CASE WHEN m.operator_id IS NULL THEN 1 ELSE 0 END) AS operators_without_machines
FROM operators o
LEFT JOIN machines m ON o.operator_id = m.operator_id
WHERE o.category_id IS NOT NULL
AND o.equipment_type IS NOT NULL;












