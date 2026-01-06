-- ============================================
-- CREATE OPERATORS TABLE (if it doesn't exist)
-- Run this in phpMyAdmin SQL tab
-- ============================================

USE `earthmover`;

-- Create operators table if it doesn't exist
CREATE TABLE IF NOT EXISTS `operators` (
    `operator_id` INT(11) NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(100) NOT NULL,
    `phone` VARCHAR(15) NOT NULL,
    `email` VARCHAR(255) DEFAULT NULL,
    `password` VARCHAR(255) NOT NULL,
    `address` TEXT DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`operator_id`),
    UNIQUE KEY `phone` (`phone`),
    UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================
-- ADD EMAIL COLUMN IF MISSING
-- ============================================

-- Check if email column exists, if not add it
ALTER TABLE `operators` 
ADD COLUMN IF NOT EXISTS `email` VARCHAR(255) NULL AFTER `phone`;

-- ============================================
-- TEST DATA (Optional)
-- ============================================

-- Insert a test operator (password: test123)
-- Uncomment and modify as needed:
/*
INSERT INTO `operators` (`name`, `phone`, `email`, `password`, `address`) 
VALUES (
    'Test Operator', 
    '9876543210', 
    'operator@example.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'Test Address'
);
*/

-- ============================================
-- VERIFY TABLE STRUCTURE
-- ============================================

-- Run this to verify the table was created correctly:
-- DESCRIBE `operators`;

-- ============================================
-- NOTES:
-- 1. operator_id is the primary key (not id)
-- 2. Email is optional but recommended
-- 3. Phone must be unique
-- 4. Password is hashed using bcrypt
-- ============================================




































