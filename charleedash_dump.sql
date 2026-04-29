-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 29, 2026 at 04:44 PM
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
-- Database: `charleedash_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `phone` varchar(15) NOT NULL,
  `alias` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `trust_tier` varchar(50) DEFAULT 'Bronze',
  `trust_score` int(11) DEFAULT 0,
  `otp_code` varchar(6) DEFAULT NULL,
  `is_verified` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `otp_created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `phone`, `alias`, `password`, `trust_tier`, `trust_score`, `otp_code`, `is_verified`, `created_at`, `otp_created_at`) VALUES
(1, 'Rachel Murambiwa', '', 'Sassy1583', '$2y$10$U68J8OPyzOqP3e0n/W82LeprnrGlK3bORryqrLc2Z11xIOlXnJn/m', 'Bronze', 0, '927606', 0, '2026-04-28 14:49:29', '2026-04-28 15:50:14'),
(19, 'Rachel Murambiwa', '0596651013', 'Sassy2003', '$2y$10$SSOkJ1YARJ7AGbUfw5SWvuqeHbr58QoFVY2qPgJmYkKEyak0M5Aby', 'Bronze', 0, '333756', 0, '2026-04-28 17:49:35', '2026-04-28 17:49:35'),
(20, 'Rachel Murambiwa', '0596651014', 'Neon440', '$2y$10$mueGwNIlG3y69rJjBm.T1O5/c4hL8aQ9JVDwBpfUdi9CGbHrjhQ4K', 'Bronze', 0, NULL, 1, '2026-04-28 17:52:38', '2026-04-28 17:52:38'),
(21, 'Rachel Murambiwa', '0596651012', 'Star2222', '$2y$10$0N9aFAoYD1ppFYrGEzMW1Oe7H8ZcGbb/Xoy1gd3eW3TPddIgN1P/O', 'Bronze', 0, NULL, 1, '2026-04-28 18:07:22', '2026-04-28 18:07:22');

-- --------------------------------------------------------

--
-- Table structure for table `vaults`
--

CREATE TABLE `vaults` (
  `id` int(11) NOT NULL,
  `lender_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `interest_rate` decimal(5,2) NOT NULL,
  `duration_days` int(11) NOT NULL,
  `status` enum('available','active','paid') DEFAULT 'available',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vaults`
--

INSERT INTO `vaults` (`id`, `lender_id`, `amount`, `interest_rate`, `duration_days`, `status`, `created_at`) VALUES
(1, 1, 500.00, 5.00, 14, 'available', '2026-04-28 20:28:47'),
(2, 20, 200.00, 0.00, 7, 'available', '2026-04-28 20:28:47'),
(3, 21, 1000.00, 8.00, 30, 'available', '2026-04-28 20:28:47');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `alias` (`alias`),
  ADD UNIQUE KEY `phone` (`phone`);

--
-- Indexes for table `vaults`
--
ALTER TABLE `vaults`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lender_id` (`lender_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `vaults`
--
ALTER TABLE `vaults`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `vaults`
--
ALTER TABLE `vaults`
  ADD CONSTRAINT `vaults_ibfk_1` FOREIGN KEY (`lender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
