-- Update existing admins table to match the new structure
-- Run this SQL in phpMyAdmin: http://localhost/phpmyadmin/index.php?route=/sql&db=earthmover
-- NOTE: This is a simpler version. For safer migration, use MIGRATE_ADMINS_TABLE.sql

USE earthmover;

-- Step 1: Add new columns (check manually if they exist first)
-- If you get "Duplicate column name" error, skip that line
ALTER TABLE `admins` 
ADD COLUMN `name` VARCHAR(100) NULL AFTER `admin_id`;

ALTER TABLE `admins` 
ADD COLUMN `email` VARCHAR(255) NULL AFTER `name`;

ALTER TABLE `admins` 
ADD COLUMN `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `password`;

-- Step 2: Migrate data from username to name and email
UPDATE `admins` SET `name` = `username` WHERE `name` IS NULL AND `username` IS NOT NULL;
UPDATE `admins` SET `name` = 'Admin' WHERE `name` IS NULL;
UPDATE `admins` SET `email` = CONCAT(`username`, '@earthmover.com') WHERE `email` IS NULL AND `username` IS NOT NULL;
UPDATE `admins` SET `email` = CONCAT('admin', admin_id, '@earthmover.com') WHERE `email` IS NULL;

-- Step 3: Make columns NOT NULL
ALTER TABLE `admins`
MODIFY COLUMN `name` VARCHAR(100) NOT NULL,
MODIFY COLUMN `email` VARCHAR(255) NOT NULL,
MODIFY COLUMN `password` VARCHAR(255) NOT NULL;

-- Step 4: Add unique constraint on email
ALTER TABLE `admins`
ADD UNIQUE KEY `email` (`email`);

-- Step 5: Hash existing plain text password 'admin123'
UPDATE `admins` 
SET `password` = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' 
WHERE `password` = 'admin123' OR LENGTH(`password`) < 60;

-- Step 6: (Optional) Remove username column if you no longer need it
-- Uncomment the line below if you want to remove the username column
-- ALTER TABLE `admins` DROP COLUMN `username`;

-- Verify the structure
DESCRIBE `admins`;

-- Show current data
SELECT * FROM `admins`;

