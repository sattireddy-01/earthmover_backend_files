-- Remove username column from admins table
-- Run this in phpMyAdmin: http://localhost/phpmyadmin/index.php?route=/sql&db=earthmover

USE earthmover;

-- Remove the username column
ALTER TABLE `admins` 
DROP COLUMN `username`;

-- Verify the structure (should show: admin_id, name, email, password, created_at)
DESCRIBE `admins`;

-- Show the updated data
SELECT * FROM `admins`;




































