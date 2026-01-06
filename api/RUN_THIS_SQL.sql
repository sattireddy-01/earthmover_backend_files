-- =====================================================
-- COMPLETE SQL SCRIPT - COPY AND PASTE INTO phpMyAdmin
-- This script will:
-- 1. Remove model_name column from machines table
-- 2. Set automatic pricing based on category_id
-- 3. Create triggers for automatic price setting
-- 4. Update machine linking trigger with pricing
-- =====================================================

-- STEP 1: Remove model_name column
-- (If you get an error that column doesn't exist, just continue)
ALTER TABLE `machines` 
DROP COLUMN `model_name`;

-- STEP 2: Update existing prices based on category_id
UPDATE `machines` 
SET `price_per_hour` = 1250.00 
WHERE `category_id` = 1;

UPDATE `machines` 
SET `price_per_hour` = 1600.00 
WHERE `category_id` = 2;

UPDATE `machines` 
SET `price_per_hour` = 1200.00 
WHERE `category_id` = 3;

-- STEP 3: Create triggers for automatic pricing on machines table
DELIMITER $$

DROP TRIGGER IF EXISTS `auto_set_machine_price`$$

CREATE TRIGGER `auto_set_machine_price`
BEFORE INSERT ON `machines`
FOR EACH ROW
BEGIN
    -- Set price based on category_id
    -- category_id = 1 (Backhoe Loader) → 1250.00
    -- category_id = 2 (Excavator) → 1600.00
    -- category_id = 3 (Dozer) → 1200.00
    IF NEW.category_id = 1 THEN
        SET NEW.price_per_hour = 1250.00;
    ELSEIF NEW.category_id = 2 THEN
        SET NEW.price_per_hour = 1600.00;
    ELSEIF NEW.category_id = 3 THEN
        SET NEW.price_per_hour = 1200.00;
    END IF;
END$$

DROP TRIGGER IF EXISTS `auto_update_machine_price`$$

CREATE TRIGGER `auto_update_machine_price`
BEFORE UPDATE ON `machines`
FOR EACH ROW
BEGIN
    -- Update price if category_id changes
    IF NEW.category_id != OLD.category_id OR (NEW.category_id IS NOT NULL AND OLD.category_id IS NULL) THEN
        IF NEW.category_id = 1 THEN
            SET NEW.price_per_hour = 1250.00;
        ELSEIF NEW.category_id = 2 THEN
            SET NEW.price_per_hour = 1600.00;
        ELSEIF NEW.category_id = 3 THEN
            SET NEW.price_per_hour = 1200.00;
        END IF;
    END IF;
END$$

DELIMITER ;

-- STEP 4: Update the operator trigger to include automatic pricing
DELIMITER $$

DROP TRIGGER IF EXISTS `update_machine_from_operator`$$

CREATE TRIGGER `update_machine_from_operator` 
AFTER UPDATE ON `operators`
FOR EACH ROW
BEGIN
    -- Declare variables at the beginning (required in MySQL/MariaDB)
    DECLARE rows_affected INT DEFAULT 0;
    DECLARE machine_price DECIMAL(10,2) DEFAULT NULL;
    
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

-- STEP 5: Verify the changes
-- Check table structure (model_name should be gone)
DESCRIBE `machines`;

-- Check prices are set correctly
SELECT 
    machine_id,
    operator_id,
    category_id,
    price_per_hour,
    equipment_type,
    machine_model
FROM `machines`
ORDER BY category_id, machine_id;

-- Verify triggers exist
SHOW TRIGGERS LIKE 'machines';
SHOW TRIGGERS LIKE 'operators';













