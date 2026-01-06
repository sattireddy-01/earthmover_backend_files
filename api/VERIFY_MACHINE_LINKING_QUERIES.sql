-- =====================================================
-- SQL QUERIES TO VERIFY MACHINE LINKING
-- Copy and paste these queries into phpMyAdmin
-- =====================================================

-- Query 1: Check ALL operators and their linked machines
-- This shows all operators and whether they have linked machines
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

-- Query 2: Check operators with license details but NO linked machine
-- These operators should have machines linked
SELECT 
    o.operator_id,
    o.name,
    o.phone,
    o.equipment_type,
    o.category_id,
    o.machine_model,
    'NO MACHINE LINKED' AS status
FROM operators o
LEFT JOIN machines m ON o.operator_id = m.operator_id
WHERE o.category_id IS NOT NULL
AND o.equipment_type IS NOT NULL
AND m.operator_id IS NULL
ORDER BY o.operator_id;

-- Query 3: Check machines that are linked to operators
SELECT 
    m.machine_id,
    m.operator_id,
    m.category_id,
    m.price_per_hour,
    m.equipment_type,
    m.machine_model,
    o.name AS operator_name,
    o.phone AS operator_phone
FROM machines m
INNER JOIN operators o ON m.operator_id = o.operator_id
ORDER BY m.machine_id;

-- Query 4: Check machines that are NOT linked (available for linking)
SELECT 
    machine_id,
    category_id,
    price_per_hour,
    equipment_type,
    machine_model
FROM machines
WHERE operator_id IS NULL
ORDER BY category_id, machine_id;

-- Query 5: Verify price is set correctly based on category_id
SELECT 
    o.operator_id,
    o.name,
    o.category_id,
    o.equipment_type,
    m.machine_id,
    m.price_per_hour AS actual_price,
    CASE 
        WHEN m.category_id = 1 THEN 1250.00
        WHEN m.category_id = 2 THEN 1600.00
        WHEN m.category_id = 3 THEN 1200.00
        ELSE NULL
    END AS expected_price,
    CASE 
        WHEN m.price_per_hour = CASE 
            WHEN m.category_id = 1 THEN 1250.00
            WHEN m.category_id = 2 THEN 1600.00
            WHEN m.category_id = 3 THEN 1200.00
            ELSE NULL
        END THEN 'CORRECT'
        ELSE 'INCORRECT'
    END AS price_status
FROM operators o
INNER JOIN machines m ON o.operator_id = m.operator_id
WHERE o.category_id IS NOT NULL
ORDER BY o.operator_id;

-- Query 6: Check a SPECIFIC operator (replace 47 with your operator_id)
-- Example: Check operator with ID 47
SELECT 
    o.operator_id,
    o.name,
    o.phone,
    o.equipment_type,
    o.category_id,
    o.machine_model,
    o.machine_year,
    m.machine_id,
    m.operator_id AS machine_operator_id,
    m.category_id AS machine_category_id,
    m.price_per_hour,
    m.machine_model AS machine_machine_model,
    m.equipment_type AS machine_equipment_type
FROM operators o
LEFT JOIN machines m ON o.operator_id = m.operator_id
WHERE o.operator_id = 47;

-- Query 7: Count operators by link status
SELECT 
    CASE 
        WHEN m.operator_id IS NULL THEN 'NOT LINKED'
        WHEN m.operator_id = o.operator_id THEN 'LINKED'
        ELSE 'ERROR'
    END AS link_status,
    COUNT(*) AS count
FROM operators o
LEFT JOIN machines m ON o.operator_id = m.operator_id
WHERE o.category_id IS NOT NULL
GROUP BY link_status;

-- Query 8: Check if trigger exists
SHOW TRIGGERS LIKE 'operators';

-- Query 9: View trigger definition
SHOW CREATE TRIGGER update_machine_from_operator;













