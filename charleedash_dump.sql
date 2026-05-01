-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: db
-- Generation Time: May 01, 2026 at 04:55 PM
-- Server version: 8.0.45
-- PHP Version: 8.3.26

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
-- Table structure for table `active_contracts`
--

CREATE TABLE `active_contracts` (
  `id` int NOT NULL,
  `vault_id` int NOT NULL,
  `borrower_id` int NOT NULL,
  `due_date` date NOT NULL,
  `status` enum('active','completed') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `loan_requests`
--

CREATE TABLE `loan_requests` (
  `id` int NOT NULL,
  `vault_id` int NOT NULL,
  `borrower_id` int NOT NULL,
  `requested_amount` decimal(10,2) NOT NULL,
  `amount_to_repay` decimal(10,2) NOT NULL,
  `status` enum('pending','approved','rejected') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `loan_requests`
--

INSERT INTO `loan_requests` (`id`, `vault_id`, `borrower_id`, `requested_amount`, `amount_to_repay`, `status`, `created_at`) VALUES
(2, 4, 9, 150.00, 157.50, 'pending', '2026-05-01 16:27:56'),
(3, 4, 5, 50.00, 52.50, 'pending', '2026-05-01 16:35:50');

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `type` enum('deposit','withdrawal','loan_disbursed','repayment') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `full_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `phone` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `alias` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `otp_code` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `is_verified` tinyint(1) NOT NULL DEFAULT '0',
  `is_admin` tinyint(1) NOT NULL DEFAULT '0',
  `otp_created_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `phone`, `email`, `password`, `alias`, `otp_code`, `is_verified`, `is_admin`, `otp_created_at`, `created_at`) VALUES
(1, 'Shammah Dzwairo', '0597030814', NULL, '$2y$10$yJPzh6r1eUD4GmhD.a5l1udXF4W27ThsB/3/2i32gb/y27LougIo6', 'Vroom911', NULL, 1, 0, '2026-05-01 01:51:55', '2026-05-01 01:51:55'),
(2, 'Nasieku Leiyagu', '0559421677', NULL, '$2y$10$Q7k2KCPU7RDOy.WhLOsRm.pCuKAjILmL0GyzjQEnfsFSDbqqFwApa', 'Ghost367', '588494', 0, 0, '2026-05-01 09:50:21', '2026-05-01 09:03:57'),
(3, 'Christine Nasieku', '0759421677', NULL, '$2y$10$Xfy9SV4VeLrrB7N9AUJMGuDgBMxRvVefHXDFO214.JLbYi5sLpena', 'Vault902', '370406', 0, 0, '2026-05-01 09:28:18', '2026-05-01 09:28:18'),
(4, 'Agoss Debb', '0503840174', NULL, '$2y$10$vWidhHRwDgEYNFWXnw4xCOUHO5ts.jSxP0hkICsA1z3ksbB/5F71C', 'debb', NULL, 1, 1, '2026-05-01 11:35:32', '2026-05-01 11:35:32'),
(5, 'Aisha Chihuri', '0536957209', NULL, '$2y$10$WZcIR61yhuwLyN0IUxROfOygA2TF.RwJ1H3e7nUp0raBJkPxOlSi2', 'Mwanasikana', NULL, 1, 1, '2026-05-01 11:41:49', '2026-05-01 11:41:49'),
(6, 'Chacha Wedu', '0596651015', NULL, '$2y$10$foU/xa7UGWqpAJzOCihJGOPXwAV1HwxA5a2jx9xX/IUwtk.9sywye', 'Micro140', NULL, 1, 0, '2026-05-01 11:58:14', '2026-05-01 11:58:14'),
(7, 'Elizabeth Leiyagu', '0538680287', NULL, '$2y$10$527KZK/Q4Lfd95iKGt.lue0NsmASlvYBrxkYWLefam/klWpFwaN7K', 'Neon568', '948066', 0, 0, '2026-05-01 12:19:07', '2026-05-01 12:19:07'),
(8, 'Shammah Dzwairo', '0784436503', NULL, '$2y$10$7JDOJHg.dtRjiLdnwEr5quX4jxBfhJbjFFjmc4zNETugsgbFp2kFG', 'Star654', NULL, 1, 0, '2026-05-01 12:26:40', '2026-05-01 12:26:40'),
(9, 'Ashesi Student', '0596651016', NULL, '$2y$10$pr5687CcQ/v6pBx94qctr.d80MpY/j66pIrM.RdbmNkwLKse0ROT6', 'Neon817', NULL, 1, 1, '2026-05-01 15:07:28', '2026-05-01 15:07:28');

-- --------------------------------------------------------

--
-- Table structure for table `vaults`
--

CREATE TABLE `vaults` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `available_amount` decimal(10,2) NOT NULL,
  `interest` decimal(5,2) NOT NULL,
  `duration` int NOT NULL,
  `status` enum('available','active','paid') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'available',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vaults`
--

INSERT INTO `vaults` (`id`, `user_id`, `amount`, `available_amount`, `interest`, `duration`, `status`, `created_at`) VALUES
(1, 1, 4000.00, 4000.00, 0.07, 7, 'available', '2026-05-01 01:53:53'),
(2, 1, 300.00, 300.00, 6.00, 14, 'available', '2026-05-01 01:56:52'),
(3, 1, 300.00, 300.00, 5.00, 30, 'available', '2026-05-01 11:00:06'),
(4, 1, 200.00, 0.00, 5.00, 12, 'available', '2026-05-01 15:00:46');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `active_contracts`
--
ALTER TABLE `active_contracts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `vault_id` (`vault_id`),
  ADD KEY `borrower_id` (`borrower_id`);

--
-- Indexes for table `loan_requests`
--
ALTER TABLE `loan_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `vault_id` (`vault_id`),
  ADD KEY `borrower_id` (`borrower_id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `phone` (`phone`);

--
-- Indexes for table `vaults`
--
ALTER TABLE `vaults`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `active_contracts`
--
ALTER TABLE `active_contracts`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `loan_requests`
--
ALTER TABLE `loan_requests`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `vaults`
--
ALTER TABLE `vaults`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `active_contracts`
--
ALTER TABLE `active_contracts`
  ADD CONSTRAINT `active_contracts_ibfk_1` FOREIGN KEY (`vault_id`) REFERENCES `vaults` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `active_contracts_ibfk_2` FOREIGN KEY (`borrower_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `loan_requests`
--
ALTER TABLE `loan_requests`
  ADD CONSTRAINT `loan_requests_ibfk_1` FOREIGN KEY (`vault_id`) REFERENCES `vaults` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `loan_requests_ibfk_2` FOREIGN KEY (`borrower_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `vaults`
--
ALTER TABLE `vaults`
  ADD CONSTRAINT `vaults_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
