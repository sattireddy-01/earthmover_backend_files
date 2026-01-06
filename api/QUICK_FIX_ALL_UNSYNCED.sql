-- =====================================================
-- QUICK FIX: Create Machines for ALL Unsynced Operators
-- This will create machines for any operator with license details
-- but no machine linked
-- =====================================================

-- Create machines for ALL operators who don't have them
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

-- Verify all are now synced
SELECT 
    o.operator_id,
    o.name,
    m.machine_id,
    m.price_per_hour,
    CASE 
        WHEN m.operator_id = o.operator_id THEN '✅ SYNCED'
        ELSE '❌ NOT SYNCED'
    END AS status
FROM operators o
LEFT JOIN machines m ON o.operator_id = m.operator_id
WHERE o.category_id IS NOT NULL
AND o.equipment_type IS NOT NULL
ORDER BY o.operator_id;












