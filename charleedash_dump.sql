-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: db
-- Generation Time: May 01, 2026 at 09:34 PM
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
  `status` enum('active','overdue','pending_confirmation','paid') COLLATE utf8mb4_general_ci DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `active_contracts`
--

INSERT INTO `active_contracts` (`id`, `vault_id`, `borrower_id`, `due_date`, `status`) VALUES
(2, 4, 9, '2026-05-13', 'active'),
(5, 4, 5, '2026-05-13', 'active'),
(6, 3, 5, '2026-05-31', 'active'),
(7, 1, 11, '2026-05-08', 'active'),
(8, 7, 12, '2026-05-31', 'active');

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
(2, 4, 9, 150.00, 157.50, 'approved', '2026-05-01 16:27:56'),
(3, 4, 5, 50.00, 52.50, 'approved', '2026-05-01 16:35:50'),
(4, 1, 10, 1000.00, 1000.70, 'pending', '2026-05-01 17:32:50'),
(5, 3, 5, 300.00, 315.00, 'approved', '2026-05-01 18:02:50'),
(6, 1, 11, 200.00, 200.14, 'approved', '2026-05-01 18:53:47'),
(7, 7, 12, 500.00, 525.00, 'approved', '2026-05-01 19:13:20');

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `type` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`id`, `user_id`, `type`, `amount`, `created_at`) VALUES
(1, 9, 'loan_disbursement', 150.00, '2026-05-01 17:21:15'),
(2, 5, 'loan_disbursement', 50.00, '2026-05-01 17:52:57'),
(3, 5, 'loan_disbursement', 300.00, '2026-05-01 18:03:06'),
(4, 11, 'loan_disbursement', 200.00, '2026-05-01 18:54:28'),
(5, 12, 'loan_disbursement', 500.00, '2026-05-01 19:13:31');

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
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `session_token` varchar(128) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `token_expiry` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `phone`, `email`, `password`, `alias`, `otp_code`, `is_verified`, `is_admin`, `otp_created_at`, `created_at`, `session_token`, `token_expiry`) VALUES
(1, 'Shammah Dzwairo', '0597030814', NULL, '$2y$10$yJPzh6r1eUD4GmhD.a5l1udXF4W27ThsB/3/2i32gb/y27LougIo6', 'Vroom911', NULL, 1, 0, '2026-05-01 01:51:55', '2026-05-01 01:51:55', NULL, NULL),
(2, 'Nasieku Leiyagu', '0559421677', NULL, '$2y$10$Q7k2KCPU7RDOy.WhLOsRm.pCuKAjILmL0GyzjQEnfsFSDbqqFwApa', 'Ghost367', '588494', 0, 0, '2026-05-01 09:50:21', '2026-05-01 09:03:57', NULL, NULL),
(3, 'Christine Nasieku', '0759421677', NULL, '$2y$10$Xfy9SV4VeLrrB7N9AUJMGuDgBMxRvVefHXDFO214.JLbYi5sLpena', 'Vault902', '370406', 0, 0, '2026-05-01 09:28:18', '2026-05-01 09:28:18', NULL, NULL),
(4, 'Agoss Debb', '0503840174', NULL, '$2y$10$vWidhHRwDgEYNFWXnw4xCOUHO5ts.jSxP0hkICsA1z3ksbB/5F71C', 'debb', NULL, 1, 1, '2026-05-01 11:35:32', '2026-05-01 11:35:32', NULL, NULL),
(5, 'Aisha Chihuri', '0536957209', NULL, '$2y$10$WZcIR61yhuwLyN0IUxROfOygA2TF.RwJ1H3e7nUp0raBJkPxOlSi2', 'Mwanasikana', NULL, 1, 1, '2026-05-01 11:41:49', '2026-05-01 11:41:49', '6a34cb038aac1244afa7bbc39046d174df9aa2c68f13dc438f64f55ac3f0ab98', '2026-05-01 20:55:10'),
(6, 'Chacha Wedu', '0596651015', NULL, '$2y$10$foU/xa7UGWqpAJzOCihJGOPXwAV1HwxA5a2jx9xX/IUwtk.9sywye', 'Micro140', NULL, 1, 0, '2026-05-01 11:58:14', '2026-05-01 11:58:14', NULL, NULL),
(7, 'Elizabeth Leiyagu', '0538680287', NULL, '$2y$10$527KZK/Q4Lfd95iKGt.lue0NsmASlvYBrxkYWLefam/klWpFwaN7K', 'Neon568', '948066', 0, 0, '2026-05-01 12:19:07', '2026-05-01 12:19:07', NULL, NULL),
(8, 'Shammah Dzwairo', '0784436503', NULL, '$2y$10$7JDOJHg.dtRjiLdnwEr5quX4jxBfhJbjFFjmc4zNETugsgbFp2kFG', 'Star654', NULL, 1, 1, '2026-05-01 12:26:40', '2026-05-01 12:26:40', '30f4dbd84c81eef6b94de6b29a4472a7260abaf3c1bac268a07cd5a378eee775', '2026-05-01 23:32:48'),
(9, 'Ashesi Student', '0596651016', NULL, '$2y$10$pr5687CcQ/v6pBx94qctr.d80MpY/j66pIrM.RdbmNkwLKse0ROT6', 'Neon817', NULL, 1, 1, '2026-05-01 15:07:28', '2026-05-01 15:07:28', '92e2f0e80b03871f200cb08773b6bf9f20565fb18f20c940c9581b835d366cdc', '2026-05-01 23:18:09'),
(10, 'Student One', '0596651011', NULL, '$2y$10$qcBUJ7POyMQw7Vh0DiQau.X9yEFrM.b8vOKzVknz6X0Fka.NJKegi', 'Cipher772', NULL, 1, 0, '2026-05-01 17:31:03', '2026-05-01 17:31:03', NULL, NULL),
(11, 'Student Two', '0596651017', NULL, '$2y$10$X35sXV5/1pnDjAqi8CARje7HT.ZYp1Rm10Ngvn7n4LGqNlm512KJW', 'Star134', NULL, 1, 0, '2026-05-01 18:52:15', '2026-05-01 18:52:15', 'e01dbf82f04af2f540c2f78396a1c7c64172099a17ffd2a9822a59574d92b7dc', '2026-05-01 20:59:50'),
(12, 'Student Three', '0596651019', NULL, '$2y$10$dxGb6HVBBHvVRokcMTdy/u8oYUkp6Lng3VjKJApKL/p.WMLTVbUVO', 'Neon941', NULL, 1, 0, '2026-05-01 19:12:30', '2026-05-01 19:12:30', 'ee9c1de8c980375c4f952c0b32e5fb08e664cbafb84bc0ab220a504337be99c2', '2026-05-01 21:13:07'),
(13, 'Student Four', '263719096252', NULL, '$2y$10$K2i.Uyvkt5O74c9YHnakneVgUVlIezNXNxaC5FAha/1lmp3Y/S1ZG', 'Echo906', NULL, 1, 0, '2026-05-01 20:52:42', '2026-05-01 20:52:42', 'adf687f32caf83d1ecbc6efdcc2a67bf8b8c7f2cc77eeca7dd0a5e1e41397e2b', '2026-05-01 23:04:14');

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
(1, 1, 4000.00, 2800.00, 0.07, 7, 'available', '2026-05-01 01:53:53'),
(2, 1, 300.00, 300.00, 6.00, 14, 'available', '2026-05-01 01:56:52'),
(3, 1, 300.00, 0.00, 5.00, 30, 'available', '2026-05-01 11:00:06'),
(4, 1, 200.00, 0.00, 5.00, 12, 'available', '2026-05-01 15:00:46'),
(6, 11, 600.00, 600.00, 5.00, 14, 'available', '2026-05-01 18:53:14'),
(7, 9, 500.00, 0.00, 5.00, 30, 'available', '2026-05-01 19:11:26'),
(8, 13, 1000.00, 1000.00, 10.00, 30, 'available', '2026-05-01 21:04:55');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `active_contracts`
--
ALTER TABLE `active_contracts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `borrower_id` (`borrower_id`),
  ADD KEY `vault_id` (`vault_id`);

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
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `loan_requests`
--
ALTER TABLE `loan_requests`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `vaults`
--
ALTER TABLE `vaults`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `active_contracts`
--
ALTER TABLE `active_contracts`
  ADD CONSTRAINT `active_contracts_ibfk_2` FOREIGN KEY (`borrower_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `active_contracts_ibfk_3` FOREIGN KEY (`vault_id`) REFERENCES `vaults` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

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
