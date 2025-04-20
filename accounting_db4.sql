-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 21, 2025 at 12:36 AM
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
-- Database: `accounting_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `cash_flows`
--

CREATE TABLE `cash_flows` (
  `id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `image` varchar(255) DEFAULT '',
  `sort_order` int(11) DEFAULT 0,
  `active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `description`, `parent_id`, `status`, `created_at`, `updated_at`, `image`, `sort_order`, `active`) VALUES
(1, 'موس', NULL, NULL, 'active', '2025-03-23 14:04:17', '2025-03-23 14:04:17', '', 0, 1),
(2, 'الکترونیک', NULL, NULL, 'active', '2025-03-23 15:02:54', '2025-03-23 15:02:54', '', 0, 1),
(3, 'لپ‌تاپ', NULL, 1, 'active', '2025-03-23 15:02:54', '2025-03-23 15:02:54', '', 0, 1),
(4, 'تلفن همراه', NULL, 1, 'active', '2025-03-23 15:02:54', '2025-03-23 15:02:54', '', 0, 1),
(5, 'دوربین', NULL, 1, 'active', '2025-03-23 15:02:54', '2025-03-23 15:02:54', '', 0, 1),
(6, 'لوازم جانبی', NULL, 1, 'active', '2025-03-23 15:02:54', '2025-03-23 15:02:54', '', 0, 1),
(7, 'پوشاک', NULL, NULL, 'active', '2025-03-23 15:02:54', '2025-03-23 15:02:54', '', 0, 1),
(8, 'مردانه', NULL, 6, 'active', '2025-03-23 15:02:54', '2025-03-23 15:02:54', '', 0, 1),
(9, 'زنانه', NULL, 6, 'active', '2025-03-23 15:02:54', '2025-03-23 15:02:54', '', 0, 1),
(10, 'بچگانه', NULL, 6, 'active', '2025-03-23 15:02:54', '2025-03-23 15:02:54', '', 0, 1),
(11, 'ورزشی', NULL, 6, 'active', '2025-03-23 15:02:54', '2025-03-23 15:02:54', '', 0, 1),
(12, 'کتاب', NULL, NULL, 'active', '2025-03-23 15:02:54', '2025-03-23 15:02:54', '', 0, 1),
(13, 'ادبیات', NULL, 11, 'active', '2025-03-23 15:02:54', '2025-03-23 15:02:54', '', 0, 1),
(14, 'علمی', NULL, 11, 'active', '2025-03-23 15:02:54', '2025-03-23 15:02:54', '', 0, 1),
(15, 'هنری', NULL, 11, 'active', '2025-03-23 15:02:54', '2025-03-23 15:02:54', '', 0, 1),
(16, 'تاریخی', NULL, 11, 'active', '2025-03-23 15:02:54', '2025-03-23 15:02:54', '', 0, 1),
(17, 'آشپزی', NULL, 11, 'active', '2025-03-23 15:02:54', '2025-03-23 15:02:54', '', 0, 1),
(18, 'خودرو', NULL, NULL, 'active', '2025-03-23 15:02:54', '2025-03-23 15:02:54', '', 0, 1),
(19, 'قطعات', NULL, 16, 'active', '2025-03-23 15:02:54', '2025-03-23 15:02:54', '', 0, 1),
(20, 'لوازم یدکی', NULL, 16, 'active', '2025-03-23 15:02:54', '2025-03-23 15:02:54', '', 0, 1),
(21, 'لوازم جانبی خودرو', NULL, 16, 'active', '2025-03-23 15:02:54', '2025-03-23 15:02:54', '', 0, 1),
(22, 'زیبایی و سلامت', NULL, NULL, 'active', '2025-03-23 15:02:54', '2025-03-23 15:02:54', '', 0, 1),
(23, 'لوازم آرایشی', NULL, 21, 'active', '2025-03-23 15:02:54', '2025-03-23 15:02:54', '', 0, 1),
(24, 'لوازم بهداشتی', NULL, 21, 'active', '2025-03-23 15:02:54', '2025-03-23 15:02:54', '', 0, 1),
(25, 'عطر و ادکلن', NULL, 21, 'active', '2025-03-23 15:02:54', '2025-03-23 15:02:54', '', 0, 1),
(26, 'لوازم شخصی برقی', NULL, 21, 'active', '2025-03-23 15:02:54', '2025-03-23 15:02:54', '', 0, 1),
(27, 'خانه و آشپزخانه', NULL, NULL, 'active', '2025-03-23 15:02:54', '2025-03-23 15:02:54', '', 0, 1),
(28, 'لوازم آشپزخانه', NULL, 26, 'active', '2025-03-23 15:02:54', '2025-03-23 15:02:54', '', 0, 1),
(29, 'لوازم برقی', NULL, 26, 'active', '2025-03-23 15:02:54', '2025-03-23 15:02:54', '', 0, 1),
(30, 'مبلمان', NULL, 26, 'active', '2025-03-23 15:02:54', '2025-03-23 15:02:54', '', 0, 1),
(31, 'فرش و موکت', NULL, 26, 'active', '2025-03-23 15:02:54', '2025-03-23 15:02:54', '', 0, 1),
(32, 'ابزار1', NULL, NULL, 'active', '2025-03-23 15:02:54', '2025-03-23 16:45:48', '', 0, 1),
(33, 'ابزار برقی', NULL, 31, 'active', '2025-03-23 15:02:54', '2025-03-23 15:02:54', '', 0, 1),
(34, 'ابزار دستی', NULL, 31, 'active', '2025-03-23 15:02:54', '2025-03-23 15:02:54', '', 0, 1),
(35, 'لوازم باغبانی', NULL, 31, 'active', '2025-03-23 15:02:54', '2025-03-23 15:02:54', '', 0, 1);

-- --------------------------------------------------------

--
-- Table structure for table `cheques`
--

CREATE TABLE `cheques` (
  `id` int(11) NOT NULL,
  `cheque_number` varchar(255) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `due_date` date NOT NULL,
  `status` enum('received','pending','cleared') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `id` int(11) NOT NULL,
  `customer_code` varchar(255) NOT NULL,
  `first_name` varchar(255) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `mobile` varchar(50) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `name` varchar(255) NOT NULL,
  `code` varchar(50) DEFAULT NULL,
  `type` enum('real','legal') DEFAULT 'real',
  `status` enum('active','inactive') DEFAULT 'active',
  `national_code` varchar(10) DEFAULT NULL,
  `economic_code` varchar(12) DEFAULT NULL,
  `company` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `credit_limit` decimal(20,2) DEFAULT 0.00,
  `credit_balance` decimal(20,2) DEFAULT 0.00,
  `created_by` int(11) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`id`, `customer_code`, `first_name`, `last_name`, `mobile`, `phone`, `address`, `email`, `description`, `image`, `created_at`, `updated_at`, `name`, `code`, `type`, `status`, `national_code`, `economic_code`, `company`, `notes`, `credit_limit`, `credit_balance`, `created_by`, `deleted_at`, `created_date`) VALUES
(1, '', 'مسعود', 'عباس ابدی', '091525105041', NULL, '', '', '', 'uploads/tesco.jpg', '2025-03-23 15:22:21', '2025-03-23 15:35:43', '', NULL, 'real', 'active', NULL, NULL, NULL, NULL, 0.00, 0.00, NULL, NULL, NULL),
(9, '', '', 'فروش سریع', '09000000000', NULL, NULL, NULL, NULL, NULL, '2025-03-26 12:19:45', '2025-03-27 10:32:27', 'مشتری', '202503264634', 'real', 'active', NULL, NULL, NULL, NULL, 0.00, 0.00, NULL, '2025-03-27 10:32:27', NULL),
(10, '', '', 'فروش سریع', '09000000000', NULL, NULL, NULL, NULL, NULL, '2025-03-26 12:23:19', '2025-03-27 10:32:09', 'مشتری', '۱۴۰۴۰۱۰۶0083', 'real', 'active', NULL, NULL, NULL, NULL, 0.00, 0.00, NULL, '2025-03-27 10:32:09', NULL),
(12, '', '', 'فروش سریع', '09000000000', NULL, NULL, NULL, NULL, NULL, '2025-03-26 12:23:22', '2025-03-26 12:23:22', 'مشتری', '۱۴۰۴۰۱۰۶1436', 'real', 'active', NULL, NULL, NULL, NULL, 0.00, 0.00, NULL, NULL, NULL),
(18, '', '', 'فروش سریع', '09000000000', NULL, NULL, NULL, NULL, NULL, '2025-03-26 12:23:36', '2025-03-27 10:32:12', 'مشتری', '۱۴۰۴۰۱۰۶4889', 'real', 'active', NULL, NULL, NULL, NULL, 0.00, 0.00, NULL, '2025-03-27 10:32:12', NULL),
(19, '', '', 'فروش سریع', '09000000000', NULL, NULL, NULL, NULL, NULL, '2025-03-26 12:25:18', '2025-03-27 10:32:20', 'مشتری', '۱۴۰۴۰۱۰۶8656', 'real', 'active', NULL, NULL, NULL, NULL, 0.00, 0.00, NULL, '2025-03-27 10:32:20', NULL),
(20, '', '', 'فروش سریع', '09000000000', NULL, NULL, NULL, NULL, NULL, '2025-03-26 12:25:24', '2025-03-27 10:32:18', 'مشتری', '۱۴۰۴۰۱۰۶2449', 'real', 'active', NULL, NULL, NULL, NULL, 0.00, 0.00, NULL, '2025-03-27 10:32:18', NULL),
(21, '', '', 'فروش سریع', '09000000000', NULL, NULL, NULL, NULL, NULL, '2025-03-26 12:25:25', '2025-03-27 10:32:17', 'مشتری', '۱۴۰۴۰۱۰۶1209', 'real', 'active', NULL, NULL, NULL, NULL, 0.00, 0.00, NULL, '2025-03-27 10:32:17', NULL),
(22, '', '', 'فروش سریع', '09000000000', NULL, NULL, NULL, NULL, NULL, '2025-03-26 12:25:29', '2025-03-27 10:32:16', 'مشتری', '۱۴۰۴۰۱۰۶4967', 'real', 'active', NULL, NULL, NULL, NULL, 0.00, 0.00, NULL, '2025-03-27 10:32:16', NULL),
(23, '', '', 'فروش سریع', '09000000000', NULL, NULL, NULL, NULL, NULL, '2025-03-26 12:27:48', '2025-03-27 10:32:13', 'مشتری', '۱۴۰۴۰۱۰۶6336', 'real', 'active', NULL, NULL, NULL, NULL, 0.00, 0.00, NULL, '2025-03-27 10:32:13', NULL),
(24, '', '', 'فروش سریع', '09000000000', NULL, NULL, NULL, NULL, NULL, '2025-03-26 12:27:50', '2025-03-27 10:32:25', 'مشتری', '۱۴۰۴۰۱۰۶6821', 'real', 'active', NULL, NULL, NULL, NULL, 0.00, 0.00, NULL, '2025-03-27 10:32:25', NULL),
(25, '', '', 'فروش سریع', '09000000000', NULL, NULL, NULL, NULL, NULL, '2025-03-27 08:31:10', '2025-03-27 10:32:06', 'مشتری', '۱۴۰۴۰۱۰۷1292', 'real', 'active', NULL, NULL, NULL, NULL, 0.00, 0.00, NULL, '2025-03-27 10:32:06', NULL),
(27, '', '', 'فروش سریع', '09000000000', NULL, NULL, NULL, NULL, NULL, '2025-03-26 14:06:17', '2025-03-26 14:06:17', 'مشتری', '202503268363', 'real', 'active', NULL, NULL, NULL, NULL, 0.00, 0.00, NULL, NULL, '2025-03-26'),
(28, '', '', 'فروش سریع', '09000000000', NULL, NULL, NULL, NULL, NULL, '2025-03-27 14:08:03', '2025-03-27 10:32:03', 'مشتری', '202503278053', 'real', 'active', NULL, NULL, NULL, NULL, 0.00, 0.00, NULL, '2025-03-27 10:32:03', '2025-03-27'),
(29, '', '', 'فروش سریع', '09000000000', NULL, NULL, NULL, NULL, NULL, '2025-03-27 15:00:04', '2025-03-27 10:32:01', 'مشتری', '202503279808', 'real', 'active', NULL, NULL, NULL, NULL, 0.00, 0.00, NULL, '2025-03-27 10:32:01', '2025-03-27'),
(30, '', '', 'فروش سریع', '09000000000', NULL, NULL, NULL, NULL, NULL, '2025-03-27 15:00:58', '2025-03-27 10:31:58', 'مشتری', '202503278436', 'real', 'active', NULL, NULL, NULL, NULL, 0.00, 0.00, NULL, '2025-03-27 10:31:58', '2025-03-27'),
(34, '', '', 'فروش سریع', '09000000000', NULL, NULL, NULL, NULL, NULL, '2025-03-27 21:32:26', '2025-03-27 10:31:55', 'مشتری', '202503280937', 'real', 'active', NULL, NULL, NULL, NULL, 0.00, 0.00, NULL, '2025-03-27 10:31:55', '2025-03-28'),
(35, '', 'محمد', 'اکرادی', '09159744548', '', '', '', NULL, NULL, '2025-03-27 10:25:45', '2025-03-27 10:25:45', 'محمد اکرادی', '۱۴۰۴00000001', 'real', 'active', '', '', '', '', 0.00, 0.00, 2, NULL, '2025-03-27'),
(38, '', 'مهدی', 'محمدی', '09159744550', '', '', '', NULL, 'uploads/customers/customer_67e5340d56644.jpg', '2025-03-27 10:27:58', '2025-03-27 11:18:41', 'مهدی محمدی', '۱۴۰۴00000002', 'real', 'active', '', '', '', '', 150000.00, 1400.00, 2, NULL, '2025-03-27'),
(39, '', 'رضا', 'محمدی', '09153756302', '', '', '', NULL, NULL, '2025-03-27 10:28:27', '2025-03-27 10:28:27', 'رضا محمدی', '۱۴۰۴00000003', 'real', 'active', '', '', '', '', 0.00, 0.00, 2, NULL, '2025-03-27'),
(40, '', 'مصطفی', 'محمدرضایی', '09380002019', NULL, '', '', NULL, NULL, '2025-03-27 11:19:39', '2025-03-27 11:19:39', 'مصطفی محمدرضایی', '۱۴۰۴00000004', 'real', 'active', '', '', '', '', 0.00, 0.00, 2, NULL, '2025-03-27'),
(41, '', 'محمد', 'عباس', '09382221654', NULL, '', '', NULL, NULL, '2025-03-27 11:23:31', '2025-03-27 11:23:31', 'محمد عباس', '140401070005', 'real', 'active', '', '', '', '', 0.00, 0.00, 2, NULL, '2025-03-27'),
(42, '', 'محمد ', 'پاقدم', '09382063009', NULL, '', '', NULL, NULL, '2025-03-27 12:09:38', '2025-03-27 12:39:38', 'محمد  پاقدم', '140401070006', 'real', 'active', '', '', '', '', 0.00, 0.00, 2, NULL, '2025-03-27'),
(43, '', 'ریحانه', ' اسماعیلی', '09911785415', NULL, '', '', NULL, NULL, '2025-03-27 19:53:17', '2025-03-27 19:53:17', 'ریحانه  اسماعیلی', '140401070003', 'real', 'active', NULL, NULL, NULL, NULL, 0.00, 0.00, 2, NULL, '2025-03-27'),
(44, '', 'محمد', 'محمدی', '09159744549', NULL, '', '', NULL, NULL, '2025-03-27 20:50:13', '2025-03-27 20:50:13', 'محمد محمدی', '140401080003', 'real', 'active', NULL, NULL, NULL, NULL, 0.00, 0.00, 2, NULL, '2025-03-28'),
(48, '', 'محمد', ' اسماعیلی', '09159744556', NULL, '', '', NULL, NULL, '2025-03-27 21:11:21', '2025-03-27 21:11:21', 'محمد  اسماعیلی', '140401080004', 'real', 'active', NULL, NULL, NULL, NULL, 0.00, 0.00, 2, NULL, '2025-03-28'),
(49, '', 'مصفط', 'اکرا', '09380072011', NULL, '', '', NULL, NULL, '2025-03-27 21:14:59', '2025-03-27 21:16:51', 'مصفط اکرا', NULL, 'real', 'active', NULL, NULL, NULL, NULL, 0.00, 0.00, 2, '2025-03-27 21:16:51', '2025-03-28'),
(50, '', 'مهدی', 'اکرادی', '09159744545', NULL, '', '', NULL, NULL, '2025-03-27 21:15:15', '2025-03-27 21:16:48', 'مهدی اکرادی', NULL, 'real', 'active', NULL, NULL, NULL, NULL, 0.00, 0.00, 2, '2025-03-27 21:16:48', '2025-03-28'),
(51, '', 'مص', 'من', '09125222121', NULL, '', '', NULL, NULL, '2025-03-27 21:18:18', '2025-03-27 21:18:18', 'مص من', '140401080005', 'real', 'active', NULL, NULL, NULL, NULL, 0.00, 0.00, 2, NULL, '2025-03-28'),
(52, '', 'مهدی', 'سزشسی', '09382221650', NULL, '', '', NULL, NULL, '2025-03-27 21:18:56', '2025-03-27 21:18:56', 'مهدی سزشسی', '140401080006', 'real', 'active', NULL, NULL, NULL, NULL, 0.00, 0.00, 2, NULL, '2025-03-28'),
(53, '140401080001', 'مهدی', 'محمدددد', '09159744544', NULL, '', '', NULL, NULL, '2025-03-27 21:22:21', '2025-03-27 21:23:28', 'مهدی محمدددد', NULL, 'real', 'active', NULL, NULL, NULL, NULL, 0.00, 0.00, 2, '2025-03-27 21:23:28', '2025-03-28'),
(54, '', 'سیب', 'سی', '09153756305', NULL, '', '', NULL, NULL, '2025-03-27 21:25:27', '2025-03-27 21:25:27', 'سیب سی', '140401080007', 'real', 'active', NULL, NULL, NULL, NULL, 0.00, 0.00, 2, NULL, '2025-03-28'),
(55, '', 'ریحانه', 'عباس', '09159744552', NULL, '', '', NULL, NULL, '2025-03-28 00:33:41', '2025-03-28 00:33:41', 'ریحانه عباس', '140401080008', 'real', 'active', NULL, NULL, NULL, NULL, 0.00, 0.00, 2, NULL, '2025-03-28'),
(56, '', 'جواد', 'خداشاهی', '09150655378', NULL, '', '', NULL, NULL, '2025-03-29 16:42:40', '2025-03-29 16:42:40', 'جواد خداشاهی', '140401090009', 'real', 'active', NULL, NULL, NULL, NULL, 0.00, 0.00, 2, NULL, '2025-03-29');

-- --------------------------------------------------------

--
-- Table structure for table `customer_cleanup`
--

CREATE TABLE `customer_cleanup` (
  `id` int(11) NOT NULL,
  `last_cleanup` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customer_counter`
--

CREATE TABLE `customer_counter` (
  `id` int(11) NOT NULL,
  `last_number` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customer_counter`
--

INSERT INTO `customer_counter` (`id`, `last_number`) VALUES
(1, 9);

-- --------------------------------------------------------

--
-- Table structure for table `debtors`
--

CREATE TABLE `debtors` (
  `id` int(11) NOT NULL,
  `customer_name` varchar(255) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inventory_transactions`
--

CREATE TABLE `inventory_transactions` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `type` enum('in','out') NOT NULL,
  `quantity` int(11) NOT NULL,
  `reference_type` varchar(50) DEFAULT NULL,
  `reference_id` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

--
-- Dumping data for table `inventory_transactions`
--

INSERT INTO `inventory_transactions` (`id`, `product_id`, `type`, `quantity`, `reference_type`, `reference_id`, `description`, `created_by`, `created_at`) VALUES
(1, 2, 'in', 1, NULL, NULL, NULL, 2, '2025-03-23 17:20:11'),
(2, 2, 'in', 1, NULL, NULL, NULL, 2, '2025-03-23 17:31:07'),
(3, 3, 'out', 5, 'invoice', 1, 'کسر از موجودی بابت فاکتور شماره INV-00001', 2, '2025-03-24 01:01:19'),
(4, 4, 'out', 3, 'invoice', 2, 'کسر از موجودی بابت فاکتور شماره INV-00002', 2, '2025-03-24 01:01:53'),
(5, 2, 'out', 1, 'invoice', 3, 'کسر از موجودی بابت فاکتور شماره INV-00003', 2, '2025-03-24 09:11:47'),
(6, 4, 'out', 1, 'invoice', 4, 'کسر از موجودی بابت فاکتور شماره INV-00004', 2, '2025-03-24 12:36:52'),
(7, 3, 'out', 1, 'invoice', 4, 'کسر از موجودی بابت فاکتور شماره INV-00004', 2, '2025-03-24 12:36:52'),
(8, 2, 'out', 1, 'invoice', 5, 'کسر از موجودی بابت فاکتور شماره INV-00005', 2, '2025-03-24 15:39:54'),
(9, 2, 'in', 1, NULL, NULL, NULL, 2, '2025-03-24 16:53:30'),
(10, 3, 'in', 1, NULL, NULL, NULL, 2, '2025-03-24 16:53:42'),
(11, 3, 'out', 1, 'invoice', 6, 'کسر از موجودی بابت فاکتور شماره INV-00006', 2, '2025-03-24 16:54:31'),
(12, 2, 'in', 1, NULL, NULL, NULL, 2, '2025-03-24 18:35:05'),
(13, 4, 'out', 1, 'invoice', 7, 'کسر از موجودی بابت فاکتور شماره INV-00007', 2, '2025-03-24 18:37:57'),
(14, 2, 'out', 3, 'invoice', 7, 'کسر از موجودی بابت فاکتور شماره INV-00007', 2, '2025-03-24 18:37:57'),
(15, 3, 'in', 5, NULL, NULL, '', 2, '2025-03-25 01:34:31'),
(16, 2, 'out', 2, 'invoice', 8, 'کسر از موجودی بابت فاکتور شماره INV-00008', 2, '2025-03-26 02:08:46'),
(17, 3, 'out', 1, 'invoice', 9, 'کسر از موجودی بابت فاکتور شماره INV-00009', 2, '2025-03-26 02:12:41'),
(18, 2, 'out', 1, 'invoice', 17, 'کسر از موجودی بابت فاکتور شماره INV-00010', 2, '2025-03-26 13:14:37'),
(19, 3, 'out', 2, 'invoice', 17, 'کسر از موجودی بابت فاکتور شماره INV-00010', 2, '2025-03-26 13:14:37'),
(20, 3, 'out', 2, 'invoice', 18, 'کسر از موجودی بابت فاکتور شماره INV-00011', 2, '2025-03-26 13:18:19'),
(21, 2, 'in', 15, NULL, NULL, '', 2, '2025-03-25 01:34:31'),
(22, 3, 'in', 15, NULL, NULL, '', 2, '2025-03-25 01:34:31'),
(23, 2, 'in', 15, NULL, NULL, '', 2, '2025-03-25 01:34:31'),
(24, 2, 'out', 1, 'invoice', 19, 'کسر از موجودی بابت فاکتور شماره INV-00012', 2, '2025-03-26 13:40:52'),
(25, 3, 'out', 1, 'invoice', 34, 'کسر از موجودی بابت فاکتور شماره INV-00013', 2, '2025-03-27 12:14:22'),
(26, 2, 'out', 1, 'invoice', 35, 'کسر از موجودی بابت فاکتور شماره INV-00014', 2, '2025-03-27 14:56:03'),
(27, 2, 'out', 2, 'invoice', 36, 'کسر از موجودی بابت فاکتور شماره INV-00015', 2, '2025-03-27 16:40:06'),
(28, 2, 'out', 1, 'invoice', 37, 'کسر از موجودی بابت فاکتور شماره INV-00016', 2, '2025-03-28 04:04:37'),
(29, 2, 'out', 1, 'invoice', 38, 'کسر از موجودی بابت فاکتور شماره ptech-00017', 2, '2025-03-28 12:54:53'),
(30, 2, 'out', 5, 'invoice', 39, 'کسر از موجودی بابت فاکتور شماره ptech-00018', 2, '2025-03-28 15:09:21'),
(31, 3, 'out', 1, 'invoice', 40, 'کسر از موجودی بابت فاکتور شماره ptech-00019', 2, '2025-03-29 20:12:45');

-- --------------------------------------------------------

--
-- Table structure for table `invoices`
--

CREATE TABLE `invoices` (
  `id` int(11) NOT NULL,
  `invoice_number` varchar(50) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `total_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `tax_rate` decimal(10,2) NOT NULL DEFAULT 0.00,
  `tax_amount` decimal(10,2) DEFAULT NULL,
  `discount_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `final_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `payment_status` enum('paid','unpaid','partial') NOT NULL DEFAULT 'unpaid',
  `last_payment_type` varchar(20) DEFAULT NULL,
  `status` enum('draft','confirmed','cancelled') NOT NULL DEFAULT 'draft',
  `description` text DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

--
-- Dumping data for table `invoices`
--

INSERT INTO `invoices` (`id`, `invoice_number`, `customer_id`, `total_amount`, `tax_rate`, `tax_amount`, `discount_amount`, `final_amount`, `payment_status`, `last_payment_type`, `status`, `description`, `created_by`, `updated_by`, `created_at`, `updated_at`) VALUES
(1, 'INV-00001', 1, 2625000.00, 15.00, NULL, 15000.00, 3003750.00, 'unpaid', NULL, 'confirmed', NULL, 2, NULL, '2025-03-24 01:01:19', '2025-03-24 01:01:19'),
(2, 'INV-00002', 1, 3600000.00, 15.00, NULL, 5000.00, 4135000.00, 'unpaid', NULL, 'confirmed', NULL, 2, NULL, '2025-03-24 01:01:53', '2025-03-24 01:01:53'),
(3, 'INV-00003', 1, 952000.00, 9.00, NULL, 0.00, 1037680.00, 'paid', 'cash', 'confirmed', NULL, 2, NULL, '2025-03-24 09:11:47', '2025-03-24 09:12:07'),
(4, 'INV-00004', 1, 1725000.00, 10.00, NULL, 20000.00, 1877500.00, 'paid', 'card', 'confirmed', NULL, 2, NULL, '2025-03-24 12:36:52', '2025-03-24 14:28:13'),
(5, 'INV-00005', 1, 952000.00, 15.00, NULL, 50000.00, 1044800.00, 'paid', 'cash', 'confirmed', NULL, 2, NULL, '2025-03-24 15:39:54', '2025-03-24 15:40:44'),
(6, 'INV-00006', 1, 525000.00, 9.00, NULL, 10000.00, 562250.00, 'paid', 'cash', 'confirmed', NULL, 2, NULL, '2025-03-24 16:54:31', '2025-03-24 16:55:19'),
(7, 'INV-00007', 1, 4056000.00, 15.00, NULL, 50000.00, 4614400.00, 'paid', 'cash', 'confirmed', NULL, 2, NULL, '2025-03-24 18:37:57', '2025-03-24 18:39:59'),
(8, 'INV-00008', 1, 1904000.00, 9.00, 171360.00, 0.00, 2075360.00, 'paid', NULL, 'confirmed', NULL, 2, NULL, '2025-03-26 02:08:46', '2025-03-26 02:08:46'),
(9, 'INV-00009', 1, 525000.00, 9.00, 47250.00, 0.00, 572250.00, 'paid', NULL, 'confirmed', NULL, 2, NULL, '2025-03-26 02:12:41', '2025-03-26 02:12:41'),
(17, 'INV-00010', 1, 2002000.00, 9.00, 180180.00, 0.00, 2182180.00, 'paid', NULL, 'confirmed', NULL, 2, NULL, '2025-03-26 13:14:37', '2025-03-26 13:14:37'),
(18, 'INV-00011', 1, 1050000.00, 9.00, 94500.00, 0.00, 1144500.00, 'paid', NULL, 'confirmed', NULL, 2, NULL, '2025-03-26 13:18:19', '2025-03-26 13:18:19'),
(19, 'INV-00012', 1, 952000.00, 9.00, NULL, 2000.00, 1035680.00, 'unpaid', NULL, 'confirmed', NULL, 2, NULL, '2025-03-26 13:40:52', '2025-03-26 13:40:52'),
(20, '1', 1, 4858000.00, 9.00, 437220.00, 0.00, 5295220.00, 'paid', NULL, 'confirmed', NULL, 2, NULL, '2025-03-26 15:00:22', '2025-03-26 15:00:22'),
(34, 'INV-00013', 1, 525000.00, 9.00, NULL, 0.00, 572250.00, 'paid', 'cash', 'confirmed', NULL, 2, NULL, '2025-03-27 12:14:22', '2025-03-27 12:14:30'),
(35, 'INV-00014', 21, 952000.00, 9.00, NULL, 0.00, 1037680.00, 'unpaid', NULL, 'confirmed', NULL, 2, NULL, '2025-03-27 14:56:03', '2025-03-27 14:56:03'),
(36, 'INV-00015', 42, 1904000.00, 12.00, NULL, 56600.00, 2075880.00, 'paid', 'cash', 'confirmed', NULL, 2, NULL, '2025-03-27 16:10:06', '2025-03-27 18:49:45'),
(37, 'INV-00016', 55, 952000.00, 12.00, NULL, 0.00, 1066240.00, 'unpaid', NULL, 'confirmed', NULL, 2, NULL, '2025-03-28 04:04:37', '2025-03-28 04:04:37'),
(38, 'ptech-00017', 48, 952000.00, 12.00, NULL, 0.00, 1066240.00, 'paid', 'cash', 'confirmed', NULL, 2, NULL, '2025-03-28 12:54:53', '2025-03-28 15:09:35'),
(39, 'ptech-00018', 39, 4760000.00, 12.10, NULL, 0.00, 5335960.00, 'paid', 'cash', 'confirmed', NULL, 2, NULL, '2025-03-28 15:09:21', '2025-03-28 15:09:29'),
(40, 'ptech-00019', 56, 525000.00, 12.10, NULL, 0.00, 588525.00, 'paid', 'cash', 'confirmed', NULL, 2, NULL, '2025-03-29 20:12:45', '2025-03-29 20:14:00');

-- --------------------------------------------------------

--
-- Table structure for table `invoice_items`
--

CREATE TABLE `invoice_items` (
  `id` int(11) NOT NULL,
  `invoice_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(15,2) NOT NULL,
  `discount` decimal(15,2) DEFAULT 0.00,
  `total_amount` decimal(15,2) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

--
-- Dumping data for table `invoice_items`
--

INSERT INTO `invoice_items` (`id`, `invoice_id`, `product_id`, `quantity`, `price`, `discount`, `total_amount`, `created_at`) VALUES
(1, 1, 3, 5, 525000.00, 0.00, 2625000.00, '2025-03-24 01:01:19'),
(2, 2, 4, 3, 1200000.00, 0.00, 3600000.00, '2025-03-24 01:01:53'),
(3, 3, 2, 1, 952000.00, 0.00, 952000.00, '2025-03-24 09:11:47'),
(4, 4, 4, 1, 1200000.00, 0.00, 1200000.00, '2025-03-24 12:36:52'),
(5, 4, 3, 1, 525000.00, 0.00, 525000.00, '2025-03-24 12:36:52'),
(6, 5, 2, 1, 952000.00, 0.00, 952000.00, '2025-03-24 15:39:54'),
(7, 6, 3, 1, 525000.00, 0.00, 525000.00, '2025-03-24 16:54:31'),
(8, 7, 4, 1, 1200000.00, 0.00, 1200000.00, '2025-03-24 18:37:57'),
(9, 7, 2, 3, 952000.00, 0.00, 2856000.00, '2025-03-24 18:37:57'),
(10, 8, 2, 2, 952000.00, 0.00, 1904000.00, '2025-03-26 02:08:46'),
(11, 9, 3, 1, 525000.00, 0.00, 525000.00, '2025-03-26 02:12:41'),
(19, 17, 2, 1, 952000.00, 0.00, 952000.00, '2025-03-26 13:14:37'),
(20, 17, 3, 2, 525000.00, 0.00, 1050000.00, '2025-03-26 13:14:37'),
(21, 18, 3, 2, 525000.00, 0.00, 1050000.00, '2025-03-26 13:18:19'),
(22, 19, 2, 1, 952000.00, 0.00, 952000.00, '2025-03-26 13:40:52'),
(23, 20, 3, 2, 952000.00, 0.00, 1904000.00, '2025-03-26 15:00:22'),
(24, 20, 2, 4, 952000.00, 0.00, 3808000.00, '2025-03-26 15:00:22'),
(25, 34, 3, 1, 525000.00, 0.00, 525000.00, '2025-03-27 12:14:22'),
(26, 35, 2, 1, 952000.00, 0.00, 952000.00, '2025-03-27 14:56:03'),
(27, 36, 2, 2, 952000.00, 0.00, 1904000.00, '2025-03-27 16:40:06'),
(28, 37, 2, 1, 952000.00, 0.00, 952000.00, '2025-03-28 04:04:37'),
(29, 38, 2, 1, 952000.00, 0.00, 952000.00, '2025-03-28 12:54:53'),
(30, 39, 2, 5, 952000.00, 0.00, 4760000.00, '2025-03-28 15:09:21'),
(31, 40, 3, 1, 525000.00, 0.00, 525000.00, '2025-03-29 20:12:45');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `invoice_id` int(11) NOT NULL,
  `payment_type` enum('cash','card','cheque','installment') NOT NULL,
  `amount` decimal(20,2) NOT NULL,
  `payment_date` date NOT NULL,
  `description` text DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `invoice_id`, `payment_type`, `amount`, `payment_date`, `description`, `created_by`, `created_at`, `updated_by`, `updated_at`) VALUES
(8, 2, 'cash', 4135000.00, '2025-03-24', '', 2, '2025-03-24 04:49:14', NULL, NULL),
(9, 1, 'card', 1000000.00, '2025-03-24', '', 2, '2025-03-24 05:00:06', NULL, NULL),
(10, 3, 'cash', 1037680.00, '2025-03-24', 'سزسیز', 2, '2025-03-24 09:12:07', NULL, NULL),
(12, 4, 'card', 1877500.00, '2025-03-24', 'سیبسیب یسبس سیب', 2, '2025-03-24 14:28:13', NULL, NULL),
(13, 5, 'cash', 1044800.00, '2025-03-24', 'مهغانلخه8غ', 2, '2025-03-24 15:40:44', NULL, NULL),
(14, 6, 'cash', 500000.00, '2025-03-24', '', 2, '2025-03-24 16:54:56', NULL, NULL),
(15, 6, 'cash', 62250.00, '2025-03-24', '', 2, '2025-03-24 16:55:19', NULL, NULL),
(16, 7, 'cash', 4000000.00, '2025-03-24', '', 2, '2025-03-24 18:39:31', NULL, NULL),
(17, 7, 'cash', 614400.00, '2025-03-24', '', 2, '2025-03-24 18:39:59', NULL, NULL),
(18, 8, 'cash', 2075360.00, '2025-03-26', NULL, 2, '2025-03-26 02:08:46', NULL, NULL),
(19, 9, 'cash', 572250.00, '2025-03-26', NULL, 2, '2025-03-26 02:12:41', NULL, NULL),
(20, 17, 'cash', 2182180.00, '2025-03-26', NULL, 2, '2025-03-26 13:14:37', NULL, NULL),
(21, 18, 'cash', 1144500.00, '2025-03-26', NULL, 2, '2025-03-26 13:18:19', NULL, NULL),
(22, 20, 'cash', 5295220.00, '2025-03-26', NULL, 2, '2025-03-26 15:00:22', NULL, NULL),
(23, 34, 'cash', 572250.00, '2025-03-27', '', 2, '2025-03-27 12:14:30', NULL, NULL),
(24, 36, 'cash', 1000000.00, '2025-03-27', '', 2, '2025-03-27 16:40:28', NULL, NULL),
(25, 36, 'cash', 1075880.00, '2025-03-27', '', 2, '2025-03-27 18:49:45', NULL, NULL),
(26, 39, 'cash', 5335960.00, '2025-03-28', '', 2, '2025-03-28 15:09:29', NULL, NULL),
(27, 38, 'cash', 1066240.00, '2025-03-28', '', 2, '2025-03-28 15:09:35', NULL, NULL),
(28, 40, 'cash', 100000.00, '2025-03-29', '', 2, '2025-03-29 20:13:22', NULL, NULL),
(29, 40, 'cash', 488525.00, '2025-03-29', '', 2, '2025-03-29 20:14:00', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `payment_card_details`
--

CREATE TABLE `payment_card_details` (
  `id` int(11) NOT NULL,
  `payment_id` int(11) NOT NULL,
  `card_number` varchar(16) NOT NULL,
  `tracking_number` varchar(50) NOT NULL,
  `bank_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

--
-- Dumping data for table `payment_card_details`
--

INSERT INTO `payment_card_details` (`id`, `payment_id`, `card_number`, `tracking_number`, `bank_name`) VALUES
(1, 9, '6037997570556663', '100000', 'melli'),
(2, 12, '6037997570556663', '654968498', 'melli');

-- --------------------------------------------------------

--
-- Table structure for table `payment_cheque_details`
--

CREATE TABLE `payment_cheque_details` (
  `id` int(11) NOT NULL,
  `payment_id` int(11) NOT NULL,
  `cheque_number` varchar(50) NOT NULL,
  `due_date` date NOT NULL,
  `bank_name` varchar(50) NOT NULL,
  `branch_name` varchar(100) NOT NULL,
  `account_number` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payment_installments`
--

CREATE TABLE `payment_installments` (
  `id` int(11) NOT NULL,
  `payment_id` int(11) NOT NULL,
  `installment_number` int(11) NOT NULL,
  `amount` decimal(20,2) NOT NULL,
  `due_date` date NOT NULL,
  `status` enum('pending','paid','overdue') NOT NULL DEFAULT 'pending',
  `paid_at` datetime DEFAULT NULL,
  `paid_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `people`
--

CREATE TABLE `people` (
  `id` int(11) NOT NULL,
  `first_name` varchar(255) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `mobile` varchar(20) NOT NULL,
  `type` enum('real','legal') DEFAULT 'real',
  `national_code` varchar(10) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `company` varchar(255) DEFAULT NULL,
  `economic_code` varchar(12) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `credit_limit` decimal(20,2) DEFAULT 0.00,
  `credit_balance` decimal(20,2) DEFAULT 0.00,
  `address` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` int(11) DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

--
-- Dumping data for table `people`
--

INSERT INTO `people` (`id`, `first_name`, `last_name`, `mobile`, `type`, `national_code`, `phone`, `company`, `economic_code`, `email`, `credit_limit`, `credit_balance`, `address`, `notes`, `profile_image`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES
(1, 'مص', 'یحخب', '09911785422', 'real', '', '', '', '', '', 0.00, 0.00, '', 'سیب', '', '2025-04-21 00:41:48', '2025-04-21 00:41:48', 2, NULL),
(2, 'سیب', 'سیب', '09159745552', 'real', '', '', '', '', '', 0.00, 0.00, '', '', '', '2025-04-21 00:49:56', '2025-04-21 00:49:56', 2, NULL),
(3, 'سیب', 'سیب', '09159745552', 'real', '', '', '', '', '', 0.00, 0.00, '', '', '', '2025-04-21 00:54:49', '2025-04-21 01:13:27', 2, '2025-04-21 01:13:27');

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `display_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `display_name`, `description`, `created_at`, `updated_at`) VALUES
(1, 'dashboard_view', 'مشاهده داشبورد', 'دسترسی به مشاهده داشبورد', '2025-04-21 01:48:14', '2025-04-21 01:48:14'),
(2, 'people_view', 'مشاهده اشخاص', 'دسترسی به مشاهده لیست اشخاص', '2025-04-21 01:48:14', '2025-04-21 01:48:14'),
(3, 'people_add', 'افزودن شخص', 'دسترسی به افزودن شخص جدید', '2025-04-21 01:48:14', '2025-04-21 01:48:14'),
(4, 'people_edit', 'ویرایش شخص', 'دسترسی به ویرایش اطلاعات اشخاص', '2025-04-21 01:48:14', '2025-04-21 01:48:14'),
(5, 'people_delete', 'حذف شخص', 'دسترسی به حذف اشخاص', '2025-04-21 01:48:14', '2025-04-21 01:48:14');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `code` varchar(50) DEFAULT NULL,
  `name` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `brand` varchar(255) DEFAULT NULL,
  `model` varchar(255) DEFAULT NULL,
  `technical_features` text DEFAULT NULL,
  `customs_tariff_code` varchar(255) DEFAULT NULL,
  `barcode` varchar(255) DEFAULT NULL,
  `store_barcode` varchar(255) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `purchase_price` decimal(15,2) DEFAULT 0.00,
  `sale_price` decimal(15,2) DEFAULT 0.00,
  `quantity` int(11) DEFAULT 0,
  `min_quantity` int(11) DEFAULT 0,
  `unit` varchar(20) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `code`, `name`, `description`, `brand`, `model`, `technical_features`, `customs_tariff_code`, `barcode`, `store_barcode`, `image`, `category_id`, `purchase_price`, `sale_price`, `quantity`, `min_quantity`, `unit`, `status`, `created_at`, `updated_at`) VALUES
(2, '65465', 'کیبورد تسکو مدل 1714', '6565ضسیب', NULL, NULL, NULL, NULL, NULL, NULL, 'uploads/4436481.png', 1, 675000.00, 952000.00, 8, 1, NULL, 'active', '2025-03-23 14:12:10', '2025-03-28 15:09:21'),
(3, '1452', 'موس تسکو مدل 1425', 'ماوس مخصوص بازی تسکو مدل TM 765GA یکی از تولیدات برند تسکو است. این برند ایرانی معروف محصولات متنوعی از جمله لوازم جانبی کامپیوتر را تولید می‌کند. بیش از دو دهه از آغاز فعالیت‌های برند تسکو می‌گذرد و در این مدت محصولات این برند توانسته‌اند نظر بخش قابل‌توجهی از مشتریان را به دست بیاورد. این مدل ماوس برند تسکو هم به دلیل قیمت مناسب و ویژگی‌های خوبی که دارد، جزو یکی از محصولات پرفروش این شرکت محسوب می‌شود. ماوس مذکور توسط رابط USB به کامپیوتر و لپ‌تاپ متصل شده و با سیم کار می‌کند. محدوده دقت این ماوس حدود 3200 است. به همین دلیل برای انجام کارهای روزمره با کامپیوتر انتخاب بسیار خوبی به نظر می‌رسد. همچنین در ساخت این مدل ماوس از حسگر اپتیکال استفاده شده است. ماوس‌هایی با این ویژگی به دلیل حساسیت زیادی که نسبت به حرکت دارند از دقت بالایی برخوردار هستند. از دیگر ویژگی‌های این مدل ماوس می‌توان به طراحی بسیار خاص و منحصربه‌فرد و وزن مناسبش اشاره کرد.\r\n', 'تسکو tsco', 'TM-765GA', 'مشخصات کلی\r\n\r\nوزن\r\n\r\n۱۱۱ گرم\r\n\r\nابعاد\r\n\r\n۱۲۴.۵×۶۶.۴×۳۹.۲ میلی‌متر\r\n\r\nتعداد کلید\r\n\r\n۶ عدد\r\n\r\nمشخصات فنی\r\n\r\nنوع اتصال\r\n\r\nبا سیم\r\n\r\nنوع رابط\r\n\r\nUSB\r\n\r\nنوع حسگر\r\n\r\nاپتیکال\r\n\r\nمحدوده دقت\r\n\r\nبیشتر از ۳۲۰۰\r\n\r\nدقت\r\n\r\nتا ۷۲۰۰ DPI\r\n\r\nطول کابل\r\n\r\n۱۸۰ سانتی‌متر\r\n\r\nجنس کابل\r\n\r\nکنفی\r\n\r\nضربه‌پذیری کلیدها\r\n\r\nتا سه میلیون کلیک\r\n\r\nسازگار با سیستم‌ عامل‌های\r\n\r\nتمامی سیستم عامل‌ها\r\n\r\nسایر قابلیت‌ها\r\n\r\nنورپردازی LED Lighting بدنه / دکمه اختصاصی برای تنظیم DPI\r\n\r\n', '', '210765405004078', '4473251656546', 'uploads/tesco.jpg', 1, 452100.00, 525000.00, 25, 1, NULL, 'active', '2025-03-23 14:36:50', '2025-03-29 20:12:45'),
(4, '581256', 'موبایل نوکیا 1100', 'خرید اینترنتی گوشی موبایل نوکیا مدل 1100 تک سیم کارت ظرفیت 4 مگابایت و رم 4 مگابایت به همراه مقایسه، بررسی مشخصات و لیست قیمت امروز در فروشگاه اینترنتی ...', 'نوکیا', '1100', '4 مگابایت و رم 4 مگابایت به همراه مقایسه،', '', '', 'tel-C45C97', 'uploads/3.JPG', 4, 1100000.00, 1200000.00, 0, 1, NULL, 'active', '2025-03-23 15:22:24', '2025-03-24 18:37:57');

-- --------------------------------------------------------

--
-- Table structure for table `profit_loss`
--

CREATE TABLE `profit_loss` (
  `id` int(11) NOT NULL,
  `date` date NOT NULL,
  `profit` decimal(10,2) DEFAULT 0.00,
  `loss` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `display_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `display_name`, `description`, `created_at`, `updated_at`) VALUES
(1, 'super_admin', 'مدیر کل', 'دسترسی نامحدود به تمام بخش‌ها', '2025-04-21 01:48:14', '2025-04-21 01:48:14'),
(2, 'admin', 'مدیر', 'دسترسی کامل به سیستم', '2025-04-21 01:48:14', '2025-04-21 01:48:14'),
(3, 'user', 'کاربر عادی', 'دسترسی محدود به سیستم', '2025-04-21 01:48:14', '2025-04-21 01:48:14');

-- --------------------------------------------------------

--
-- Table structure for table `role_permissions`
--

CREATE TABLE `role_permissions` (
  `role_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

--
-- Dumping data for table `role_permissions`
--

INSERT INTO `role_permissions` (`role_id`, `permission_id`, `created_at`) VALUES
(2, 1, '2025-04-21 02:05:14'),
(2, 2, '2025-04-21 02:05:14'),
(2, 3, '2025-04-21 02:05:14'),
(2, 4, '2025-04-21 02:05:14'),
(2, 5, '2025-04-21 02:05:14'),
(3, 1, '2025-04-21 01:48:14'),
(3, 2, '2025-04-21 01:48:14');

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `key` varchar(100) NOT NULL,
  `value` text DEFAULT NULL,
  `type` varchar(50) DEFAULT 'text',
  `description` text DEFAULT NULL,
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `tax_rate` decimal(5,2) DEFAULT 9.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `key`, `value`, `type`, `description`, `updated_at`, `tax_rate`) VALUES
(1, 'company_name', 'شرکت پاره سنگ', 'text', 'نام شرکت', '2025-03-23 14:00:13', 9.00),
(2, 'company_phone', '', 'text', 'شماره تماس شرکت', '2025-03-23 14:00:13', 9.00),
(3, 'company_address', '', 'textarea', 'آدرس شرکت', '2025-03-23 14:00:13', 9.00),
(4, 'invoice_prefix', 'ptech-', 'text', 'پیشوند شماره فاکتور', '2025-03-28 12:54:21', 9.00),
(5, 'tax_rate', '12.1', 'number', 'درصد مالیات', '2025-03-28 14:04:58', 9.00),
(6, 'currency', 'تومان', 'text', 'واحد پول', '2025-03-23 14:00:13', 9.00),
(7, 'organization_name', 'فروشگاه پارس تک', 'text', 'نام سازمان', '2025-03-27 15:18:41', 9.00),
(8, 'organization_address', 'نقاب روبروی سه راه مسجد جامع', 'text', 'آدرس سازمان', '2025-03-27 15:18:41', 9.00),
(9, 'organization_phone', '05145220145', 'text', 'تلفن ثابت', '2025-03-27 15:18:41', 9.00),
(10, 'organization_mobile', '09380072019', 'text', 'شماره موبایل', '2025-03-27 15:18:41', 9.00),
(11, 'organization_email', 'Akradim@gmail.com', 'text', 'ایمیل سازمان', '2025-03-27 15:18:41', 9.00),
(12, 'organization_logo', 'uploads/logo_67e53b19c2528.png', 'text', 'آدرس لوگو', '2025-03-27 15:18:41', 9.00);

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `type` enum('income','expense') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `role` enum('admin','user') DEFAULT 'user',
  `status` enum('active','inactive') DEFAULT 'active',
  `last_login` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_super_admin` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `full_name`, `email`, `role`, `status`, `last_login`, `created_at`, `updated_at`, `is_super_admin`) VALUES
(1, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'مدیر سیستم', 'admin@example.com', 'admin', 'active', NULL, '2025-03-23 14:00:13', '2025-04-21 01:48:14', 1),
(2, 'akradim', '$2y$10$9tTHkKeK1OcnTPfsXnb2lu3gdtBNO7XaYfm1OtMjF0ovufhAsYgYa', 'مصطفی اکرادی', 'akradim@gmail.com', 'user', 'active', '2025-04-21 01:54:16', '2025-03-23 14:00:41', '2025-04-21 01:54:16', 0);

-- --------------------------------------------------------

--
-- Table structure for table `user_roles`
--

CREATE TABLE `user_roles` (
  `user_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

--
-- Dumping data for table `user_roles`
--

INSERT INTO `user_roles` (`user_id`, `role_id`, `created_at`) VALUES
(1, 2, '2025-04-21 01:48:14');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cash_flows`
--
ALTER TABLE `cash_flows`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `parent_id` (`parent_id`);

--
-- Indexes for table `cheques`
--
ALTER TABLE `cheques`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `customer_cleanup`
--
ALTER TABLE `customer_cleanup`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `customer_counter`
--
ALTER TABLE `customer_counter`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `debtors`
--
ALTER TABLE `debtors`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `inventory_transactions`
--
ALTER TABLE `inventory_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `invoices`
--
ALTER TABLE `invoices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `invoice_number` (`invoice_number`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `payment_status` (`payment_status`),
  ADD KEY `status` (`status`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indexes for table `invoice_items`
--
ALTER TABLE `invoice_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `invoice_id` (`invoice_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `invoice_id` (`invoice_id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indexes for table `payment_card_details`
--
ALTER TABLE `payment_card_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `payment_id` (`payment_id`);

--
-- Indexes for table `payment_cheque_details`
--
ALTER TABLE `payment_cheque_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `payment_id` (`payment_id`);

--
-- Indexes for table `payment_installments`
--
ALTER TABLE `payment_installments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `payment_id` (`payment_id`),
  ADD KEY `paid_by` (`paid_by`);

--
-- Indexes for table `people`
--
ALTER TABLE `people`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `profit_loss`
--
ALTER TABLE `profit_loss`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD PRIMARY KEY (`role_id`,`permission_id`),
  ADD KEY `permission_id` (`permission_id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `key` (`key`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_roles`
--
ALTER TABLE `user_roles`
  ADD PRIMARY KEY (`user_id`,`role_id`),
  ADD KEY `role_id` (`role_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cash_flows`
--
ALTER TABLE `cash_flows`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `cheques`
--
ALTER TABLE `cheques`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- AUTO_INCREMENT for table `customer_cleanup`
--
ALTER TABLE `customer_cleanup`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `customer_counter`
--
ALTER TABLE `customer_counter`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `debtors`
--
ALTER TABLE `debtors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `inventory_transactions`
--
ALTER TABLE `inventory_transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `invoices`
--
ALTER TABLE `invoices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `invoice_items`
--
ALTER TABLE `invoice_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `payment_card_details`
--
ALTER TABLE `payment_card_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `payment_cheque_details`
--
ALTER TABLE `payment_cheque_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payment_installments`
--
ALTER TABLE `payment_installments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `people`
--
ALTER TABLE `people`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `profit_loss`
--
ALTER TABLE `profit_loss`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `categories`
--
ALTER TABLE `categories`
  ADD CONSTRAINT `categories_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `inventory_transactions`
--
ALTER TABLE `inventory_transactions`
  ADD CONSTRAINT `inventory_transactions_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `inventory_transactions_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `invoices`
--
ALTER TABLE `invoices`
  ADD CONSTRAINT `invoices_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`),
  ADD CONSTRAINT `invoices_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `invoices_ibfk_3` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `invoice_items`
--
ALTER TABLE `invoice_items`
  ADD CONSTRAINT `invoice_items_ibfk_1` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `invoice_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`),
  ADD CONSTRAINT `payments_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `payments_ibfk_3` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `payment_card_details`
--
ALTER TABLE `payment_card_details`
  ADD CONSTRAINT `payment_card_details_ibfk_1` FOREIGN KEY (`payment_id`) REFERENCES `payments` (`id`);

--
-- Constraints for table `payment_cheque_details`
--
ALTER TABLE `payment_cheque_details`
  ADD CONSTRAINT `payment_cheque_details_ibfk_1` FOREIGN KEY (`payment_id`) REFERENCES `payments` (`id`);

--
-- Constraints for table `payment_installments`
--
ALTER TABLE `payment_installments`
  ADD CONSTRAINT `payment_installments_ibfk_1` FOREIGN KEY (`payment_id`) REFERENCES `payments` (`id`),
  ADD CONSTRAINT `payment_installments_ibfk_2` FOREIGN KEY (`paid_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `people`
--
ALTER TABLE `people`
  ADD CONSTRAINT `people_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `products_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD CONSTRAINT `role_permissions_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_permissions_ibfk_2` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_roles`
--
ALTER TABLE `user_roles`
  ADD CONSTRAINT `user_roles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_roles_ibfk_2` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
