-- =====================================================
-- SQL Script to Ensure Automatic Machine Linking
-- This trigger automatically links machines to operators
-- when license details are saved (UPDATE on operators)
-- =====================================================

-- Drop existing trigger if it exists
DROP TRIGGER IF EXISTS `update_machine_from_operator`;

DELIMITER $$

CREATE TRIGGER `update_machine_from_operator` 
AFTER UPDATE ON `operators`
FOR EACH ROW
BEGIN
    -- Declare variable at the beginning (required in MySQL/MariaDB)
    DECLARE rows_affected INT DEFAULT 0;
    DECLARE machine_price DECIMAL(10,2) DEFAULT NULL;
    
    -- Only update machines if operator has category_id and equipment_type
    -- This ensures we only link when license details are provided
    IF NEW.category_id IS NOT NULL AND NEW.equipment_type IS NOT NULL THEN
        -- Set price based on category_id
        -- category_id = 1 (Backhoe Loader) → 1250
        -- category_id = 2 (Excavator) → 1600
        -- category_id = 3 (Dozer) → 1200
        IF NEW.category_id = 1 THEN
            SET machine_price = 1250.00;
        ELSEIF NEW.category_id = 2 THEN
            SET machine_price = 1600.00;
        ELSEIF NEW.category_id = 3 THEN
            SET machine_price = 1200.00;
        END IF;
        
        -- First, try to update existing machine linked to this operator
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
        WHERE operator_id = NEW.operator_id;
        
        -- Get number of rows affected (must be called immediately after UPDATE)
        SET rows_affected = ROW_COUNT();
        
        -- If no machine is linked yet (rows_affected = 0), link one from the same category
        IF rows_affected = 0 THEN
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
END$$

DELIMITER ;

-- Verify the trigger was created
SHOW TRIGGERS LIKE 'operators';
