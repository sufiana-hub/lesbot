-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: lesbot-db-server.mysql.database.azure.com:3306
-- Generation Time: Jun 19, 2026 at 03:16 AM
-- Server version: 8.0.44-azure
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `lms_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `category`
--

CREATE TABLE `category` (
  `my_row_id` bigint UNSIGNED NOT NULL ,
  `category_id` int NOT NULL,
  `category_name` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `severity_level` enum('Low','Medium','High','Urgent','Critical') COLLATE utf8mb4_general_ci DEFAULT 'Medium'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `category`
--

INSERT INTO `category` (`my_row_id`, `category_id`, `category_name`, `severity_level`) VALUES
(1, 1, 'WATER LEAKAGE / PLUMBING', 'High'),
(2, 2, 'ELECTRICAL (FAN/LIGHT/SOCKET)', 'Urgent'),
(3, 3, 'WIFI / NETWORK CONNECTIVITY', 'Medium'),
(4, 4, 'FURNITURE (BED/TABLE/CHAIR)', 'Low'),
(5, 5, 'DOOR LOCK / KEY ISSUES', 'High'),
(6, 6, 'PEST CONTROL (INSECTS/SNAKES)', 'Urgent'),
(7, 7, 'CEILING / WALL DAMAGE', 'Medium'),
(8, 8, 'TOILET / DRAINAGE CLOGGED', 'High'),
(9, 9, 'FIRE SAFETY / SMOKE DETECTOR', 'Critical'),
(10, 10, 'OTHERS (SPECIFY IN DESCRIPTION)', 'Medium');

-- --------------------------------------------------------

--
-- Table structure for table `maintenance_history`
--

CREATE TABLE `maintenance_history` (
  `my_row_id` bigint UNSIGNED NOT NULL ,
  `history_id` int NOT NULL,
  `request_id` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `action` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `performed_by` varchar(30) COLLATE utf8mb4_general_ci NOT NULL,
  `remarks` text COLLATE utf8mb4_general_ci,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `maintenance_request`
--

CREATE TABLE `maintenance_request` (
  `my_row_id` bigint UNSIGNED NOT NULL ,
  `request_id` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `student_id` varchar(30) COLLATE utf8mb4_general_ci NOT NULL,
  `category_id` int NOT NULL,
  `description` text COLLATE utf8mb4_general_ci NOT NULL,
  `status` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `priority` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `assigned_staff_id` varchar(30) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `hold_timestamp` datetime DEFAULT NULL,
  `rejected_count` int DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `maintenance_request`
--

INSERT INTO `maintenance_request` (`my_row_id`, `request_id`, `student_id`, `category_id`, `description`, `status`, `priority`, `assigned_staff_id`, `created_at`, `hold_timestamp`, `rejected_count`) VALUES
(3, 'REQ-20260518231204', 'B032410816', 10, 'Ular masuk dalam bilik saya', 'On-Hold', 'Critical', 'STF01', '2026-05-18 15:12:04', '2026-05-18 23:19:26', 0),
(4, 'REQ-20260518232342', 'B032410987', 1, 'My room are in critical conditions and full of water', 'Pending', 'High', 'STF01', '2026-05-18 15:23:42', NULL, 1),
(5, 'REQ-20260521165909', 'B032310712', 7, 'siling tercabut', 'In Progress', 'Medium', 'STF01', '2026-05-21 08:59:09', NULL, 0),
(6, 'REQ-20260617132821', 'B032410816', 1, 'Limitless Vision Test: Direct SQL Injection Check', 'In Progress', 'Urgent', 'STF01', '2026-06-17 05:28:21', NULL, 0),
(7, 'REQ-20260617132850', 'B032410816', 1, 'Limitless Vision Test: Direct SQL Injection Check', 'In Progress', 'Urgent', 'STF01', '2026-06-17 05:28:50', NULL, 0),
(8, 'REQ-20260617135415', 'B032410816', 1, 'Limitless Vision Test: Direct SQL Injection Check', 'In Progress', 'Urgent', 'STF01', '2026-06-17 05:54:15', NULL, 0),
(9, 'REQ-20260617144541', 'B032410816', 1, 'Limitless Vision Test: Direct SQL Injection Check', 'In Progress', 'Urgent', 'STF01', '2026-06-17 06:45:41', NULL, 0),
(10, 'REQ-20260617144641', 'B032410816', 1, 'Limitless Vision Test: Direct SQL Injection Check', 'In Progress', 'Urgent', 'STF01', '2026-06-17 06:46:41', NULL, 0),
(11, 'REQ-20260617173715', 'B032410816', 1, 'Limitless Vision Test: Direct SQL Injection Check', 'In Progress', 'Urgent', 'STF01', '2026-06-17 09:37:15', NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `payment_transactions`
--

CREATE TABLE `payment_transactions` (
  `transaction_id` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `penalty_id` int DEFAULT NULL,
  `matric_number` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `amount_penalty` decimal(10,2) DEFAULT NULL,
  `fpx_gateway_fee` decimal(10,2) DEFAULT '1.00',
  `total_paid` decimal(10,2) DEFAULT NULL,
  `bank_name` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `transaction_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `bank_ref_no` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `penalty_types`
--

CREATE TABLE `penalty_types` (
  `my_row_id` bigint UNSIGNED NOT NULL ,
  `penalty_type_id` int NOT NULL,
  `description` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `default_amount` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `penalty_types`
--

INSERT INTO `penalty_types` (`my_row_id`, `penalty_type_id`, `description`, `default_amount`) VALUES
(1, 1, 'LITTERING / POOR ROOM HYGIENE', 10.00),
(2, 2, 'NOISE DISTURBANCE (AFTER 11 PM)', 20.00),
(3, 3, 'LOSS OF ROOM KEY / ACCESS CARD', 25.00),
(4, 4, 'UNAUTHORIZED ROOM SWAP', 30.00),
(5, 5, 'UNAUTHORIZED ELECTRICAL APPLIANCES', 30.00),
(6, 6, 'UNAUTHORIZED VISITOR IN ROOM', 40.00),
(7, 7, 'ILLEGAL COOKING IN DORMITORY', 45.00),
(8, 8, 'SMOKING / VAPING IN ROOM', 50.00),
(9, 9, 'PROPERTY DAMAGE / VANDALISM (MINOR)', 50.00),
(10, 10, 'TAMPERING WITH FIRE SAFETY EQUIPMENT', 50.00),
(11, 11, 'OTHERS / GENERAL MISCONDUCT', 10.00);

-- --------------------------------------------------------

--
-- Table structure for table `staff`
--

CREATE TABLE `staff` (
  `my_row_id` bigint UNSIGNED NOT NULL ,
  `staff_id` varchar(30) COLLATE utf8mb4_general_ci NOT NULL,
  `phone_num` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `department` varchar(50) COLLATE utf8mb4_general_ci DEFAULT 'Maintenance'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `staff`
--

INSERT INTO `staff` (`my_row_id`, `staff_id`, `phone_num`, `department`) VALUES
(7, 'STF01', '0104654394', 'Maintenance'),
(8, 'STF01', '0104654394', 'Maintenance'),
(9, 'STF02', '0149240191', 'Security'),
(10, 'STF03', '01116194436', 'Security');

-- --------------------------------------------------------

--
-- Table structure for table `student`
--

CREATE TABLE `student` (
  `my_row_id` bigint UNSIGNED NOT NULL ,
  `matric_number` varchar(30) COLLATE utf8mb4_general_ci NOT NULL,
  `room_number` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `year_sem` varchar(30) COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student`
--

INSERT INTO `student` (`my_row_id`, `matric_number`, `room_number`, `year_sem`) VALUES
(4, 'B032410816', 'B1-4-D-06', 'YEAR 1 SEM 2'),
(5, 'B1234567890', 'B1-1-C-04', 'YEAR 1 SEM 2'),
(6, 'B032410987', 'B1-3-B-01', 'YEAR 3 SEM 1'),
(7, 'B032310712', 'B1-1-C-04', 'YEAR 2 SEM 2');

-- --------------------------------------------------------

--
-- Table structure for table `student_payments`
--

CREATE TABLE `student_payments` (
  `payment_id` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `penalty_id` int DEFAULT NULL,
  `matric_number` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `payment_method` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `amount_paid` decimal(10,2) DEFAULT NULL,
  `transaction_ref` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `payment_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_penalties`
--

CREATE TABLE `student_penalties` (
  `penalty_id` int NOT NULL,
  `matric_number` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `penalty_type_id` int NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `date_issued` datetime DEFAULT CURRENT_TIMESTAMP,
  `is_paid` tinyint(1) DEFAULT '0',
  `remarks` text COLLATE utf8mb4_general_ci,
  `issued_by` varchar(30) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_penalties`
--

INSERT INTO `student_penalties` (`penalty_id`, `matric_number`, `penalty_type_id`, `amount`, `date_issued`, `is_paid`, `remarks`, `issued_by`) VALUES
(2, 'B032410816', 6, 10.00, '2026-05-20 22:07:05', 1, NULL, NULL),
(3, 'B032410816', 10, 20.00, '2026-05-21 10:11:57', 1, NULL, NULL),
(4, 'B032310712', 5, 50.00, '2026-05-21 17:28:25', 1, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `student_room_history`
--

CREATE TABLE `student_room_history` (
  `my_row_id` bigint UNSIGNED NOT NULL ,
  `history_id` int NOT NULL,
  `matric_number` varchar(30) COLLATE utf8mb4_general_ci NOT NULL,
  `room_number` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `semester_session` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `move_in_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `system_audit_trail`
--

CREATE TABLE `system_audit_trail` (
  `audit_id` int NOT NULL,
  `admin_id` varchar(30) NOT NULL,
  `action_type` varchar(50) NOT NULL,
  `target_entity` varchar(50) DEFAULT NULL,
  `action_details` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `system_audit_trail`
--

INSERT INTO `system_audit_trail` (`audit_id`, `admin_id`, `action_type`, `target_entity`, `action_details`, `created_at`) VALUES
(56, 'AD001', 'STAFF_REGISTERED', 'ST01', 'REGISTRATION: New Staff Entity [AUNI ZANAWANI] initialized in Unit: Maintenance', '2026-05-17 13:50:01'),
(57, 'AD001', 'ACCESS_HUB', NULL, 'Admin monitored entity archive hub', '2026-05-17 13:50:09'),
(58, 'AD001', 'STAFF_REGISTERED', 'STF01', 'REGISTRATION: New Staff Entity [AUNI ZANAWANI] initialized in Unit: Maintenance', '2026-05-17 13:52:26'),
(59, 'AD001', 'STAFF_REGISTERED', 'STF01', 'REGISTRATION: New Staff Entity [AUNI ZANAWANI] initialized in Unit: Maintenance', '2026-05-18 12:17:52'),
(60, 'AD001', 'STAFF_REGISTERED', 'STF01', 'REGISTRATION: New Staff Entity [AUNI ZANAWANI] initialized in Unit: Maintenance', '2026-05-18 12:27:29'),
(61, 'AD001', 'STAFF_REGISTERED', 'STF02', 'REGISTRATION: New Staff Entity [SUHAIL AZMIN] initialized in Unit: Security', '2026-05-18 15:25:06'),
(62, 'AD001', 'ACCESS_HUB', NULL, 'Admin monitored entity archive hub', '2026-05-18 15:26:55'),
(63, 'AD001', 'ACCESS_HUB', NULL, 'Admin monitored entity archive hub', '2026-05-19 01:09:35'),
(64, 'AD001', 'ACCESS_HUB', NULL, 'Admin monitored entity archive hub', '2026-05-19 01:22:55'),
(65, 'B032410816', 'PAYMENT_COMPLETED', 'PAY-2B3F972D', 'PAYMENT SUCCESS: RM 10.00 via FPX Online Banking. ID: PAY-2B3F972D', '2026-05-20 12:56:18'),
(66, 'SYSTEM_HUB', 'PAYMENT_VERIFIED', '2', 'FPX SETTLEMENT SUCCESS: Real-world transfer verified. Ref: TP2605202994993338', '2026-05-20 14:29:54'),
(67, 'SYSTEM_HUB', 'PAYMENT_VERIFIED', '3', 'FPX SETTLEMENT SUCCESS: Real-world transfer verified. Ref: TP2605213446863149', '2026-05-21 02:12:47'),
(68, 'AD001', 'STAFF_REGISTERED', 'STF03', 'REGISTRATION: New Staff Entity [AINA] initialized in Unit: Security', '2026-05-21 02:14:43'),
(69, 'SYSTEM_HUB', 'PAYMENT_VERIFIED', '4', 'FPX SETTLEMENT SUCCESS: Real-world transfer verified. Ref: TP2605211987719499', '2026-05-21 09:29:25'),
(70, 'AD001', 'ACCESS_HUB', NULL, 'Admin monitored entity archive hub', '2026-05-26 14:01:07'),
(71, 'AD001', 'ACCESS_HUB', NULL, 'Admin monitored entity archive hub', '2026-06-17 01:21:02'),
(72, 'AD001', 'ACCESS_HUB', NULL, 'Admin monitored entity archive hub', '2026-06-17 01:21:13'),
(73, 'AD001', 'ACCESS_HUB', NULL, 'Admin monitored entity archive hub', '2026-06-17 01:21:24'),
(74, 'AD001', 'ACCESS_HUB', NULL, 'Admin monitored entity archive hub', '2026-06-17 01:40:01'),
(75, 'AD001', 'ACCESS_HUB', NULL, 'Admin monitored entity archive hub', '2026-06-17 01:42:58');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_chat_log`
--

CREATE TABLE `tbl_chat_log` (
  `my_row_id` bigint UNSIGNED NOT NULL ,
  `log_id` int NOT NULL,
  `session_id` int NOT NULL,
  `user_message` text COLLATE utf8mb4_general_ci,
  `bot_response` text COLLATE utf8mb4_general_ci,
  `intent_detected` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_chat_session`
--

CREATE TABLE `tbl_chat_session` (
  `my_row_id` bigint UNSIGNED NOT NULL ,
  `session_id` int NOT NULL,
  `user_id` varchar(30) COLLATE utf8mb4_general_ci NOT NULL,
  `start_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `session_status` enum('Active','Completed') COLLATE utf8mb4_general_ci DEFAULT 'Active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `my_row_id` bigint UNSIGNED NOT NULL ,
  `user_id` varchar(30) COLLATE utf8mb4_general_ci NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `role` enum('Student','Staff','Admin') COLLATE utf8mb4_general_ci NOT NULL,
  `reset_token` varchar(64) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `token_expiry` datetime DEFAULT NULL,
  `requires_reset` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`my_row_id`, `user_id`, `name`, `email`, `password`, `role`, `reset_token`, `token_expiry`, `requires_reset`) VALUES
(1, 'AD001', 'ADMIN1', '21DDT21F1150@student.pbu.edu.my', '$argon2id$v=19$m=65536,t=4,p=1$ZTkvNGNESTliNTkweUh2cQ$Axqa8upstV5xo5yhvqjekbuG3Wtu/WyZHYNdCCkHxk8', 'Admin', NULL, NULL, 0),
(12, 'STF01', 'AUNI ZANAWANI', 'auni@gmail.com', '$argon2id$v=19$m=65536,t=4,p=1$T0J2TTl4dTE3Q3JNNTZDRQ$abKwhGUM/wNY/ZIBkJWxb64OAAKCre5YytphpL5N2OI', 'Staff', NULL, NULL, 0),
(13, 'B032410816', 'SUFIANA ADLIN BINTI BAHAROM', 'sufianabaharom@gmail.com', '$argon2id$v=19$m=65536,t=4,p=1$MGZMVTlNTDRZdjRKaFJzdQ$PB9T0ZNQQ+k+KQcYOWzfmFa14wnEwOmfSOE/L0ZxFy0', 'Student', NULL, NULL, 0),
(15, 'B032410987', 'HANI FAKHINAH', 'sufianaadlin4@gmail.com', '$argon2id$v=19$m=65536,t=4,p=1$VUtMVDRVWHZHZHdQeWFMbA$92XhqKTHIiGhSuYFFqe2cRlMqEHxJ6jzKe8nifNXr0Y', 'Student', NULL, NULL, 0),
(16, 'STF02', 'SUHAIL AZMIN', 'suhail@gmail.com', '$argon2id$v=19$m=65536,t=4,p=1$b3RKamhKMXlnOXB5ZWVQNg$61Jmwuxv8fFGQKQc5kO7Vm9zreV6yKrG1iezfsm0RgU', 'Staff', NULL, NULL, 0),
(17, 'STF03', 'AINA', 'aina@gmail.com', '$argon2id$v=19$m=65536,t=4,p=1$LlhJMGxVOVVEM0pUUGtveQ$BYQV1PB6ikX9AygkoCwOlJYtezQTBXYZRhFj+H1PjQo', 'Staff', NULL, NULL, 0),
(18, 'B032310712', 'ANIZA', 'b032510567@student.utem.edu.my', '$argon2id$v=19$m=65536,t=4,p=1$NGg0eEJWb1NlY0hnWTQ4Qw$F+LxTkOF5XADCYXjEkqfclPge77PlZCpGwQsubFbix8', 'Student', NULL, NULL, 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `category`
--
ALTER TABLE `category`
  ADD PRIMARY KEY (`my_row_id`);

--
-- Indexes for table `maintenance_history`
--
ALTER TABLE `maintenance_history`
  ADD PRIMARY KEY (`my_row_id`);

--
-- Indexes for table `maintenance_request`
--
ALTER TABLE `maintenance_request`
  ADD PRIMARY KEY (`my_row_id`);

--
-- Indexes for table `payment_transactions`
--
ALTER TABLE `payment_transactions`
  ADD PRIMARY KEY (`transaction_id`),
  ADD KEY `penalty_id` (`penalty_id`);

--
-- Indexes for table `penalty_types`
--
ALTER TABLE `penalty_types`
  ADD PRIMARY KEY (`my_row_id`);

--
-- Indexes for table `staff`
--
ALTER TABLE `staff`
  ADD PRIMARY KEY (`my_row_id`);

--
-- Indexes for table `student`
--
ALTER TABLE `student`
  ADD PRIMARY KEY (`my_row_id`);

--
-- Indexes for table `student_payments`
--
ALTER TABLE `student_payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `penalty_id` (`penalty_id`);

--
-- Indexes for table `student_penalties`
--
ALTER TABLE `student_penalties`
  ADD PRIMARY KEY (`penalty_id`),
  ADD KEY `fk_issued_by` (`issued_by`);

--
-- Indexes for table `student_room_history`
--
ALTER TABLE `student_room_history`
  ADD PRIMARY KEY (`my_row_id`);

--
-- Indexes for table `system_audit_trail`
--
ALTER TABLE `system_audit_trail`
  ADD PRIMARY KEY (`audit_id`);

--
-- Indexes for table `tbl_chat_log`
--
ALTER TABLE `tbl_chat_log`
  ADD PRIMARY KEY (`my_row_id`);

--
-- Indexes for table `tbl_chat_session`
--
ALTER TABLE `tbl_chat_session`
  ADD PRIMARY KEY (`my_row_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`my_row_id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `category`
--
ALTER TABLE `category`
  MODIFY `my_row_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT , AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `maintenance_history`
--
ALTER TABLE `maintenance_history`
  MODIFY `my_row_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT ;

--
-- AUTO_INCREMENT for table `maintenance_request`
--
ALTER TABLE `maintenance_request`
  MODIFY `my_row_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT , AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `penalty_types`
--
ALTER TABLE `penalty_types`
  MODIFY `my_row_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT , AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `staff`
--
ALTER TABLE `staff`
  MODIFY `my_row_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT , AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `student`
--
ALTER TABLE `student`
  MODIFY `my_row_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT , AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `student_penalties`
--
ALTER TABLE `student_penalties`
  MODIFY `penalty_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `student_room_history`
--
ALTER TABLE `student_room_history`
  MODIFY `my_row_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT ;

--
-- AUTO_INCREMENT for table `system_audit_trail`
--
ALTER TABLE `system_audit_trail`
  MODIFY `audit_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=76;

--
-- AUTO_INCREMENT for table `tbl_chat_log`
--
ALTER TABLE `tbl_chat_log`
  MODIFY `my_row_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT , AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tbl_chat_session`
--
ALTER TABLE `tbl_chat_session`
  MODIFY `my_row_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT , AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `my_row_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT , AUTO_INCREMENT=19;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `payment_transactions`
--
ALTER TABLE `payment_transactions`
  ADD CONSTRAINT `payment_transactions_ibfk_1` FOREIGN KEY (`penalty_id`) REFERENCES `student_penalties` (`penalty_id`);

--
-- Constraints for table `student_payments`
--
ALTER TABLE `student_payments`
  ADD CONSTRAINT `student_payments_ibfk_1` FOREIGN KEY (`penalty_id`) REFERENCES `student_penalties` (`penalty_id`) ON DELETE CASCADE;

--
-- Constraints for table `student_penalties`
--
ALTER TABLE `student_penalties`
  ADD CONSTRAINT `fk_issued_by` FOREIGN KEY (`issued_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
