-- =====================================================
-- Safe Script to Remove model_name Column
-- This version checks if column exists before dropping
-- =====================================================

-- Check if model_name column exists and drop it
SET @col_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'machines' 
    AND COLUMN_NAME = 'model_name'
);

SET @sql = IF(@col_exists > 0,
    'ALTER TABLE `machines` DROP COLUMN `model_name`',
    'SELECT "Column model_name does not exist, skipping drop" AS message'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;













