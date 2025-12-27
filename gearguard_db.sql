-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 27, 2025 at 10:55 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `gearguard_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `equipment`
--

CREATE TABLE `equipment` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `serial_number` varchar(100) DEFAULT NULL,
  `model` varchar(100) DEFAULT NULL,
  `location` varchar(100) DEFAULT NULL,
  `preferred_tech_id` int(11) DEFAULT NULL,
  `team_id` int(11) DEFAULT NULL,
  `status` enum('Operational','Maintenance','Decommissioned') DEFAULT 'Operational',
  `next_service_date` date DEFAULT NULL,
  `purchase_date` date DEFAULT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `equipment`
--

INSERT INTO `equipment` (`id`, `name`, `serial_number`, `model`, `location`, `preferred_tech_id`, `team_id`, `status`, `next_service_date`, `purchase_date`, `description`) VALUES
(1, 'Hydraulic Press X1', 'SN-FIX-6758', NULL, NULL, NULL, NULL, 'Operational', '2025-12-29', NULL, 'Main production press'),
(2, 'Conveyor Belt A', 'SN-FIX-2575', NULL, NULL, NULL, NULL, 'Maintenance', '2025-12-22', NULL, 'Assembly line belt'),
(3, 'Server Rack Main', 'SN-FIX-2603', NULL, NULL, NULL, NULL, 'Operational', '2026-01-26', NULL, 'Primary data center'),
(4, 'Cooling Tower B', 'SN-FIX-5288', NULL, NULL, NULL, NULL, 'Decommissioned', NULL, NULL, 'Old cooling unit'),
(5, 'Forklift Z500', 'SN-FIX-8631', NULL, NULL, NULL, NULL, 'Operational', '2026-04-26', NULL, 'Warehouse forklift'),
(6, 'CNC Machine 01', 'SN-FIX-7293', NULL, NULL, NULL, NULL, 'Operational', '2026-01-01', NULL, 'Precision cutting'),
(7, 'Generator Backup', 'SN-FIX-574', NULL, NULL, NULL, NULL, 'Operational', '2026-01-10', NULL, 'Emergency power'),
(8, 'Drill Press Heavy', 'SN-FIX-990', NULL, NULL, NULL, NULL, 'Operational', '2026-02-25', NULL, 'Heavy duty drilling'),
(9, 'Network Switch Core', 'SN-FIX-3228', NULL, NULL, NULL, NULL, 'Operational', '2026-03-27', NULL, 'Core network switch'),
(10, 'Production Robot Arm', 'SN-FIX-3171', NULL, NULL, NULL, NULL, 'Maintenance', '2025-12-26', NULL, 'Robotic assembly arm'),
(11, 'hydraulic press', 'SN-1223', 'x-500', 'press room', NULL, NULL, 'Operational', '2025-12-28', NULL, 'circuit failure\r\n');

-- --------------------------------------------------------

--
-- Table structure for table `requests`
--

CREATE TABLE `requests` (
  `id` int(11) NOT NULL,
  `equipment_id` int(11) NOT NULL,
  `subject` varchar(150) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `priority` enum('High','Medium','Low') DEFAULT 'Medium',
  `type` enum('Breakdown','Routine') DEFAULT 'Breakdown',
  `stage` enum('New','In Progress','Waiting for Parts','Repaired','Scrap') DEFAULT 'New',
  `assigned_to` int(11) DEFAULT NULL,
  `duration` float DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `requests`
--

INSERT INTO `requests` (`id`, `equipment_id`, `subject`, `description`, `priority`, `type`, `stage`, `assigned_to`, `duration`, `created_at`) VALUES
(1, 2, 'Belt slipping', NULL, 'High', 'Breakdown', 'In Progress', 3, 4.5, '2025-12-27 09:08:39'),
(2, 10, 'Calibration Error', NULL, 'Medium', 'Routine', 'Waiting for Parts', 3, 2, '2025-12-27 09:08:39'),
(3, 1, 'Weekly Checkup', NULL, 'Low', 'Routine', 'New', NULL, 1, '2025-12-27 09:08:39'),
(4, 6, 'Coolant Leak', NULL, 'High', 'Breakdown', 'New', NULL, 0, '2025-12-27 09:08:39'),
(5, 4, 'Structural Failure', NULL, 'High', 'Breakdown', 'Scrap', 2, 0, '2025-12-27 09:08:39');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('Admin','Manager','Technician') NOT NULL,
  `team` enum('IT','Mechanical','Electrical','General') DEFAULT 'General',
  `avatar` varchar(255) DEFAULT 'default_avatar.png'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`, `team`, `avatar`) VALUES
(1, 'admin', '$2y$10$zkKPw7lPYMDhkH6QLW5KXuITuUDderP/9YIBP.LX6XdfzAKKuBP9i', 'Admin', 'General', 'default_avatar.png'),
(2, 'manager_mech', '$2y$10$zkKPw7lPYMDhkH6QLW5KXuITuUDderP/9YIBP.LX6XdfzAKKuBP9i', 'Manager', 'Mechanical', 'default_avatar.png'),
(3, 'tech_alex', '$2y$10$zkKPw7lPYMDhkH6QLW5KXuITuUDderP/9YIBP.LX6XdfzAKKuBP9i', 'Technician', 'Mechanical', 'default_avatar.png'),
(4, 'tech_sarah', '$2y$10$zkKPw7lPYMDhkH6QLW5KXuITuUDderP/9YIBP.LX6XdfzAKKuBP9i', 'Technician', 'Electrical', 'default_avatar.png'),
(5, 'tech_mike', '$2y$10$zkKPw7lPYMDhkH6QLW5KXuITuUDderP/9YIBP.LX6XdfzAKKuBP9i', 'Technician', 'IT', 'default_avatar.png');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `equipment`
--
ALTER TABLE `equipment`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `requests`
--
ALTER TABLE `requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `equipment_id` (`equipment_id`),
  ADD KEY `assigned_to` (`assigned_to`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `equipment`
--
ALTER TABLE `equipment`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `requests`
--
ALTER TABLE `requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `requests`
--
ALTER TABLE `requests`
  ADD CONSTRAINT `requests_ibfk_1` FOREIGN KEY (`equipment_id`) REFERENCES `equipment` (`id`),
  ADD CONSTRAINT `requests_ibfk_2` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
