-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 28, 2024 at 10:21 AM
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
-- Database: `abp_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `gold_silver_rates`
--

CREATE TABLE `gold_silver_rates` (
  `id` int(11) NOT NULL,
  `date_rate` date NOT NULL,
  `gold_999_am_price` varchar(10) NOT NULL,
  `gold_999_pm_price` varchar(10) NOT NULL,
  `gold_995_am_price` varchar(10) NOT NULL,
  `gold_995_pm_price` varchar(10) NOT NULL,
  `gold_916_am_price` varchar(10) NOT NULL,
  `gold_916_pm_price` varchar(10) NOT NULL,
  `gold_750_am_price` varchar(10) NOT NULL,
  `gold_750_pm_price` varchar(10) NOT NULL,
  `gold_585_am_price` varchar(10) NOT NULL,
  `gold_585_pm_price` varchar(10) NOT NULL,
  `silver_999_am_price` varchar(10) NOT NULL,
  `silver_999_pm_price` varchar(10) NOT NULL,
  `created_at` datetime NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `gold_silver_rates`
--
ALTER TABLE `gold_silver_rates`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `gold_silver_rates`
--
ALTER TABLE `gold_silver_rates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
