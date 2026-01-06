-- =====================================================
-- AUTO-CREATE MACHINES FROM OPERATORS
-- When operator submits license details, automatically CREATE a machine
-- if no available machine exists. This ensures all operators have machines.
-- =====================================================

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
                category_id = NEW.category_id
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
                    category_id = NEW.category_id
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
                    NEW.equipment_type,  -- Use equipment_type as specs
                    NEW.machine_year,    -- Use machine_year as model_year
                    NEW.machine_image_1  -- Use machine_image_1 as image
                );
            END IF;
        END IF;
    END IF;
END$$

DELIMITER ;

-- Verify trigger was created
SHOW TRIGGERS LIKE 'operators';

-- Test: Check if all operators with license details have machines
SELECT 
    o.operator_id,
    o.name,
    o.category_id,
    o.equipment_type,
    CASE 
        WHEN m.operator_id IS NOT NULL THEN 'HAS MACHINE'
        ELSE 'NO MACHINE'
    END AS machine_status,
    m.machine_id
FROM operators o
LEFT JOIN machines m ON o.operator_id = m.operator_id
WHERE o.category_id IS NOT NULL
AND o.equipment_type IS NOT NULL
ORDER BY o.operator_id;












