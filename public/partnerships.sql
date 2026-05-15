-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 15, 2026 at 09:40 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.4.21

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `kreston_erp`
--

-- --------------------------------------------------------

--
-- Table structure for table `partnerships`
--

CREATE TABLE `partnerships` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nik` varchar(255) NOT NULL,
  `code` varchar(255) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `gender` enum('male','female') NOT NULL,
  `division` varchar(255) NOT NULL,
  `date_of_entry` date DEFAULT NULL,
  `release_date` date DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `partnerships`
--

INSERT INTO `partnerships` (`id`, `nik`, `code`, `name`, `email`, `phone`, `gender`, `division`, `date_of_entry`, `release_date`, `status`, `created_at`, `updated_at`) VALUES
(1, '50000', 'AH', 'A. Hendra Winata', 'hendra.winata@kreston.co.id', NULL, 'male', 'Audit', NULL, NULL, 'active', NULL, NULL),
(2, '80001', 'RS', 'Robby Sumargo', 'robby.sumargo@kreston.co.id', NULL, 'male', 'Audit', NULL, NULL, 'active', NULL, NULL),
(3, '50001', 'RM', 'Razmal Muin', 'razmal.muin@kreston.co.id', NULL, 'male', 'Tax', NULL, NULL, 'active', NULL, NULL),
(4, '50008', 'LW', 'Lianty Widjaja', 'lianty.widjaja@kreston.co.id', NULL, 'female', 'Audit', NULL, NULL, 'active', NULL, NULL),
(5, '50016', 'EAW', 'Erwin A. Winata', 'erwin.winata@kreston.co.id', NULL, 'male', 'Audit', NULL, NULL, 'active', NULL, NULL),
(6, '50038', 'WA', 'Welly Adrianto', 'welly.adrianto@kreston.co.id', NULL, 'male', 'Audit', NULL, NULL, 'active', NULL, NULL),
(7, '50285', 'LJ', 'Leknor Joni', 'leknor.joni@kreston.co.id', NULL, 'male', 'Audit', NULL, NULL, 'active', NULL, NULL),
(8, '50335', 'ZUL', 'Zulbadri', 'zulbadri@kreston.co.id', NULL, 'male', 'Audit', NULL, NULL, 'active', NULL, NULL),
(9, '50427', 'RAD', 'Ronady Surya Sembiring', 'ronady.sembiring@kreston.co.id', NULL, 'male', 'Audit', NULL, NULL, 'active', NULL, NULL),
(10, '50428', 'RAD', 'Martinus Aryo Prabowo', 'aryo.prabowo@kreston.co.id', NULL, 'male', 'Audit', NULL, NULL, 'active', NULL, NULL),
(11, '50462', NULL, 'Florensia Yunita Siaw', 'florensia.yunita@kreston.co.id', NULL, 'female', 'Tax', NULL, NULL, 'active', NULL, NULL),
(12, '80000', NULL, 'Anny Hutagaol', 'anny.hutagaol@kreston.co.id', NULL, 'female', 'Audit', NULL, NULL, 'active', NULL, NULL),
(13, '50401', 'RAD', 'Triyoga Deni Intarto', 'deni.intarto@kreston.co.id', NULL, 'male', 'Audit', NULL, NULL, 'active', NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `partnerships`
--
ALTER TABLE `partnerships`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `partnerships_nik_unique` (`nik`),
  ADD UNIQUE KEY `partnerships_email_unique` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `partnerships`
--
ALTER TABLE `partnerships`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
