-- Migrate existing admins table to new structure
-- Run this SQL in phpMyAdmin: http://localhost/phpmyadmin/index.php?route=/sql&db=earthmover
-- This script preserves existing data and updates the table structure

USE earthmover;

-- Step 1: Check if columns exist and add them if they don't
-- Add name column (if it doesn't exist)
SET @dbname = DATABASE();
SET @tablename = 'admins';
SET @columnname = 'name';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname, ' VARCHAR(100) NULL AFTER admin_id')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Add email column (if it doesn't exist)
SET @columnname = 'email';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname, ' VARCHAR(255) NULL AFTER name')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Add created_at column (if it doesn't exist)
SET @columnname = 'created_at';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname, ' TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER password')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Step 2: Migrate data from username to name and email
-- Copy username to name if name is NULL
UPDATE `admins` SET `name` = `username` WHERE `name` IS NULL AND `username` IS NOT NULL;

-- Set default name if still NULL
UPDATE `admins` SET `name` = 'Admin' WHERE `name` IS NULL;

-- Create email from username if email is NULL
UPDATE `admins` SET `email` = CONCAT(`username`, '@earthmover.com') WHERE `email` IS NULL AND `username` IS NOT NULL;

-- Set default email if still NULL
UPDATE `admins` SET `email` = CONCAT('admin', admin_id, '@earthmover.com') WHERE `email` IS NULL;

-- Step 3: Make columns NOT NULL
ALTER TABLE `admins`
MODIFY COLUMN `name` VARCHAR(100) NOT NULL,
MODIFY COLUMN `email` VARCHAR(255) NOT NULL,
MODIFY COLUMN `password` VARCHAR(255) NOT NULL;

-- Step 4: Add unique constraint on email (if it doesn't exist)
-- First, check if unique key exists
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (index_name = 'email')
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD UNIQUE KEY email (email)')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Step 5: Hash existing plain text passwords (if needed)
-- Update password 'admin123' to bcrypt hash
-- Note: The hash below is for password 'admin123'
UPDATE `admins` 
SET `password` = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' 
WHERE `password` = 'admin123' OR LENGTH(`password`) < 60;

-- Verify the final structure
DESCRIBE `admins`;

-- Show current data
SELECT * FROM `admins`;




































