-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 06, 2026 at 06:23 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `earthmover`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `admin_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`admin_id`, `name`, `email`, `password`, `created_at`) VALUES
(1, 'admin', 'admin@earthmover.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2025-12-23 04:05:31'),
(2, 'Venkatareddy', 'sattireddysabbella7@gmail.com', '$2y$10$TN86FTkxoNgTnhMgYXJ3m.tHdPqk.GropbsILPhQjlNP9KnYckH2S', '2025-12-23 04:09:03');

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `booking_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `operator_id` int(11) DEFAULT NULL,
  `machine_id` int(11) DEFAULT NULL,
  `hours` int(11) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `status` enum('PENDING','ACCEPTED','IN_PROGRESS','COMPLETED') DEFAULT 'PENDING',
  `acceptance` varchar(50) DEFAULT 'PENDING',
  `payment_status` enum('UNPAID','PAID') DEFAULT 'UNPAID',
  `location` varchar(255) DEFAULT 'Not specified',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`booking_id`, `user_id`, `operator_id`, `machine_id`, `hours`, `amount`, `status`, `acceptance`, `payment_status`, `location`, `created_at`) VALUES
(1, 14, NULL, 9, 2116, 34027.00, 'PENDING', 'DECLINED', 'UNPAID', 'Not specified', '2026-01-04 15:46:22'),
(2, 1, 50, 9, 2150, 34933.00, 'COMPLETED', 'ACCEPTED', 'PAID', 'Not specified', '2026-01-04 16:21:13'),
(4, 1, 52, 10, 2, 5000.00, 'COMPLETED', 'ACCEPTED', 'PAID', 'Test Location - Verified 1767544110', '2026-01-04 16:28:30'),
(5, 1, 52, 10, 2, 5000.00, 'COMPLETED', 'ACCEPTED', 'PAID', 'Test Location - Verified 1767544161', '2026-01-04 16:29:21'),
(6, 1, 50, 9, 2312, 37120.00, 'COMPLETED', 'ACCEPTED', 'PAID', 'Not specified', '2026-01-04 17:43:09'),
(7, 1, 45, 7, 19, 380.00, 'COMPLETED', 'ACCEPTED', 'PAID', 'Not specified', '2026-01-04 18:49:12'),
(8, 1, 50, 9, 1, 1600.00, 'PENDING', 'PENDING', 'UNPAID', 'Not specified', '2026-01-06 04:46:55'),
(9, 1, 50, 9, 1021, 16560.00, 'PENDING', 'PENDING', 'UNPAID', 'Not specified', '2026-01-06 04:51:24'),
(10, 1, 50, 9, 1023, 16613.00, 'PENDING', 'PENDING', 'UNPAID', 'Not specified', '2026-01-06 04:53:35'),
(11, 1, 50, 9, 1027, 16720.00, 'PENDING', 'PENDING', 'UNPAID', 'Not specified', '2026-01-06 04:57:41'),
(12, 1, 50, 9, 1033, 16880.00, 'PENDING', 'PENDING', 'UNPAID', 'Not specified', '2026-01-06 05:03:43'),
(13, 1, 50, 9, 1041, 17093.00, 'PENDING', 'PENDING', 'UNPAID', 'Not specified', '2026-01-06 05:12:05');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`category_id`, `category_name`) VALUES
(1, 'JCB'),
(2, 'Excavator'),
(3, 'Dozer'),
(4, 'Crane');

-- --------------------------------------------------------

--
-- Table structure for table `machines`
--

CREATE TABLE `machines` (
  `machine_id` int(11) NOT NULL,
  `operator_id` int(11) DEFAULT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `price_per_hour` decimal(10,2) DEFAULT NULL,
  `specs` text DEFAULT NULL,
  `model_year` int(11) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `equipment_type` varchar(50) DEFAULT NULL,
  `machine_model` varchar(100) DEFAULT NULL,
  `machine_year` int(11) DEFAULT NULL,
  `machine_image_1` varchar(255) DEFAULT NULL,
  `availability` enum('ONLINE','OFFLINE') DEFAULT 'OFFLINE',
  `profile_image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `machines`
--

INSERT INTO `machines` (`machine_id`, `operator_id`, `phone`, `category_id`, `price_per_hour`, `specs`, `model_year`, `image`, `address`, `equipment_type`, `machine_model`, `machine_year`, `machine_image_1`, `availability`, `profile_image`) VALUES
(6, 44, '8886859716', 3, 1200.00, 'Dozer', 2024, 'uploads/machines/machine_1767112331_6953fe8b3a027.png', 'Chennai', 'Dozer', 'Sonalika', 2024, 'uploads/machine_images/machine_44_1_1767290797.jpg', 'ONLINE', NULL),
(7, 45, '6302640622', 3, 1200.00, 'Dozer', 2024, 'uploads/machines/machine_1767157015_6954ad1707704.png', 'Chennai', 'Dozer', 'John Deer 5310', 2025, 'uploads/machine_images/machine_45_1_1767292591.jpg', 'ONLINE', NULL),
(9, 50, '9985256766', 2, 1600.00, 'Excavator', 2024, 'uploads/machines/machine_1767157112_6954ad784d3a2.png', 'Andhra Pradesh', 'Excavator', 'Tata Hitachi Ex 200', 2025, 'uploads/machine_images/machine_50_1_1767337835.jpg', 'ONLINE', NULL),
(10, 43, '7337381175', 1, 1250.00, 'Backhoe Loader', 2024, 'uploads/machines/machine_1767157141_6954ad95ad4b7.png', 'Andhra Pradesh', 'Backhoe Loader', 'JCB 3DX', 2024, 'uploads/machine_images/machine_43_1_1767289983.jpg', 'ONLINE', 'uploads/profile_images/operator_43_profile_1767424369.jpg'),
(11, 48, '7286056981', 3, 1200.00, 'Dozer', 2023, 'uploads/machine_images/machine_48_1_1767326682.jpg', 'Chennai', 'Dozer', 'John Deer 5050', 2023, 'uploads/machine_images/machine_48_1_1767326682.jpg', 'ONLINE', NULL),
(12, 51, '7675903108', 2, 1600.00, 'Excavator', 2024, 'uploads/machine_images/machine_51_1_1767338055.jpg', 'Chennai', 'Excavator', 'Tata Hitachi 110', 2024, 'uploads/machine_images/machine_51_1_1767338055.jpg', 'ONLINE', 'uploads/profile_images/operator_51_profile_1767424274.jpg'),
(13, 52, '6300996266', 2, 1600.00, 'Excavator', 2023, 'uploads/machine_images/machine_52_1_1767338722.jpg', 'Chennai', 'Excavator', 'Tata Hitachi 110', 2023, 'uploads/machine_images/machine_52_1_1767338722.jpg', 'ONLINE', NULL);

--
-- Triggers `machines`
--
DELIMITER $$
CREATE TRIGGER `auto_set_machine_price` BEFORE INSERT ON `machines` FOR EACH ROW BEGIN
    IF NEW.category_id = 1 THEN
        SET NEW.price_per_hour = 1250.00;
    ELSEIF NEW.category_id = 2 THEN
        SET NEW.price_per_hour = 1600.00;
    ELSEIF NEW.category_id = 3 THEN
        SET NEW.price_per_hour = 1200.00;
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `auto_update_machine_price` BEFORE UPDATE ON `machines` FOR EACH ROW BEGIN
    IF NEW.category_id != OLD.category_id OR (NEW.category_id IS NOT NULL AND OLD.category_id IS NULL) THEN
        IF NEW.category_id = 1 THEN
            SET NEW.price_per_hour = 1250.00;
        ELSEIF NEW.category_id = 2 THEN
            SET NEW.price_per_hour = 1600.00;
        ELSEIF NEW.category_id = 3 THEN
            SET NEW.price_per_hour = 1200.00;
        END IF;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `operators`
--

CREATE TABLE `operators` (
  `operator_id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `license_no` varchar(50) DEFAULT NULL,
  `rc_number` varchar(50) DEFAULT NULL,
  `equipment_type` varchar(50) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `machine_model` varchar(100) DEFAULT NULL,
  `machine_year` int(11) DEFAULT NULL,
  `machine_image_1` varchar(255) DEFAULT NULL,
  `approve_status` enum('APPROVED','REJECTED','PENDING') DEFAULT 'PENDING',
  `approval_pending` tinyint(1) DEFAULT 1,
  `availability` enum('ONLINE','OFFLINE') DEFAULT 'OFFLINE',
  `profile_image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `operators`
--

INSERT INTO `operators` (`operator_id`, `name`, `phone`, `email`, `password`, `address`, `license_no`, `rc_number`, `equipment_type`, `category_id`, `machine_model`, `machine_year`, `machine_image_1`, `approve_status`, `approval_pending`, `availability`, `profile_image`, `created_at`) VALUES
(1, 'Operator Name', '9876543211', NULL, NULL, NULL, 'LIC123456', 'RC789012', NULL, NULL, NULL, NULL, NULL, 'APPROVED', 0, 'ONLINE', NULL, '2025-12-16 04:42:46'),
(43, 'Rocky', '7337381175', 'bhadradrisattireddys4240.sse@saveetha.com', '$2y$10$btevrMtjeUkarj3PI74lt.5BWLYaKDncwHE4rKx39Q.7LGMNWW8yi', 'Andhra Pradesh', 'LIC733', 'RC744', 'Backhoe Loader', 1, 'JCB 3DX', 2024, 'uploads/machine_images/machine_43_1_1767289983.jpg', 'APPROVED', 0, 'ONLINE', 'uploads/profile_images/operator_43_profile_1767424369.jpg', '2026-01-01 17:51:07'),
(44, 'Sai', '8886859716', 'saikrishna918@gmail.com', '$2y$10$V6nJbUcmeij3qxHrjRXNB.MKq8Tm46Rr91kAbtFFgumth3oWvRlXe', 'Chennai', 'LIC88', 'RC88', 'Dozer', 3, 'Sonalika', 2024, 'uploads/machine_images/machine_44_1_1767290797.jpg', 'APPROVED', 0, 'ONLINE', NULL, '2026-01-01 18:05:39'),
(45, 'Tarun Reddy', '6302640622', 'saikrishna26981@gmail.com', '$2y$10$/ZmzQ3gLBIR99oOahcXTWuUMmqBwWQZS/lg60MRJJAOZZYxNyH/4u', 'Chennai', 'LIC63', 'RC63', 'Dozer', 3, 'John Deer 5310', 2025, 'uploads/machine_images/machine_45_1_1767292591.jpg', 'APPROVED', 0, 'ONLINE', NULL, '2026-01-01 18:35:40'),
(48, 'Harish', '7286056981', 'narrinikesh1810@gmail.com', '$2y$10$36/B7segLK4EHIXugiExoOraDbuCl7XV8PDkHTM8QgfMCdtCWMhj.', 'Chennai', 'LIC72', 'RC72', 'Dozer', 3, 'John Deer 5050', 2023, 'uploads/machine_images/machine_48_1_1767326682.jpg', 'APPROVED', 0, 'ONLINE', NULL, '2026-01-02 04:03:53'),
(50, 'Siva Reddy', '9985256766', 'sivanagireddysabbella@gmail.com', '$2y$10$fjmG8vWCokrqD.oltYH1Ne4lxrBrM5mCF86XkkNAp/D1CSaa1WSYO', 'Andhra Pradesh', 'LIC99', 'RC99', 'Excavator', 2, 'Tata Hitachi Ex 200', 2025, 'uploads/machine_images/machine_50_1_1767337835.jpg', 'APPROVED', 0, 'ONLINE', NULL, '2026-01-02 07:09:11'),
(51, 'Harsha', '7675903108', 'bhvc905@gmail.com', '$2y$10$4SzEwd2HXQPnveYZTyd8BuGewOR8yi3LQLsKCa9By8WQMx0ihraxm', 'Chennai', 'LIC76', 'RC76', 'Excavator', 2, 'Tata Hitachi 110', 2024, 'uploads/machine_images/machine_51_1_1767338055.jpg', 'APPROVED', 0, 'ONLINE', 'uploads/profile_images/operator_51_profile_1767424274.jpg', '2026-01-02 07:13:01'),
(52, 'Vardhan', '6300996266', 'saitejswi71@gmail.com', '$2y$10$DHuGdrhnstN61ioXimJp5uFvAWob//TDVIYhtpw1YNy3KVPhN/PdC', 'Chennai', 'LIC63', 'RC63', 'Excavator', 2, 'Tata Hitachi 110', 2023, 'uploads/machine_images/machine_52_1_1767338722.jpg', 'APPROVED', 0, 'ONLINE', NULL, '2026-01-02 07:23:44');

--
-- Triggers `operators`
--
DELIMITER $$
CREATE TRIGGER `update_machine_from_operator` AFTER UPDATE ON `operators` FOR EACH ROW BEGIN
    DECLARE rows_affected INT DEFAULT 0;
    DECLARE machine_price DECIMAL(10,2) DEFAULT NULL;
    DECLARE existing_machine_id INT DEFAULT NULL;
    
    IF NEW.category_id IS NOT NULL AND NEW.equipment_type IS NOT NULL THEN
        -- Set price based on category_id
        IF NEW.category_id = 1 THEN
            SET machine_price = 1250.00;
        ELSEIF NEW.category_id = 2 THEN
            SET machine_price = 1600.00;
        ELSEIF NEW.category_id = 3 THEN
            SET machine_price = 1200.00;
        END IF;
        
        -- Check if operator already has a machine linked
        SELECT machine_id INTO existing_machine_id
        FROM machines
        WHERE operator_id = NEW.operator_id
        LIMIT 1;
        
        -- If operator already has a machine, update that one
        IF existing_machine_id IS NOT NULL THEN
            UPDATE `machines`
            SET 
                phone = NEW.phone,
                address = NEW.address,
                equipment_type = NEW.equipment_type,
                machine_model = NEW.machine_model,
                machine_year = NEW.machine_year,
                machine_image_1 = NEW.machine_image_1,
                availability = NEW.availability,
                profile_image = NEW.profile_image,
                price_per_hour = machine_price,
                category_id = NEW.category_id
            WHERE machine_id = existing_machine_id;
        ELSE
            -- If no machine is linked, link one from the same category
            -- Double-check that operator still doesn't have a machine
            SELECT machine_id INTO existing_machine_id
            FROM machines
            WHERE operator_id = NEW.operator_id
            LIMIT 1;
            
            IF existing_machine_id IS NULL THEN
                UPDATE `machines`
                SET 
                    operator_id = NEW.operator_id,
                    phone = NEW.phone,
                    address = NEW.address,
                    equipment_type = NEW.equipment_type,
                    machine_model = NEW.machine_model,
                    machine_year = NEW.machine_year,
                    machine_image_1 = NEW.machine_image_1,
                    availability = NEW.availability,
                    profile_image = NEW.profile_image,
                    price_per_hour = machine_price,
                    category_id = NEW.category_id
                WHERE category_id = NEW.category_id 
                AND operator_id IS NULL
                LIMIT 1;
            END IF;
        END IF;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `operator_earnings`
--

CREATE TABLE `operator_earnings` (
  `earning_id` int(11) NOT NULL,
  `operator_id` int(11) DEFAULT NULL,
  `booking_id` int(11) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `operator_earnings`
--

INSERT INTO `operator_earnings` (`earning_id`, `operator_id`, `booking_id`, `amount`, `created_at`) VALUES
(1, 1, 2, 11200.00, '2025-12-16 08:28:33');

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `phone` varchar(255) NOT NULL,
  `role` varchar(20) NOT NULL,
  `otp` varchar(10) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `password_resets`
--

INSERT INTO `password_resets` (`id`, `phone`, `role`, `otp`, `expires_at`, `created_at`) VALUES
(14, 'bhadradrisattireddy@', 'user', '443919', '2025-12-23 05:50:59', '2025-12-23 04:45:59'),
(15, 'bhadradrisattireddy@', 'user', '708679', '2025-12-23 05:52:58', '2025-12-23 04:47:58'),
(16, 'bhadradrisattireddy@', 'user', '279166', '2025-12-23 06:32:59', '2025-12-23 05:27:59'),
(17, 'bhadradrisattireddy@', 'user', '921933', '2025-12-23 06:36:52', '2025-12-23 05:31:52'),
(18, 'bhadradrisattireddy@', 'user', '193826', '2025-12-23 07:54:25', '2025-12-23 06:49:25'),
(19, 'bhadradrisattireddy@', 'user', '735370', '2025-12-23 07:55:59', '2025-12-23 06:50:59'),
(20, 'bhadradrisattireddy@', 'user', '166977', '2025-12-23 08:05:22', '2025-12-23 07:00:22'),
(21, 'bhadradrisattireddy@', 'user', '155721', '2025-12-23 08:51:07', '2025-12-23 07:46:07'),
(22, 'bhadradrisattireddy@', 'user', '824440', '2025-12-23 08:51:12', '2025-12-23 07:46:12'),
(23, 'bhadradrisattireddy@', 'user', '084933', '2025-12-23 08:51:12', '2025-12-23 07:46:12'),
(24, 'bhadradrisattireddy@', 'user', '771054', '2025-12-23 08:53:23', '2025-12-23 07:48:23'),
(25, 'bhadradrisattireddy@', 'user', '646058', '2025-12-23 08:58:31', '2025-12-23 07:53:31'),
(26, 'bhadradrisattireddy@', 'user', '581143', '2025-12-23 09:09:32', '2025-12-23 08:04:32'),
(27, 'bhadradrisattireddy@', 'user', '882286', '2025-12-23 09:13:58', '2025-12-23 08:08:58'),
(28, 'bhadradrisattireddy@', 'user', '672174', '2025-12-23 09:15:35', '2025-12-23 08:10:35'),
(29, 'bhadradrisattireddy@', 'user', '580994', '2025-12-23 09:23:34', '2025-12-23 08:18:34');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `payment_id` int(11) NOT NULL,
  `booking_id` int(11) DEFAULT NULL,
  `payment_method` enum('UPI','CARD','WALLET') DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `payment_status` enum('SUCCESS','FAILED') DEFAULT NULL,
  `payment_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`payment_id`, `booking_id`, `payment_method`, `amount`, `payment_status`, `payment_date`) VALUES
(1, 1, 'UPI', 11200.00, 'SUCCESS', '2025-12-16 08:18:55'),
(2, 1, 'UPI', 11200.00, 'SUCCESS', '2025-12-16 08:19:35');

-- --------------------------------------------------------

--
-- Table structure for table `ratings`
--

CREATE TABLE `ratings` (
  `rating_id` int(11) NOT NULL,
  `booking_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `operator_id` int(11) DEFAULT NULL,
  `rating` int(11) DEFAULT NULL CHECK (`rating` between 1 and 5),
  `feedback` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ratings`
--

INSERT INTO `ratings` (`rating_id`, `booking_id`, `user_id`, `operator_id`, `rating`, `feedback`, `created_at`) VALUES
(1, 2, 1, 1, 5, 'Great operator, completed work on time.', '2025-12-16 08:28:33');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `phone` varchar(15) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `address` text DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `profile_picture` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `name`, `phone`, `email`, `password`, `address`, `location`, `latitude`, `longitude`, `profile_picture`, `created_at`) VALUES
(1, 'Sabbella', '7995778148', 'bhadradrisattireddy@gmail.com', '$2y$10$d5sIsZ2dJJWXTMC1qg9fpOgUwIU6gbe4xkacTGXHkanRCMN9byLxq', 'Chennai', 'Saveetha Nagar, Thandalam, Kanchipuram - Chennai Rd, Chennai, Kuthambakkam, Tamil Nadu 602105, India', 13.02802380, 80.01620910, 'uploads/profiles/user_1_1767545221.jpg', '2026-01-04 16:19:42'),
(2, 'Siva Nagi Reddy', '9985256766', 'sivanagireddysabbella@gmail.com', '$2y$10$coHB780kILOM8E.4A8XH.ejwmYd.GlLLiNRbZd0FeZKZwB/rYu0OS', 'Chennai', 'Saveetha Nagar, Thandalam, Kanchipuram - Chennai Rd, Chennai, Kuthambakkam, Tamil Nadu 602105, India', 13.02837080, 80.01627870, NULL, '2026-01-05 04:56:44');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`booking_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `operator_id` (`operator_id`),
  ADD KEY `machine_id` (`machine_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`);

--
-- Indexes for table `machines`
--
ALTER TABLE `machines`
  ADD PRIMARY KEY (`machine_id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `idx_operator_id` (`operator_id`);

--
-- Indexes for table `operators`
--
ALTER TABLE `operators`
  ADD PRIMARY KEY (`operator_id`),
  ADD UNIQUE KEY `phone` (`phone`);

--
-- Indexes for table `operator_earnings`
--
ALTER TABLE `operator_earnings`
  ADD PRIMARY KEY (`earning_id`),
  ADD KEY `operator_id` (`operator_id`),
  ADD KEY `booking_id` (`booking_id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_phone_role` (`phone`,`role`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `booking_id` (`booking_id`);

--
-- Indexes for table `ratings`
--
ALTER TABLE `ratings`
  ADD PRIMARY KEY (`rating_id`),
  ADD KEY `booking_id` (`booking_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `phone` (`phone`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `booking_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `machines`
--
ALTER TABLE `machines`
  MODIFY `machine_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `operators`
--
ALTER TABLE `operators`
  MODIFY `operator_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT for table `operator_earnings`
--
ALTER TABLE `operator_earnings`
  MODIFY `earning_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `ratings`
--
ALTER TABLE `ratings`
  MODIFY `rating_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`operator_id`) REFERENCES `operators` (`operator_id`),
  ADD CONSTRAINT `bookings_ibfk_3` FOREIGN KEY (`machine_id`) REFERENCES `machines` (`machine_id`);

--
-- Constraints for table `machines`
--
ALTER TABLE `machines`
  ADD CONSTRAINT `fk_machines_operator` FOREIGN KEY (`operator_id`) REFERENCES `operators` (`operator_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `machines_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`);

--
-- Constraints for table `operator_earnings`
--
ALTER TABLE `operator_earnings`
  ADD CONSTRAINT `operator_earnings_ibfk_1` FOREIGN KEY (`operator_id`) REFERENCES `operators` (`operator_id`),
  ADD CONSTRAINT `operator_earnings_ibfk_2` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`booking_id`);

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`booking_id`);

--
-- Constraints for table `ratings`
--
ALTER TABLE `ratings`
  ADD CONSTRAINT `ratings_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`booking_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
