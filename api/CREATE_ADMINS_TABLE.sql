-- Create admins table for EarthMover application
-- Run this SQL in phpMyAdmin: http://localhost/phpmyadmin/index.php?route=/sql&db=earthmover

USE earthmover;

-- Create admins table
CREATE TABLE IF NOT EXISTS `admins` (
    `admin_id` INT(11) NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(255) NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`admin_id`),
    UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Note: Admins use email for login and password reset (no phone number)




































