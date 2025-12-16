-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 14, 2025 at 11:07 PM
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
(1, 'TRK-745718', 'Tareq', '000000000', 'Office', 'Tanjimul Islam Tareq', '01568993772', '93, Bernaiya, Shahrasti, Chandpur', NULL, 'standard', 100.00, 'in_transit', NULL, '2025-12-14 16:33:47', '2025-12-14 16:34:26');

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
(1, 1, 'Order Placed', 'Parcel received at source office', 'Main Hub', NULL, '2025-12-14 16:33:47'),
(2, 1, 'In Transit', 'Entrepreneur', 'Chandpur, Bangladesh ', NULL, '2025-12-14 16:34:26');

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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `full_name`, `email`, `password_hash`, `phone`, `role`, `created_at`) VALUES
(1, 'Tanjimul Islam Tareq', 'engineertareqbd@gmail.com', '$2y$10$aIetIUKuZ4mvsILp9vYWiusi7L7J0Rp1YIbfhKD6b5OtzbbVb/acW', '+8801568993772', 'customer', '2025-12-14 16:43:15'),
(2, 'EXPRESS ONE', 'karimuggl@gmail.com', '$2y$10$9pItRZiC.s2Ll2xr0FMspODcl/Csw1/8MOZ1cJbwEV.ePGs20QQzq', '+8801568993772', 'staff', '2025-12-14 16:43:38'),
(6, 'Tanjimul Islam Tareq', 'karimuddddggl@gmail.com', '$2y$10$OAlJPqnd4R9MrkX5uUDP4.oKNpVo5NcbbMvewzTTZxIB1ErpSfZia', '01568993772', 'customer', '2025-12-14 16:46:06');

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
-- AUTO_INCREMENT for table `parcels`
--
ALTER TABLE `parcels`
  MODIFY `parcel_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `parcel_history`
--
ALTER TABLE `parcel_history`
  MODIFY `history_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

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
