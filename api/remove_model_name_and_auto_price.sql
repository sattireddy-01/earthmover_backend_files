-- =====================================================
-- SQL Script to Remove model_name Column and Set Auto Pricing
-- Based on category_id:
--   category_id = 1 (Backhoe Loader) → 1250
--   category_id = 2 (Excavator) → 1600
--   category_id = 3 (Dozer) → 1200
-- =====================================================

-- Step 1: Remove model_name column from machines table
-- Note: DROP COLUMN IF EXISTS may not work in older MariaDB versions
-- If you get an error, check if column exists first, then drop it manually
ALTER TABLE `machines` 
DROP COLUMN `model_name`;

-- Step 2: Update existing prices based on category_id
UPDATE `machines` 
SET `price_per_hour` = 1250.00 
WHERE `category_id` = 1;

UPDATE `machines` 
SET `price_per_hour` = 1600.00 
WHERE `category_id` = 2;

UPDATE `machines` 
SET `price_per_hour` = 1200.00 
WHERE `category_id` = 3;

-- Step 3: Create trigger to automatically set price when category_id is set/updated
DELIMITER $$

DROP TRIGGER IF EXISTS `auto_set_machine_price`$$

CREATE TRIGGER `auto_set_machine_price`
BEFORE INSERT ON `machines`
FOR EACH ROW
BEGIN
    -- Set price based on category_id
    IF NEW.category_id = 1 THEN
        SET NEW.price_per_hour = 1250.00;
    ELSEIF NEW.category_id = 2 THEN
        SET NEW.price_per_hour = 1600.00;
    ELSEIF NEW.category_id = 3 THEN
        SET NEW.price_per_hour = 1200.00;
    END IF;
END$$

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

-- Step 4: Also update price when operator's category_id is set (via the existing trigger)
-- We need to modify the existing update_machine_from_operator trigger to include price
DELIMITER $$

DROP TRIGGER IF EXISTS `update_machine_from_operator`$$

CREATE TRIGGER `update_machine_from_operator` 
AFTER UPDATE ON `operators`
FOR EACH ROW
BEGIN
    -- Declare variable at the beginning (required in MySQL/MariaDB)
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

-- Step 5: Verify changes
SELECT 
    machine_id,
    operator_id,
    category_id,
    price_per_hour,
    equipment_type,
    machine_model
FROM `machines`
ORDER BY category_id, machine_id;

-- Step 6: Show triggers
SHOW TRIGGERS LIKE 'machines';
SHOW TRIGGERS LIKE 'operators';

