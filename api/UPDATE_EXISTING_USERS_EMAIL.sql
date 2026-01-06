-- ============================================
-- UPDATE EXISTING USERS WITH EMAIL ADDRESSES
-- Run this in phpMyAdmin SQL tab
-- ============================================

USE `earthmover`;

-- Update user with phone 7995778148 (Sattireddy)
UPDATE `users` 
SET `email` = 'bhadragrisattireddy@gmail.com' 
WHERE `phone` = '7995778148';

-- Update user with phone 7675903108 (Harsha) - Example
-- UPDATE `users` 
-- SET `email` = 'harsha@example.com' 
-- WHERE `phone` = '7675903108';

-- Verify the updates
SELECT user_id, name, phone, email, created_at 
FROM users 
ORDER BY user_id DESC;

-- ============================================
-- NOTES:
-- 1. Email is optional during signup
-- 2. Users can login with either phone OR email
-- 3. New signups will automatically save email if provided
-- ============================================




































