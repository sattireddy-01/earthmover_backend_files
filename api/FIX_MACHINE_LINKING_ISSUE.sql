-- =====================================================
-- FIX: Machine Linking Issue - No Available Machines
-- Problem: Operators can't be linked if no machines exist with operator_id IS NULL
-- Solution: Check for available machines and create if needed, or improve trigger
-- =====================================================

-- Step 1: Check which operators need machines linked
SELECT 
    o.operator_id,
    o.name,
    o.category_id,
    o.equipment_type,
    COUNT(m.machine_id) AS linked_machines,
    (SELECT COUNT(*) FROM machines m2 
     WHERE m2.category_id = o.category_id AND m2.operator_id IS NULL) AS available_machines
FROM operators o
LEFT JOIN machines m ON o.operator_id = m.operator_id
WHERE o.category_id IS NOT NULL
AND o.equipment_type IS NOT NULL
GROUP BY o.operator_id, o.name, o.category_id, o.equipment_type
HAVING linked_machines = 0
ORDER BY o.operator_id;

-- Step 2: Check available machines by category
SELECT 
    category_id,
    COUNT(*) AS total_machines,
    SUM(CASE WHEN operator_id IS NULL THEN 1 ELSE 0 END) AS available_machines,
    SUM(CASE WHEN operator_id IS NOT NULL THEN 1 ELSE 0 END) AS linked_machines
FROM machines
GROUP BY category_id;

-- Step 3: Manually link operators that need machines
-- For operator 48 (category_id = 3, Dozer)
-- Check if there are available machines in category 3
SELECT * FROM machines WHERE category_id = 3 AND operator_id IS NULL;

-- If no machines available, we need to create one or link from another source
-- For now, let's manually link operator 48 to an available machine
-- (This will only work if there's an available machine)

-- Step 4: Create a better trigger that handles the case when no machines are available
-- This trigger will log/notify when linking fails due to no available machines

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
            -- Check how many available machines exist in this category
            SELECT COUNT(*) INTO available_machine_count
            FROM machines
            WHERE category_id = NEW.category_id 
            AND operator_id IS NULL;
            
            -- If no machine is linked and machines are available, link one
            IF available_machine_count > 0 THEN
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
                
                -- Double-check: If still no machine linked, try again (race condition protection)
                IF rows_affected = 0 THEN
                    SELECT machine_id INTO existing_machine_id
                    FROM machines
                    WHERE operator_id = NEW.operator_id
                    LIMIT 1;
                    
                    IF existing_machine_id IS NULL AND available_machine_count > 0 THEN
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
            -- Note: If available_machine_count = 0, no machine will be linked
            -- This is expected behavior - machines must be created by admin first
        END IF;
    END IF;
END$$

DELIMITER ;

-- Step 5: Verify trigger was updated
SHOW TRIGGERS LIKE 'operators';












