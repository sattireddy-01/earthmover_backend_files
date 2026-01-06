-- ===========================================
-- ADD PASSWORD COLUMN TO USERS TABLE
-- Run this in phpMyAdmin SQL tab
-- ===========================================

USE `earthmover`;

-- Add password column to users table
ALTER TABLE `users` 
ADD COLUMN `password` VARCHAR(255) NULL AFTER `phone`;

-- Add email column (optional, but good to have)
ALTER TABLE `users` 
ADD COLUMN `email` VARCHAR(255) NULL AFTER `phone`;

-- Update existing users with a default password (change this!)
-- Password: test123 (hashed with bcrypt)
-- You should update these passwords after first login
UPDATE `users` 
SET `password` = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' 
WHERE `password` IS NULL;

-- Make password NOT NULL after setting default values
ALTER TABLE `users` 
MODIFY COLUMN `password` VARCHAR(255) NOT NULL;

-- ===========================================
-- IF YOU HAVE AN OPERATORS TABLE, RUN THIS TOO:
-- ===========================================

-- Add password column to operators table (if it exists)
-- ALTER TABLE `operators` 
-- ADD COLUMN `password` VARCHAR(255) NULL AFTER `phone`;

-- ALTER TABLE `operators` 
-- ADD COLUMN `email` VARCHAR(255) NULL AFTER `phone`;

-- UPDATE `operators` 
-- SET `password` = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' 
-- WHERE `password` IS NULL;

-- ALTER TABLE `operators` 
-- MODIFY COLUMN `password` VARCHAR(255) NOT NULL;

-- ===========================================
-- NOTES:
-- The default password hash is for: test123
-- To create your own password hash, use PHP:
-- echo password_hash('your_password', PASSWORD_BCRYPT);
-- ===========================================




































