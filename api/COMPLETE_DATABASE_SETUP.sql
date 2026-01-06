-- ============================================
-- COMPLETE DATABASE SETUP FOR EARTHMOVER
-- Run this entire file in phpMyAdmin SQL tab
-- ============================================

USE `earthmover`;

-- ============================================
-- PART 1: UPDATE USERS TABLE
-- ============================================

-- Add email column if it doesn't exist
SET @dbname = DATABASE();
SET @tablename = 'users';
SET @columnname = 'email';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname, ' VARCHAR(255) NULL AFTER phone')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Add password column if it doesn't exist
SET @columnname = 'password';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname, ' VARCHAR(255) NULL AFTER email')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Add unique constraint on email (optional - uncomment if you want unique emails)
-- ALTER TABLE `users` ADD UNIQUE KEY `email` (`email`);

-- ============================================
-- PART 2: UPDATE OPERATORS TABLE
-- ============================================

-- Add email column to operators if it doesn't exist
SET @tablename = 'operators';
SET @columnname = 'email';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname, ' VARCHAR(255) NULL AFTER phone')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Add password column to operators if it doesn't exist
SET @columnname = 'password';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname, ' VARCHAR(255) NULL AFTER email')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Add address column to operators if it doesn't exist
SET @columnname = 'address';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname, ' TEXT NULL AFTER password')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Add unique constraint on email for operators (optional)
-- ALTER TABLE `operators` ADD UNIQUE KEY `email` (`email`);

-- ============================================
-- PART 3: UPDATE EXISTING USER WITH EMAIL
-- ============================================

-- Update user with phone 7995778148 (Sattireddy) with email
UPDATE `users` 
SET `email` = 'bhadragrisattireddy@gmail.com'
WHERE `phone` = '7995778148' 
AND (`email` IS NULL OR `email` = '');

-- ============================================
-- PART 4: UPDATE EXISTING OPERATOR WITH EMAIL AND PASSWORD
-- ============================================

-- Update existing operator (operator_id = 1) with email and password
-- Password: test123 (hashed with bcrypt)
UPDATE `operators` 
SET 
    `email` = 'operator@example.com',
    `password` = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    `address` = COALESCE(`address`, 'Operator Address')
WHERE `operator_id` = 1 
AND (`email` IS NULL OR `email` = '' OR `password` IS NULL OR `password` = '');

-- ============================================
-- PART 5: CREATE PASSWORD_RESETS TABLE (if not exists)
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
-- PART 6: VERIFY ALL CHANGES
-- ============================================

-- Check users table structure
DESCRIBE `users`;

-- Check operators table structure
DESCRIBE `operators`;

-- Check password_resets table structure
DESCRIBE `password_resets`;

-- Verify user with email
SELECT user_id, name, phone, email, created_at 
FROM users 
WHERE phone = '7995778148';

-- Verify operator with email and password
SELECT operator_id, name, phone, email, license_no, rc_number, is_verified, availability 
FROM operators 
WHERE operator_id = 1;

-- ============================================
-- SUCCESS MESSAGES
-- ============================================

-- If you see this message, all changes were applied successfully!
-- You can now:
-- 1. Login with email: bhadragrisattireddy@gmail.com (password: your password)
-- 2. Login with phone: 7995778148 (password: your password)
-- 3. Operator login: operator@example.com (password: test123)
-- 4. Signup new users/operators with email support

-- ============================================
-- NOTES:
-- ============================================
-- 1. Email is optional but recommended for login
-- 2. Password is required for login
-- 3. All passwords are hashed using bcrypt
-- 4. Test operator password: test123
-- 5. Users can login with email OR phone
-- 6. Operators can login with email OR phone
-- ============================================

