-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 17, 2025 at 08:38 PM
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
-- Database: `local_courier_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `couriers`
--

CREATE TABLE `couriers` (
  `courier_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `vehicle_type` enum('bike','bicycle','van','truck') DEFAULT 'bike',
  `vehicle_plate_number` varchar(50) DEFAULT NULL,
  `status` enum('available','busy','offline') DEFAULT 'offline',
  `current_latitude` decimal(10,8) DEFAULT NULL,
  `current_longitude` decimal(11,8) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `delivery_packages`
--

CREATE TABLE `delivery_packages` (
  `package_id` int(11) NOT NULL,
  `package_name` varchar(100) NOT NULL,
  `delivery_time` varchar(50) NOT NULL,
  `price_inside_dhaka` decimal(10,2) NOT NULL,
  `price_outside_dhaka` decimal(10,2) NOT NULL,
  `status` enum('Active','Inactive') DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `delivery_packages`
--

INSERT INTO `delivery_packages` (`package_id`, `package_name`, `delivery_time`, `price_inside_dhaka`, `price_outside_dhaka`, `status`, `created_at`) VALUES
(1, 'Regular', '5 Days', 50.00, 120.00, 'Active', '2025-12-17 16:30:29'),
(2, 'Standard', '3 Days', 80.00, 140.00, 'Active', '2025-12-17 16:30:29'),
(3, 'Premium', '1 Days', 100.00, 200.00, 'Active', '2025-12-17 16:30:29'),
(4, 'Express', '12', 150.00, 250.00, 'Active', '2025-12-17 17:02:12');

-- --------------------------------------------------------

--
-- Table structure for table `parcels`
--

CREATE TABLE `parcels` (
  `parcel_id` int(11) NOT NULL,
  `tracking_number` varchar(50) NOT NULL,
  `sender_name` varchar(100) NOT NULL,
  `sender_phone` varchar(20) NOT NULL,
  `sender_address` text NOT NULL,
  `receiver_name` varchar(100) NOT NULL,
  `receiver_phone` varchar(20) NOT NULL,
  `receiver_address` text NOT NULL,
  `weight_kg` decimal(5,2) DEFAULT NULL,
  `delivery_type` enum('standard','express','same_day') DEFAULT 'standard',
  `price` decimal(10,2) NOT NULL,
  `current_status` enum('pending','picked_up','in_transit','out_for_delivery','delivered','returned','cancelled') DEFAULT 'pending',
  `assigned_courier_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `parcels`
--

INSERT INTO `parcels` (`parcel_id`, `tracking_number`, `sender_name`, `sender_phone`, `sender_address`, `receiver_name`, `receiver_phone`, `receiver_address`, `weight_kg`, `delivery_type`, `price`, `current_status`, `assigned_courier_id`, `created_at`, `updated_at`) VALUES
(3, 'TRK-959431', 'Tanjimul Islam Tareq', '000000000', 'Office', 'Tanjimul Islam Tareq', '+8801568993772', '93, Bernaiya, Shahrasti, Dhaka', NULL, 'standard', 50.00, 'delivered', NULL, '2025-12-17 15:53:59', '2025-12-17 16:51:45');

-- --------------------------------------------------------

--
-- Table structure for table `parcel_history`
--

CREATE TABLE `parcel_history` (
  `history_id` int(11) NOT NULL,
  `parcel_id` int(11) NOT NULL,
  `status` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `location` varchar(100) DEFAULT NULL,
  `updated_by_user_id` int(11) DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `parcel_history`
--

INSERT INTO `parcel_history` (`history_id`, `parcel_id`, `status`, `description`, `location`, `updated_by_user_id`, `timestamp`) VALUES
(4, 3, 'Order Placed', 'Parcel received at source office', 'Main Hub', NULL, '2025-12-17 15:53:59'),
(5, 3, 'In Transit', 'Will Deliver soon', 'Chandpur, Bangladesh ', NULL, '2025-12-17 15:54:25'),
(6, 3, 'In Transit', 'Initiate to delivery\r\n', 'Cumilla', NULL, '2025-12-17 15:57:13'),
(7, 3, 'In Transit', 'Initiate to delivery\r\n', 'Cumilla', NULL, '2025-12-17 15:58:08'),
(8, 3, 'In Transit', 'Initiate to delivery\r\n', 'Cumilla', NULL, '2025-12-17 15:58:44'),
(9, 3, 'Out For Delivery', 'Going For Delivery\r\n', 'Cumilla', NULL, '2025-12-17 16:00:41'),
(11, 3, 'Out For Delivery', 'tjtdyj', 'Chandpur, Bangladesh ', NULL, '2025-12-17 16:39:17');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `payment_id` int(11) NOT NULL,
  `parcel_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` enum('cash_on_delivery','online_gateway','bank_transfer') DEFAULT 'cash_on_delivery',
  `payment_status` enum('unpaid','paid','refunded') DEFAULT 'unpaid',
  `transaction_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `role` enum('admin','staff','customer') DEFAULT 'customer',
  `department` varchar(100) DEFAULT NULL,
  `designation` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `profile_image` varchar(255) DEFAULT NULL,
  `status` enum('Active','Inactive','Pending') NOT NULL DEFAULT 'Active',
  `permission_group` varchar(50) DEFAULT 'View Only',
  `location` varchar(100) DEFAULT 'Unknown',
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_token_expire` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `full_name`, `email`, `password_hash`, `phone`, `role`, `department`, `designation`, `description`, `created_at`, `profile_image`, `status`, `permission_group`, `location`, `reset_token`, `reset_token_expire`) VALUES
(1, 'Tanjimul Islam Tareq', 'engineertareqbd@gmail.com', '$2y$10$T7p1GZVfuyoDoeH9hnuLUOcQv5gf/1HFJKXBGAKSp.ONFHylPrE4e', '+8801568993772', 'admin', 'IT', 'Executive', 'Head of IT', '2025-12-14 16:43:15', 'user_1765984912.png', 'Active', 'Full Access', 'Unknown', '6161400761ce560b8f5636dad8955aff5d4e502f5c23b89017592b681a95435375a36b3e1cd8610fd4c0f54cf828e9125735', '2025-12-17 23:12:49'),
(6, 'Tanjimul Islam Tareq', 'karimuddddggl@gmail.com', '$2y$10$OAlJPqnd4R9MrkX5uUDP4.oKNpVo5NcbbMvewzTTZxIB1ErpSfZia', '01568993772', 'admin', 'IT', 'Developer', '', '2025-12-14 16:46:06', 'user_1765983892.png', 'Active', 'View Only', 'Unknown', NULL, NULL),
(7, 'ABS Sabbir', 'abs@gmail.com', '$2y$10$lJHeQwy4gtGhTnGzSPnKEeeGdbxxGPTfHFc5e4.uWG6HkG1Qplaxu', '01309029797', 'staff', 'Sales', 'Executive', 'Sales Exicutive', '2025-12-17 14:56:37', 'user_1765983397.png', 'Active', 'View Only', 'Unknown', NULL, NULL),
(10, 'Shuvro Sajeeb', 'kafhrdfrirhystrhmula@gmail.com', '$2y$10$zDKGXjafB346GX.kQjvMmOAp.nq3Bhlphy8OpREa64LGvPsPcXVBK', '01795611971', 'customer', '', '', '', '2025-12-17 15:24:06', 'user_1765985046.jpg', 'Active', 'View Only', 'Unknown', NULL, NULL),
(11, 'Sachinoor Sachi', 'sachinoor@gmail.com', '$2y$10$9FxTiQKuBKuu/.90UL4d5uJ9u26XPrzSqSUX.1SfcD2BRAZF/C6Sm', NULL, 'customer', NULL, NULL, NULL, '2025-12-17 16:13:37', NULL, 'Active', 'View Only', 'Unknown', NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `couriers`
--
ALTER TABLE `couriers`
  ADD PRIMARY KEY (`courier_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `delivery_packages`
--
ALTER TABLE `delivery_packages`
  ADD PRIMARY KEY (`package_id`);

--
-- Indexes for table `parcels`
--
ALTER TABLE `parcels`
  ADD PRIMARY KEY (`parcel_id`),
  ADD UNIQUE KEY `tracking_number` (`tracking_number`),
  ADD KEY `assigned_courier_id` (`assigned_courier_id`);

--
-- Indexes for table `parcel_history`
--
ALTER TABLE `parcel_history`
  ADD PRIMARY KEY (`history_id`),
  ADD KEY `parcel_id` (`parcel_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `parcel_id` (`parcel_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `couriers`
--
ALTER TABLE `couriers`
  MODIFY `courier_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `delivery_packages`
--
ALTER TABLE `delivery_packages`
  MODIFY `package_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `parcels`
--
ALTER TABLE `parcels`
  MODIFY `parcel_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `parcel_history`
--
ALTER TABLE `parcel_history`
  MODIFY `history_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `couriers`
--
ALTER TABLE `couriers`
  ADD CONSTRAINT `couriers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `parcels`
--
ALTER TABLE `parcels`
  ADD CONSTRAINT `parcels_ibfk_1` FOREIGN KEY (`assigned_courier_id`) REFERENCES `couriers` (`courier_id`) ON DELETE SET NULL;

--
-- Constraints for table `parcel_history`
--
ALTER TABLE `parcel_history`
  ADD CONSTRAINT `parcel_history_ibfk_1` FOREIGN KEY (`parcel_id`) REFERENCES `parcels` (`parcel_id`) ON DELETE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`parcel_id`) REFERENCES `parcels` (`parcel_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
