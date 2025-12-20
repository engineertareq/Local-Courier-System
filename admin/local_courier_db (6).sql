-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 20, 2025 at 10:04 PM
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
-- Table structure for table `branches`
--

CREATE TABLE `branches` (
  `branch_id` int(11) NOT NULL,
  `branch_name` varchar(100) NOT NULL,
  `branch_email` varchar(100) NOT NULL,
  `branch_contact` varchar(20) NOT NULL,
  `branch_address` text NOT NULL,
  `city` varchar(50) NOT NULL,
  `zip_code` varchar(20) NOT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `branches`
--

INSERT INTO `branches` (`branch_id`, `branch_name`, `branch_email`, `branch_contact`, `branch_address`, `city`, `zip_code`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Dhaka Main Hub', 'dhaka@courier.com', '01700000001', '123 Motijheel C/A', 'Dhaka', '1000', 'active', '2025-12-18 07:47:41', '2025-12-18 07:47:41'),
(2, 'Chittagong Branch', 'ctg@courier.com', '01700000002', '45 Agrabad', 'Chittagong', '4000', 'active', '2025-12-18 07:47:41', '2025-12-18 07:47:41'),
(3, 'Sylhet Branch', 'sylhet@courier.com', '01700000003', '78 Zindabazar', 'Sylhet', '3100', 'active', '2025-12-18 07:47:41', '2025-12-18 07:47:41'),
(4, 'BIJOYNAGAR BRANCH', 'bijonagar@gmail.com', '+8801756478924', '93, Bijonagar, Shahrasti, Dhaka', 'Dhaka', '3623', 'active', '2025-12-18 08:00:15', '2025-12-18 08:00:15'),
(5, 'Feni Branch', 'feni@deshcourier.com', '+8801756478985', 'SHAH ALAM TOWER, SHAHIDULLAH KAISER ROAD; Feni Sadar Main PS; Feni-3900; Bangladesh', 'Feni', '3900', 'active', '2025-12-20 06:08:26', '2025-12-20 06:08:26');

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

--
-- Dumping data for table `couriers`
--

INSERT INTO `couriers` (`courier_id`, `user_id`, `vehicle_type`, `vehicle_plate_number`, `status`, `current_latitude`, `current_longitude`) VALUES
(2, 6, 'bike', 'DHK-15496', 'available', 23.75163980, 90.38426190),
(3, 10, 'bike', 'DHK-1549645', 'available', 1.99954000, 124.39700000),
(6, 12, 'truck', 'DHK-15496d', 'available', 23.75241570, 90.39161320),
(8, 7, 'bike', 'DHK-15496fgjgh', 'available', 0.00000000, 0.00000000);

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
  `parcel_type` enum('Parcel','Document') DEFAULT 'Parcel',
  `sender_name` varchar(100) NOT NULL,
  `sender_phone` varchar(20) NOT NULL,
  `sender_address` text NOT NULL,
  `receiver_name` varchar(100) NOT NULL,
  `receiver_phone` varchar(20) NOT NULL,
  `receiver_address` text NOT NULL,
  `weight_kg` decimal(5,2) DEFAULT NULL,
  `delivery_type` enum('standard','express','same_day') DEFAULT 'standard',
  `price` decimal(10,2) NOT NULL,
  `payment_method` varchar(50) DEFAULT 'Cash (Receiver)',
  `current_status` enum('pending','picked_up','in_transit','out_for_delivery','delivered','returned','cancelled') DEFAULT 'pending',
  `assigned_courier_id` int(11) DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `parcels`
--

INSERT INTO `parcels` (`parcel_id`, `tracking_number`, `parcel_type`, `sender_name`, `sender_phone`, `sender_address`, `receiver_name`, `receiver_phone`, `receiver_address`, `weight_kg`, `delivery_type`, `price`, `payment_method`, `current_status`, `assigned_courier_id`, `branch_id`, `created_at`, `updated_at`) VALUES
(3, 'TRK-959431', 'Parcel', 'Tanjimul Islam Tareq', '000000000', 'Office', 'Tanjimul Islam Tareq', '+8801568993772', '93, Bernaiya, Shahrasti, Dhaka', NULL, 'standard', 50.00, 'Cash (Receiver)', 'delivered', NULL, NULL, '2025-12-17 15:53:59', '2025-12-17 16:51:45'),
(5, 'TRK-536597', 'Parcel', 'Tareq', '000000000', 'Office', 'Tanjimul Islam Tareq', '+8801568993772', '93, Bernaiya, Shahrasti, Chandpur', NULL, 'standard', 18000.00, 'Cash (Receiver)', 'picked_up', NULL, NULL, '2025-12-18 08:04:02', '2025-12-18 08:04:02'),
(6, 'TRK-507150', 'Parcel', 'Tanjimul Islam Tareq', '000000000', 'Office', 'Tanjimul Islam Tareq', '+8801568993772', '93, Bernaiya, Shahrasti, Chandpur', NULL, 'standard', 5000.00, 'bkash', 'picked_up', NULL, 1, '2025-12-18 08:17:16', '2025-12-18 08:17:16'),
(7, 'TRK-993374', 'Parcel', 'Tanjimul Islam Tareq', '000000000', 'Office', 'Tanjimul Islam Tareq', '+8801568993772', '93, Bernaiya, Shahrasti, Chandpur', NULL, 'standard', 5000.00, 'bkash', 'picked_up', NULL, 1, '2025-12-18 08:20:53', '2025-12-18 08:20:53'),
(8, 'TRK-111086', 'Parcel', 'Tanjimul Islam Tareq', '000000000', 'Office', 'Tanjimul Islam Tareq', '+8801568993772', '93, Bernaiya, Shahrasti, Chandpur', NULL, 'standard', 5000.00, 'bkash', 'delivered', 6, 1, '2025-12-18 08:21:15', '2025-12-19 09:52:31'),
(9, 'TRK-195302', 'Parcel', 'Tanjimul Islam Tareq', '000000000', 'Office', 'Tanjimul Islam Tareq', '+8801568993772', '93, Bernaiya, Shahrasti, Chandpur', NULL, 'standard', 5000.00, 'bkash', '', 3, 1, '2025-12-18 08:21:32', '2025-12-18 13:59:55'),
(10, 'TRK-925176', 'Parcel', 'Tareq', '000000000', 'Office', 'ABS Sabbir', '+8801309029797', 'Sonargaon Imtiaz Tower, House# 8, 9, 10/3, Free School Street, Kathalbagan', NULL, 'standard', 7000.00, 'bkash', 'delivered', 6, 1, '2025-12-18 08:26:44', '2025-12-20 20:56:36'),
(11, 'TRK-998938', 'Parcel', 'Tareq', '000000000', 'Office', 'Sachinoor Sachi', '+8801901025151', 'House 8,9,10/3, 3rd Floor Free School Street', NULL, 'standard', 6300.00, '2Checkout', 'delivered', 6, 1, '2025-12-18 13:26:50', '2025-12-18 16:19:29'),
(12, 'TRK-770834', 'Parcel', 'Tareq', '000000000', 'Office', 'Sachinoor Sachi', '+8801901025151', 'House 8,9,10/3, 3rd Floor Free School Street', NULL, 'standard', 6300.00, '2Checkout', 'delivered', 2, 1, '2025-12-18 14:07:13', '2025-12-18 14:44:00'),
(13, 'TRK-693113', 'Parcel', 'Tareq', '01568993772', '93, Bernaiya, Shahrasti, Chandpur', 'Tanjimul Islam Tareq', '+8801901025151', 'House 8,9,10/3, 3rd Floor Free School Street', 15.00, 'standard', 2100.00, 'Cash (Sender)', 'picked_up', 6, 2, '2025-12-19 08:33:23', '2025-12-19 09:11:32'),
(14, 'TRK-592087', 'Parcel', 'Tareq', '01568993772', '93, Bernaiya, Shahrasti, Chandpur', 'Tanjimul Islam Tareq', '+8801901025151', '93, Bernaiya, Shahrasti, Chandpur', 12.00, 'standard', 960.00, 'Cash (Receiver)', 'delivered', 6, 4, '2025-12-19 12:20:49', '2025-12-19 15:50:20'),
(15, 'TRK-541989', 'Parcel', 'Sachinoor Sachi', '01568993772', '93, Bernaiya, Shahrasti, Chandpur', 'Tanjimul Islam Tareq', '+8801568993772', '93, Bernaiya, Shahrasti, Chandpur', 10.00, 'standard', 500.00, 'Cash (Receiver)', 'pending', NULL, 2, '2025-12-19 14:53:20', '2025-12-19 14:53:20'),
(17, 'TRK-268218', 'Parcel', 'Sachinoor Sachi', '01719721387', '93, Bernaiya, Shahrasti, Chandpur', 'Tanjimul Islam Tareq', '+8801901025151', 'House 8,9,10/3, 3rd Floor Free School Street', 50.00, 'standard', 4000.00, 'Cash (Sender)', 'pending', NULL, 2, '2025-12-19 15:05:38', '2025-12-19 15:05:38'),
(18, 'TRK-811820', 'Parcel', 'Sachinoor Sachi', '01719721387', '93, Bernaiya, Shahrasti, Chandpur', 'Tanjimul Islam Tareq', '+8801568993772', 'Dhanmondi', 50.00, 'standard', 2500.00, '2Checkout', '', 6, 2, '2025-12-19 15:11:57', '2025-12-20 20:35:21'),
(19, 'TRK-755407', 'Parcel', 'rider', '01715540895', '93, Bernaiya, Shahrasti, Chandpur', 'Tanjimul Islam Tareq', '+8801901025151', 'House 8,9,10/3, 3rd Floor Free School Street', 150.00, 'standard', 7500.00, 'bkash', 'delivered', 6, 1, '2025-12-19 15:30:44', '2025-12-20 20:34:58'),
(20, 'TRK-200962', 'Parcel', 'Tareq', '01568993772', '93, Bernaiya, Shahrasti, Chandpur', 'Tanjimul Islam Tareq', '+8801568993772', 'Chittagong', 120.00, 'standard', 14400.00, 'bkash', 'out_for_delivery', NULL, 2, '2025-12-20 20:33:08', '2025-12-20 20:34:39'),
(21, 'TRK-231142', 'Parcel', 'Sachinoor Sachi', '01719721387', '93, Bernaiya, Shahrasti, Chandpur', 'Tanjimul Islam Tareq', '+8801568993772', 'Dhanmondi', 50.00, 'standard', 4000.00, 'bkash', 'in_transit', NULL, 3, '2025-12-20 20:33:30', '2025-12-20 20:34:09'),
(22, 'TRK-721965', 'Parcel', 'Sachinoor', '01568993772', '93, Bernaiya, Shahrasti, Chandpur', 'Tanjimul Islam Tareq', '+8801568993772', 'Sylhet', 1.50, 'standard', 120.00, 'bkash', 'pending', NULL, 3, '2025-12-20 20:36:04', '2025-12-20 20:36:04'),
(23, 'TRK-869471', 'Parcel', 'Sachinoor Sachi', '01568993772', '93, Bernaiya, Shahrasti, Chandpur', 'Tanjimul Islam Tareq', '+8801855457301', 'SHAH ALAM TOWER, SHAHIDULLAH KAISER ROAD; Feni Sadar Main PS; Feni-3900; Bangladesh', 54.00, 'standard', 4320.00, 'Cash (Receiver)', 'pending', 6, 2, '2025-12-20 20:36:24', '2025-12-20 21:02:38'),
(24, 'TRK-951504', 'Parcel', 'Sachinoor Sachi', '01855457301', 'Dhanmondi', 'Shuvro Sajeeb', '+8801855457301', 'Dhaka', 50.00, 'standard', 5000.00, 'Cash (Sender)', 'pending', 6, 1, '2025-12-20 20:36:50', '2025-12-20 21:01:23');

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
(11, 3, 'Out For Delivery', 'tjtdyj', 'Chandpur, Bangladesh ', NULL, '2025-12-17 16:39:17'),
(12, 5, 'Order Placed', 'Order placed. Type: Document, Weight: 150KG, Loc: Outside Dhaka.', 'Main Hub', NULL, '2025-12-18 08:04:02'),
(13, 6, 'Order Placed', 'Order placed. Type: Document, Weight: 50KG, Loc: Inside Dhaka. Method: bkash', 'Main Hub', NULL, '2025-12-18 08:17:16'),
(14, 7, 'Order Placed', 'Order placed. Type: Document, Weight: 50KG, Loc: Inside Dhaka. Method: bkash', 'Main Hub', NULL, '2025-12-18 08:20:53'),
(15, 8, 'Order Placed', 'Order placed. Type: Document, Weight: 50KG, Loc: Inside Dhaka. Method: bkash', 'Main Hub', NULL, '2025-12-18 08:21:15'),
(16, 9, 'Order Placed', 'Order placed. Type: Document, Weight: 50KG, Loc: Inside Dhaka. Method: bkash', 'Main Hub', NULL, '2025-12-18 08:21:32'),
(17, 10, 'Order Placed', 'Order placed. Type: Document, Weight: 50KG, Loc: Outside Dhaka. Method: bkash', 'Main Hub', NULL, '2025-12-18 08:26:45'),
(18, 11, 'Order Placed', 'Order placed. Type: Document, Weight: 45KG, Loc: Outside Dhaka. Method: 2Checkout', 'Main Hub', NULL, '2025-12-18 13:26:50'),
(19, 11, 'In Transit', '', 'Cumilla', NULL, '2025-12-18 13:48:12'),
(20, 11, 'Courier Assigned', 'Shipment assigned to courier: Sachinoor Sachi', 'Dispatch Center', NULL, '2025-12-18 13:58:27'),
(21, 10, 'Courier Assigned', 'Shipment assigned to courier: Shuvro Sajeeb', 'Dispatch Center', NULL, '2025-12-18 13:58:32'),
(22, 10, 'Courier Assigned', 'Shipment assigned to courier: Shuvro Sajeeb', 'Dispatch Center', NULL, '2025-12-18 13:58:54'),
(23, 9, 'Courier Assigned', 'Shipment assigned to courier: Shuvro Sajeeb', 'Dispatch Center', NULL, '2025-12-18 13:59:56'),
(24, 12, 'Order Placed', 'Order placed. Type: Document, Weight: 45KG, Loc: Outside Dhaka. Method: 2Checkout', 'Main Hub', NULL, '2025-12-18 14:07:13'),
(25, 12, 'Courier Assigned', 'Shipment assigned to courier: Tanjimul Islam Tareq', 'Dispatch Center', NULL, '2025-12-18 14:18:42'),
(26, 12, 'In Transit', 'Courier is driving to destination', 'On Route', NULL, '2025-12-18 14:18:54'),
(27, 12, 'Delivered', 'Delivered successfully', 'On Route', NULL, '2025-12-18 14:44:00'),
(28, 11, 'Courier Assigned', 'Shipment assigned to courier: rider', 'Dispatch Center', NULL, '2025-12-18 14:51:46'),
(29, 10, 'Courier Assigned', 'Shipment assigned to courier: rider', 'Dispatch Center', NULL, '2025-12-18 14:54:20'),
(30, 8, 'Courier Assigned', 'Shipment assigned to courier: rider', 'Dispatch Center', NULL, '2025-12-18 14:59:48'),
(31, 11, 'Out For Delivery', 'Courier is out for delivery.', 'On Route', 12, '2025-12-18 15:02:28'),
(32, 8, 'Out For Delivery', 'Courier is out for delivery.', 'On Route', 12, '2025-12-18 15:02:33'),
(33, 8, 'Out For Delivery', 'Courier is out for delivery.', 'On Route', 12, '2025-12-18 15:02:55'),
(34, 8, 'Out For Delivery', 'Courier is out for delivery.', 'On Route', 12, '2025-12-18 15:12:45'),
(35, 13, 'Order Placed', 'Order placed. Status: Pending. Type: Document, Weight: 15KG, Loc: Outside. Method: Cash (Sender)', 'Main Hub', NULL, '2025-12-19 08:33:24'),
(36, 13, 'Courier Assigned', 'Shipment assigned to courier: rider', 'Dispatch Center', NULL, '2025-12-19 08:34:42'),
(37, 13, 'Picked Up', 'Pickedup from Main Branch', 'Dhaka', NULL, '2025-12-19 09:11:32'),
(38, 8, 'Delivered', 'Package delivered successfully.', 'On Route', 12, '2025-12-19 09:52:31'),
(39, 14, 'Order Placed', 'Order placed. Status: Pending. Type: Document, Weight: 12KG, Loc: Inside. Method: Cash (Receiver)', 'Main Hub', NULL, '2025-12-19 12:20:50'),
(40, 14, 'Courier Assigned', 'Shipment assigned to courier: rider', 'Dispatch Center', NULL, '2025-12-19 12:22:18'),
(41, 14, 'Picked Up', 'Rider Picked Up the Parcel\r\n', 'Motijheel', NULL, '2025-12-19 12:22:58'),
(42, 10, 'In Transit', 'Shipment is on the way.', 'On Route', 12, '2025-12-19 12:26:42'),
(43, 14, 'In Transit', 'Shipment is on the way.', 'On Route', 12, '2025-12-19 12:27:00'),
(44, 15, 'Order Placed', 'Order placed. Status: Pending. Type: Document. Method: Cash (Receiver)', 'Main Hub', 11, '2025-12-19 14:53:20'),
(46, 17, 'Order Placed', 'Order placed. Status: Pending. Type: Document. Method: Cash (Sender)', 'Main Hub', 11, '2025-12-19 15:05:38'),
(47, 18, 'Order Placed', 'Order placed. Status: Pending. Type: Document. Method: 2Checkout', 'Main Hub', 11, '2025-12-19 15:11:57'),
(48, 18, 'Courier Assigned', 'Shipment assigned to courier: rider', 'Dispatch Center', NULL, '2025-12-19 15:13:42'),
(49, 19, 'Order Placed', 'Order placed. Status: Pending. Type: Parcel. Method: bkash', 'Main Hub', 12, '2025-12-19 15:30:44'),
(50, 14, 'Delivered', 'Package delivered successfully.', 'On Route', 12, '2025-12-19 15:50:20'),
(51, 10, 'Picked Up', 'Courier has picked up the parcel.', 'On Route', 12, '2025-12-19 15:50:38'),
(52, 10, 'Out For Delivery', 'Courier is out for delivery.', 'On Route', 12, '2025-12-19 15:50:44'),
(53, 19, 'Courier Assigned', 'Shipment assigned to courier: Hasibul Hasan', 'Dispatch Center', NULL, '2025-12-20 06:07:31'),
(54, 20, 'Order Placed', 'Order placed. Status: Pending. Type: Document, Weight: 120KG, Loc: Outside. Method: bkash', 'Main Hub', NULL, '2025-12-20 20:33:08'),
(55, 21, 'Order Placed', 'Order placed. Status: Pending. Type: Parcel, Weight: 50KG, Loc: Inside. Method: bkash', 'Main Hub', NULL, '2025-12-20 20:33:30'),
(56, 21, 'In Transit', '', 'Dhaka', NULL, '2025-12-20 20:34:09'),
(57, 20, 'Out For Delivery', '', 'Cumilla', NULL, '2025-12-20 20:34:39'),
(58, 19, 'Delivered', '', 'Motijheel', NULL, '2025-12-20 20:34:58'),
(59, 18, 'Failed', '', 'Coxs Bazar', NULL, '2025-12-20 20:35:21'),
(60, 22, 'Order Placed', 'Order placed. Status: Pending. Type: Parcel, Weight: 1.5KG, Loc: Inside. Method: bkash', 'Main Hub', NULL, '2025-12-20 20:36:04'),
(61, 23, 'Order Placed', 'Order placed. Status: Pending. Type: Parcel, Weight: 54KG, Loc: Inside. Method: Cash (Receiver)', 'Main Hub', NULL, '2025-12-20 20:36:24'),
(62, 24, 'Order Placed', 'Order placed. Status: Pending. Type: Document, Weight: 50KG, Loc: Inside. Method: Cash (Sender)', 'Main Hub', NULL, '2025-12-20 20:36:50'),
(63, 10, 'Delivered', 'Package delivered successfully.', 'On Route', 12, '2025-12-20 20:56:36'),
(64, 24, 'Rider Assigned', 'Shipment assigned to courier: Hasibul Hasan', 'Dispatch Center', NULL, '2025-12-20 21:01:24'),
(65, 23, 'Rider Assigned', 'Shipment assigned to rider: Hasibul Hasan', 'Dispatch Center', NULL, '2025-12-20 21:02:39');

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
(1, 'Tanjimul Islam Tareq', 'admin@gmail.com', '$2y$10$mpisD2p.3MoOvLwnmYyKUenV2sV83tvuPNzmiKuTDNao12NFaYMdq', '+8801568993772', 'admin', 'IT', 'Executive', 'Head of IT', '2025-12-14 16:43:15', 'user_1765984912.png', 'Active', 'Full Access', 'Unknown', '6161400761ce560b8f5636dad8955aff5d4e502f5c23b89017592b681a95435375a36b3e1cd8610fd4c0f54cf828e9125735', '2025-12-17 23:12:49'),
(6, 'Tanjimul Islam Tareq', 'karimuddddggl@gmail.com', '$2y$10$OAlJPqnd4R9MrkX5uUDP4.oKNpVo5NcbbMvewzTTZxIB1ErpSfZia', '01568993772', 'admin', 'IT', 'Developer', '', '2025-12-14 16:46:06', 'user_1765983892.png', 'Active', 'View Only', 'Unknown', NULL, NULL),
(7, 'ABS Sabbir', 'abs@gmail.com', '$2y$10$lJHeQwy4gtGhTnGzSPnKEeeGdbxxGPTfHFc5e4.uWG6HkG1Qplaxu', '01309029797', 'staff', 'Sales', 'Executive', 'Sales Exicutive', '2025-12-17 14:56:37', 'user_1765983397.png', 'Active', 'View Only', 'Unknown', NULL, NULL),
(10, 'Shuvro Sajeeb', 'kafhrdfrirhystrhmula@gmail.com', '$2y$10$zDKGXjafB346GX.kQjvMmOAp.nq3Bhlphy8OpREa64LGvPsPcXVBK', '01795611971', 'customer', '', '', '', '2025-12-17 15:24:06', 'user_1765985046.jpg', 'Active', 'View Only', 'Unknown', NULL, NULL),
(11, 'Sachinoor Sachi', 'user@gmail.com', '$2y$10$wSol4yVfJB54q6mU8VgvwuexmbwnHx2ht3/uRiKI759Fp/C8/Bu7a', '01719721387', 'customer', '', '', '', '2025-12-17 16:13:37', NULL, 'Active', 'View Only', 'Unknown', NULL, NULL),
(12, 'Hasibul Hasan', 'rider@gmail.com', '$2y$10$KLiMDbllVwDlqDFOrRG05uEOhRnh.uIlTnpV9tM34mmLRqdRNEqd6', '01715540895', 'staff', 'Sales', '', '', '2025-12-18 14:05:31', 'user_1766159265.jpg', 'Active', 'View Only', 'Unknown', NULL, NULL),
(13, 'Sachinoor Sachi', 'hfkhtfh@gmail.com', '$2y$10$Xcam.X0S9BEK/jywHvc2luRxp1T9UpEJlI760Lx4ckcWTkEqilgLO', NULL, 'customer', NULL, NULL, NULL, '2025-12-19 12:27:47', NULL, 'Active', 'View Only', 'Unknown', NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `branches`
--
ALTER TABLE `branches`
  ADD PRIMARY KEY (`branch_id`),
  ADD UNIQUE KEY `branch_email` (`branch_email`);

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
  ADD KEY `assigned_courier_id` (`assigned_courier_id`),
  ADD KEY `fk_parcel_branch` (`branch_id`);

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
-- AUTO_INCREMENT for table `branches`
--
ALTER TABLE `branches`
  MODIFY `branch_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `couriers`
--
ALTER TABLE `couriers`
  MODIFY `courier_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `delivery_packages`
--
ALTER TABLE `delivery_packages`
  MODIFY `package_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `parcels`
--
ALTER TABLE `parcels`
  MODIFY `parcel_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `parcel_history`
--
ALTER TABLE `parcel_history`
  MODIFY `history_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=66;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

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
  ADD CONSTRAINT `fk_parcel_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`branch_id`) ON DELETE SET NULL,
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
