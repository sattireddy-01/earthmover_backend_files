-- Add email address to user with phone 7995778148 (Sattireddy)
-- Run this in phpMyAdmin SQL tab

USE `earthmover`;

UPDATE `users` 
SET `email` = 'bhadragrisattireddy@gmail.com' 
WHERE `phone` = '7995778148' 
AND `user_id` = 12;

-- Verify the update
SELECT user_id, name, phone, email FROM users WHERE phone = '7995778148';




































