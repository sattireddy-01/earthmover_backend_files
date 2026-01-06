-- =====================================================
-- COMPLETE SETUP: Auto-Create Machines from Operators
-- This ensures ALL operators with license details have machines
-- =====================================================

-- STEP 1: Update trigger to auto-create machines
DELIMITER $$

DROP TRIGGER IF EXISTS `update_machine_from_operator`$$

CREATE TRIGGER `update_machine_from_operator` 
AFTER UPDATE ON `operators`
FOR EACH ROW
BEGIN
    DECLARE rows_affected INT DEFAULT 0;
    DECLARE machine_price DECIMAL(10,2) DEFAULT NULL;
    DECLARE existing_machine_id INT DEFAULT NULL;
    DECLARE available_machine_count INT DEFAULT 0;
    
    -- Only update/create machines if operator has category_id and equipment_type
    IF NEW.category_id IS NOT NULL AND NEW.equipment_type IS NOT NULL THEN
        -- Set price based on category_id
        IF NEW.category_id = 1 THEN
            SET machine_price = 1250.00;
        ELSEIF NEW.category_id = 2 THEN
            SET machine_price = 1600.00;
        ELSEIF NEW.category_id = 3 THEN
            SET machine_price = 1200.00;
        END IF;
        
        -- Check if operator already has a machine linked
        SELECT machine_id INTO existing_machine_id
        FROM machines
        WHERE operator_id = NEW.operator_id
        LIMIT 1;
        
        -- If operator already has a machine, update that one
        IF existing_machine_id IS NOT NULL THEN
            UPDATE `machines`
            SET 
                phone = NEW.phone,
                address = NEW.address,
                equipment_type = NEW.equipment_type,
                machine_model = NEW.machine_model,
                machine_year = NEW.machine_year,
                machine_image_1 = NEW.machine_image_1,
                availability = NEW.availability,
                profile_image = NEW.profile_image,
                price_per_hour = machine_price,
                category_id = NEW.category_id,
                specs = NEW.equipment_type,
                model_year = NEW.machine_year,
                image = NEW.machine_image_1
            WHERE machine_id = existing_machine_id;
        ELSE
            -- Operator doesn't have a machine yet
            -- First, try to link an available machine from the same category
            SELECT COUNT(*) INTO available_machine_count
            FROM machines
            WHERE category_id = NEW.category_id 
            AND operator_id IS NULL;
            
            IF available_machine_count > 0 THEN
                -- Link an available machine
                UPDATE `machines`
                SET 
                    operator_id = NEW.operator_id,
                    phone = NEW.phone,
                    address = NEW.address,
                    equipment_type = NEW.equipment_type,
                    machine_model = NEW.machine_model,
                    machine_year = NEW.machine_year,
                    machine_image_1 = NEW.machine_image_1,
                    availability = NEW.availability,
                    profile_image = NEW.profile_image,
                    price_per_hour = machine_price,
                    category_id = NEW.category_id,
                    specs = NEW.equipment_type,
                    model_year = NEW.machine_year,
                    image = NEW.machine_image_1
                WHERE category_id = NEW.category_id 
                AND operator_id IS NULL
                LIMIT 1;
                
                SET rows_affected = ROW_COUNT();
            END IF;
            
            -- If no machine was linked (no available machines), CREATE a new one
            IF rows_affected = 0 THEN
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
                ) VALUES (
                    NEW.operator_id,
                    NEW.phone,
                    NEW.address,
                    NEW.equipment_type,
                    NEW.machine_model,
                    NEW.machine_year,
                    NEW.machine_image_1,
                    NEW.availability,
                    NEW.profile_image,
                    machine_price,
                    NEW.category_id,
                    NEW.equipment_type,
                    NEW.machine_year,
                    NEW.machine_image_1
                );
            END IF;
        END IF;
    END IF;
END$$

DELIMITER ;

-- STEP 2: Sync all existing operators to machines table
-- Create machines for operators who don't have them yet
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

-- STEP 3: Verify all operators now have machines
SELECT 
    o.operator_id,
    o.name,
    o.category_id,
    o.equipment_type,
    m.machine_id,
    m.operator_id AS machine_operator_id,
    CASE 
        WHEN m.operator_id = o.operator_id THEN '✅ SYNCED'
        ELSE '❌ NOT SYNCED'
    END AS sync_status
FROM operators o
LEFT JOIN machines m ON o.operator_id = m.operator_id
WHERE o.category_id IS NOT NULL
AND o.equipment_type IS NOT NULL
ORDER BY o.operator_id;

-- STEP 4: Show summary
SELECT 
    'Total Operators with License' AS metric,
    COUNT(*) AS count
FROM operators
WHERE category_id IS NOT NULL AND equipment_type IS NOT NULL
UNION ALL
SELECT 
    'Operators with Machines' AS metric,
    COUNT(DISTINCT m.operator_id) AS count
FROM machines m
INNER JOIN operators o ON m.operator_id = o.operator_id
WHERE o.category_id IS NOT NULL AND o.equipment_type IS NOT NULL
UNION ALL
SELECT 
    'Operators without Machines' AS metric,
    COUNT(*) AS count
FROM operators o
LEFT JOIN machines m ON o.operator_id = m.operator_id
WHERE o.category_id IS NOT NULL
AND o.equipment_type IS NOT NULL
AND m.operator_id IS NULL;

-- STEP 5: Verify trigger exists
SHOW TRIGGERS LIKE 'operators';












