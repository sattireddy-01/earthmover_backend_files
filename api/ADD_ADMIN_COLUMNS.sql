-- Add required columns to existing admins table
-- Run this in phpMyAdmin: http://localhost/phpmyadmin/index.php?route=/sql&db=earthmover

USE earthmover;

-- Step 1: Add name column
ALTER TABLE `admins` 
ADD COLUMN `name` VARCHAR(100) NULL AFTER `admin_id`;

-- Step 2: Add email column
ALTER TABLE `admins` 
ADD COLUMN `email` VARCHAR(255) NULL AFTER `name`;

-- Step 3: Add created_at column
ALTER TABLE `admins` 
ADD COLUMN `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `password`;

-- Step 4: Migrate data - Copy username to name
UPDATE `admins` SET `name` = `username` WHERE `name` IS NULL;

-- Step 5: Create email from username (e.g., "admin" becomes "admin@earthmover.com")
UPDATE `admins` SET `email` = CONCAT(`username`, '@earthmover.com') WHERE `email` IS NULL;

-- Step 6: Make name and email required (NOT NULL)
ALTER TABLE `admins`
MODIFY COLUMN `name` VARCHAR(100) NOT NULL,
MODIFY COLUMN `email` VARCHAR(255) NOT NULL;

-- Step 7: Add unique constraint on email
ALTER TABLE `admins`
ADD UNIQUE KEY `email` (`email`);

-- Step 8: Hash the existing password 'admin123' to bcrypt format
-- The hash below is for password 'admin123'
UPDATE `admins` 
SET `password` = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' 
WHERE `password` = 'admin123';

-- Verify the structure
DESCRIBE `admins`;

-- Show the updated data
SELECT * FROM `admins`;




































