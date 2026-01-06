-- SQL script to create password_resets table
-- Run this in phpMyAdmin or MySQL command line

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




































