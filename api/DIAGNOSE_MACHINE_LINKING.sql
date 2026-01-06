-- =====================================================
-- DIAGNOSE: Why Operators Are Not Getting Machines Linked
-- Run this to identify the root cause
-- =====================================================

-- Query 1: Find operators with license details but NO machines linked
SELECT 
    o.operator_id,
    o.name,
    o.phone,
    o.category_id,
    o.equipment_type,
    o.machine_model,
    'NO MACHINE LINKED' AS status,
    (SELECT COUNT(*) FROM machines m2 
     WHERE m2.category_id = o.category_id AND m2.operator_id IS NULL) AS available_machines_in_category
FROM operators o
LEFT JOIN machines m ON o.operator_id = m.operator_id
WHERE o.category_id IS NOT NULL
AND o.equipment_type IS NOT NULL
AND m.operator_id IS NULL
ORDER BY o.operator_id;

-- Query 2: Check machine availability by category
SELECT 
    category_id,
    CASE 
        WHEN category_id = 1 THEN 'Backhoe Loader'
        WHEN category_id = 2 THEN 'Excavator'
        WHEN category_id = 3 THEN 'Dozer'
        ELSE 'Unknown'
    END AS category_name,
    COUNT(*) AS total_machines,
    SUM(CASE WHEN operator_id IS NULL THEN 1 ELSE 0 END) AS available_machines,
    SUM(CASE WHEN operator_id IS NOT NULL THEN 1 ELSE 0 END) AS linked_machines,
    GROUP_CONCAT(CASE WHEN operator_id IS NULL THEN machine_id END) AS available_machine_ids
FROM machines
GROUP BY category_id
ORDER BY category_id;

-- Query 3: Show all machines and their status
SELECT 
    m.machine_id,
    m.operator_id,
    m.category_id,
    CASE 
        WHEN m.category_id = 1 THEN 'Backhoe Loader'
        WHEN m.category_id = 2 THEN 'Excavator'
        WHEN m.category_id = 3 THEN 'Dozer'
        ELSE 'Unknown'
    END AS category_name,
    m.price_per_hour,
    m.equipment_type,
    m.machine_model,
    CASE 
        WHEN m.operator_id IS NULL THEN 'AVAILABLE'
        ELSE CONCAT('LINKED TO OPERATOR ', m.operator_id)
    END AS status,
    o.name AS operator_name
FROM machines m
LEFT JOIN operators o ON m.operator_id = o.operator_id
ORDER BY m.category_id, m.machine_id;

-- Query 4: Operators waiting for machines (no available machines in their category)
SELECT 
    o.operator_id,
    o.name,
    o.category_id,
    o.equipment_type,
    (SELECT COUNT(*) FROM machines m2 
     WHERE m2.category_id = o.category_id AND m2.operator_id IS NULL) AS available_machines,
    'NEEDS MACHINES CREATED' AS action_required
FROM operators o
LEFT JOIN machines m ON o.operator_id = m.operator_id
WHERE o.category_id IS NOT NULL
AND o.equipment_type IS NOT NULL
AND m.operator_id IS NULL
AND (SELECT COUNT(*) FROM machines m2 
     WHERE m2.category_id = o.category_id AND m2.operator_id IS NULL) = 0
ORDER BY o.category_id, o.operator_id;

-- Query 5: Check if trigger exists and is correct
SHOW TRIGGERS LIKE 'operators';

-- Query 6: View trigger definition
SHOW CREATE TRIGGER update_machine_from_operator;












