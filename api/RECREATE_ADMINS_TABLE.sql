-- Complete recreation of admins table with correct structure
-- Run this SQL in phpMyAdmin: http://localhost/phpmyadmin/index.php?route=/sql&db=earthmover
-- WARNING: This will delete all existing admin data!

USE earthmover;

-- Drop existing table (WARNING: This deletes all data!)
DROP TABLE IF EXISTS `admins`;

-- Create new admins table with correct structure
CREATE TABLE `admins` (
    `admin_id` INT(11) NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(255) NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`admin_id`),
    UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert a default admin (password: admin123 - hashed)
-- You can change this password later through the app
INSERT INTO `admins` (`name`, `email`, `password`) VALUES
('Admin', 'admin@earthmover.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Verify the structure
DESCRIBE `admins`;




































