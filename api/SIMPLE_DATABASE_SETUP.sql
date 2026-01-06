-- ============================================
-- SIMPLE DATABASE SETUP - RUN THIS IN phpMyAdmin
-- ============================================

USE `earthmover`;

-- ============================================
-- ADD COLUMNS TO USERS TABLE
-- ============================================

-- Add email column (ignore error if already exists)
ALTER TABLE `users` ADD COLUMN `email` VARCHAR(255) NULL AFTER `phone`;

-- Add password column (ignore error if already exists)
ALTER TABLE `users` ADD COLUMN `password` VARCHAR(255) NULL AFTER `email`;

-- ============================================
-- ADD COLUMNS TO OPERATORS TABLE
-- ============================================

-- Add email column (ignore error if already exists)
ALTER TABLE `operators` ADD COLUMN `email` VARCHAR(255) NULL AFTER `phone`;

-- Add password column (ignore error if already exists)
ALTER TABLE `operators` ADD COLUMN `password` VARCHAR(255) NULL AFTER `email`;

-- Add address column (ignore error if already exists)
ALTER TABLE `operators` ADD COLUMN `address` TEXT NULL AFTER `password`;

-- ============================================
-- UPDATE EXISTING USER WITH EMAIL
-- ============================================

UPDATE `users` 
SET `email` = 'bhadragrisattireddy@gmail.com'
WHERE `phone` = '7995778148' 
AND (`email` IS NULL OR `email` = '');

-- ============================================
-- UPDATE EXISTING OPERATOR WITH EMAIL AND PASSWORD
-- ============================================

UPDATE `operators` 
SET 
    `email` = 'operator@example.com',
    `password` = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    `address` = COALESCE(`address`, 'Operator Address')
WHERE `operator_id` = 1 
AND (`email` IS NULL OR `email` = '' OR `password` IS NULL OR `password` = '');

-- ============================================
-- CREATE PASSWORD_RESETS TABLE
-- ============================================

CREATE TABLE IF NOT EXISTS `password_resets` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `phone` VARCHAR(20) NOT NULL,
    `role` VARCHAR(20) NOT NULL,
    `otp` VARCHAR(10) NOT NULL,
    `expires_at` DATETIME NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_phone_role` (`phone`, `role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- VERIFY CHANGES
-- ============================================

SELECT 'Users table structure:' AS info;
DESCRIBE `users`;

SELECT 'Operators table structure:' AS info;
DESCRIBE `operators`;

SELECT 'User with email:' AS info;
SELECT user_id, name, phone, email FROM users WHERE phone = '7995778148';

SELECT 'Operator with email:' AS info;
SELECT operator_id, name, phone, email FROM operators WHERE operator_id = 1;

-- ============================================
-- DONE! All changes applied successfully.
-- ============================================




































