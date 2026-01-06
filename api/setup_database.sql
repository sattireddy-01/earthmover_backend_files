-- ===========================================
-- EARTH MOVER DATABASE SETUP SCRIPT
-- Run this in phpMyAdmin
-- ===========================================

-- Create database if it doesn't exist
CREATE DATABASE IF NOT EXISTS `earth_mover` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Use the database
USE `earth_mover`;

-- Create users table (if it doesn't exist)
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL,
    `phone` VARCHAR(20) NOT NULL,
    `email` VARCHAR(255) DEFAULT NULL,
    `password` VARCHAR(255) NOT NULL,
    `address` TEXT DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `phone` (`phone`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create operators table (if it doesn't exist)
CREATE TABLE IF NOT EXISTS `operators` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL,
    `phone` VARCHAR(20) NOT NULL,
    `email` VARCHAR(255) DEFAULT NULL,
    `password` VARCHAR(255) NOT NULL,
    `address` TEXT DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `phone` (`phone`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create password_resets table
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

-- ===========================================
-- TEST DATA (Optional - Remove in production)
-- ===========================================

-- Insert a test user (password: test123 - hashed)
-- You can change this password later
INSERT INTO `users` (`name`, `phone`, `password`, `address`) 
VALUES ('Test User', '7995778148', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Test Address')
ON DUPLICATE KEY UPDATE `name` = `name`;

-- Insert a test operator (password: test123 - hashed)
INSERT INTO `operators` (`name`, `phone`, `password`, `address`) 
VALUES ('Test Operator', '7995778148', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Test Address')
ON DUPLICATE KEY UPDATE `name` = `name`;

-- ===========================================
-- NOTES:
-- The test password 'test123' is hashed using bcrypt
-- To create your own password hash, use PHP:
-- password_hash('your_password', PASSWORD_BCRYPT)
-- ===========================================




































