-- ============================================
-- UPDATE OPERATORS TABLE - ADD EMAIL AND PASSWORD
-- Run this in phpMyAdmin SQL tab
-- ============================================

USE `earthmover`;

-- Add email column if it doesn't exist
ALTER TABLE `operators` 
ADD COLUMN IF NOT EXISTS `email` VARCHAR(255) NULL AFTER `phone`;

-- Add password column if it doesn't exist
ALTER TABLE `operators` 
ADD COLUMN IF NOT EXISTS `password` VARCHAR(255) NULL AFTER `email`;

-- Add address column if it doesn't exist (optional)
ALTER TABLE `operators` 
ADD COLUMN IF NOT EXISTS `address` TEXT NULL AFTER `password`;

-- Add unique constraint on email (if not exists)
-- Note: This will fail if duplicate emails exist, remove duplicates first
-- ALTER TABLE `operators` ADD UNIQUE KEY `email` (`email`);

-- ============================================
-- UPDATE EXISTING OPERATOR WITH EMAIL AND PASSWORD
-- ============================================

-- Update existing operator (operator_id = 1) with email and password
-- Password: test123 (hashed)
UPDATE `operators` 
SET 
    `email` = 'operator@example.com',
    `password` = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    `address` = 'Operator Address'
WHERE `operator_id` = 1;

-- ============================================
-- VERIFY THE UPDATE
-- ============================================

-- Check the updated structure
DESCRIBE `operators`;

-- Check the updated data
SELECT operator_id, name, phone, email, license_no, rc_number, is_verified, availability 
FROM operators;

-- ============================================
-- NOTES:
-- 1. Email is optional but recommended for login
-- 2. Password is required for login
-- 3. Address is optional
-- 4. Default password for test operator: test123
-- ============================================




































