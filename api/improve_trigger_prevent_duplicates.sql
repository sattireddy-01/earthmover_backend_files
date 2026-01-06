-- =====================================================
-- Improve Trigger to Prevent Duplicate Machine Links
-- This ensures one operator can only have ONE machine linked
-- =====================================================

DELIMITER $$

DROP TRIGGER IF EXISTS `update_machine_from_operator`$$

CREATE TRIGGER `update_machine_from_operator` 
AFTER UPDATE ON `operators`
FOR EACH ROW
BEGIN
    -- Declare variables at the beginning (required in MySQL/MariaDB)
    DECLARE rows_affected INT DEFAULT 0;
    DECLARE machine_price DECIMAL(10,2) DEFAULT NULL;
    DECLARE existing_machine_id INT DEFAULT NULL;
    
    -- Only update machines if operator has category_id and equipment_type
    IF NEW.category_id IS NOT NULL AND NEW.equipment_type IS NOT NULL THEN
        -- Set price based on category_id
        IF NEW.category_id = 1 THEN
            SET machine_price = 1250.00;
        ELSEIF NEW.category_id = 2 THEN
            SET machine_price = 1600.00;
        ELSEIF NEW.category_id = 3 THEN
            SET machine_price = 1200.00;
        END IF;
        
        -- First, check if operator already has a machine linked
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
            -- If no machine is linked, link one from the same category
            -- Double-check that operator still doesn't have a machine (race condition protection)
            SELECT machine_id INTO existing_machine_id
            FROM machines
            WHERE operator_id = NEW.operator_id
            LIMIT 1;
            
            -- Only link if operator still doesn't have a machine
            IF existing_machine_id IS NULL THEN
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
            END IF;
        END IF;
    END IF;
END$$

DELIMITER ;

-- Verify trigger was updated
SHOW TRIGGERS LIKE 'operators';

-- Test: Check trigger prevents duplicates
-- This query should show that each operator has at most 1 machine
SELECT 
    operator_id,
    COUNT(*) AS machine_count
FROM machines
WHERE operator_id IS NOT NULL
GROUP BY operator_id
HAVING COUNT(*) > 1;

