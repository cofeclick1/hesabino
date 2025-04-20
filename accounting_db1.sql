-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 24, 2025 at 06:26 PM
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
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `id` int(11) NOT NULL,
  `first_name` varchar(255) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `mobile` varchar(50) NOT NULL,
  `address` text DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `name` varchar(255) NOT NULL,
  `code` varchar(50) DEFAULT NULL,
  `type` enum('real','legal') DEFAULT 'real',
  `national_code` varchar(10) DEFAULT NULL,
  `economic_code` varchar(12) DEFAULT NULL,
  `company` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `credit_limit` decimal(20,2) DEFAULT 0.00,
  `credit_balance` decimal(20,2) DEFAULT 0.00,
  `created_by` int(11) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`id`, `first_name`, `last_name`, `mobile`, `address`, `email`, `description`, `image`, `created_at`, `updated_at`, `name`, `code`, `type`, `national_code`, `economic_code`, `company`, `notes`, `credit_limit`, `credit_balance`, `created_by`, `deleted_at`) VALUES
(1, 'مسعود', 'عباس ابدی', '091525105041', '', '', '', 'uploads/tesco.jpg', '2025-03-23 15:22:21', '2025-03-23 15:35:43', '', NULL, 'real', NULL, NULL, NULL, NULL, 0.00, 0.00, NULL, NULL),
(2, '', '', '0000000000', NULL, NULL, NULL, NULL, '2025-03-24 13:11:40', '2025-03-24 13:11:40', 'فروش سریع ۱۴۰۴۰۱۰۴0001', NULL, 'real', NULL, NULL, NULL, NULL, 0.00, 0.00, NULL, NULL),
(4, '', '', '0000000000', NULL, NULL, NULL, NULL, '2025-03-24 13:13:36', '2025-03-24 13:13:36', 'فروش سریع ۱۴۰۴۰۱۰۴0002', NULL, 'real', NULL, NULL, NULL, NULL, 0.00, 0.00, NULL, NULL),
(5, '', '', '0000000000', NULL, NULL, NULL, NULL, '2025-03-24 15:05:45', '2025-03-24 15:05:45', 'فروش سریع ۱۴۰۴۰۱۰۴0003', NULL, 'real', NULL, NULL, NULL, NULL, 0.00, 0.00, NULL, NULL);

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
(14, 2, 'out', 3, 'invoice', 7, 'کسر از موجودی بابت فاکتور شماره INV-00007', 2, '2025-03-24 18:37:57');

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

INSERT INTO `invoices` (`id`, `invoice_number`, `customer_id`, `total_amount`, `tax_rate`, `discount_amount`, `final_amount`, `payment_status`, `last_payment_type`, `status`, `description`, `created_by`, `updated_by`, `created_at`, `updated_at`) VALUES
(1, 'INV-00001', 1, 2625000.00, 15.00, 15000.00, 3003750.00, 'unpaid', NULL, 'confirmed', NULL, 2, NULL, '2025-03-24 01:01:19', '2025-03-24 01:01:19'),
(2, 'INV-00002', 1, 3600000.00, 15.00, 5000.00, 4135000.00, 'unpaid', NULL, 'confirmed', NULL, 2, NULL, '2025-03-24 01:01:53', '2025-03-24 01:01:53'),
(3, 'INV-00003', 1, 952000.00, 9.00, 0.00, 1037680.00, 'paid', 'cash', 'confirmed', NULL, 2, NULL, '2025-03-24 09:11:47', '2025-03-24 09:12:07'),
(4, 'INV-00004', 1, 1725000.00, 10.00, 20000.00, 1877500.00, 'paid', 'card', 'confirmed', NULL, 2, NULL, '2025-03-24 12:36:52', '2025-03-24 14:28:13'),
(5, 'INV-00005', 1, 952000.00, 15.00, 50000.00, 1044800.00, 'paid', 'cash', 'confirmed', NULL, 2, NULL, '2025-03-24 15:39:54', '2025-03-24 15:40:44'),
(6, 'INV-00006', 1, 525000.00, 9.00, 10000.00, 562250.00, 'paid', 'cash', 'confirmed', NULL, 2, NULL, '2025-03-24 16:54:31', '2025-03-24 16:55:19'),
(7, 'INV-00007', 1, 4056000.00, 15.00, 50000.00, 4614400.00, 'paid', 'cash', 'confirmed', NULL, 2, NULL, '2025-03-24 18:37:57', '2025-03-24 18:39:59');

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
(9, 7, 2, 3, 952000.00, 0.00, 2856000.00, '2025-03-24 18:37:57');

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
(17, 7, 'cash', 614400.00, '2025-03-24', '', 2, '2025-03-24 18:39:59', NULL, NULL);

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
(2, '65465', 'کیبورد تسکو مدل 1714', '6565ضسیب', NULL, NULL, NULL, NULL, NULL, NULL, 'uploads/4436481.png', 1, 675000.00, 952000.00, 3, 1, NULL, 'active', '2025-03-23 14:12:10', '2025-03-24 18:37:57'),
(3, '1452', 'موس تسکو مدل 1425', 'ماوس مخصوص بازی تسکو مدل TM 765GA یکی از تولیدات برند تسکو است. این برند ایرانی معروف محصولات متنوعی از جمله لوازم جانبی کامپیوتر را تولید می‌کند. بیش از دو دهه از آغاز فعالیت‌های برند تسکو می‌گذرد و در این مدت محصولات این برند توانسته‌اند نظر بخش قابل‌توجهی از مشتریان را به دست بیاورد. این مدل ماوس برند تسکو هم به دلیل قیمت مناسب و ویژگی‌های خوبی که دارد، جزو یکی از محصولات پرفروش این شرکت محسوب می‌شود. ماوس مذکور توسط رابط USB به کامپیوتر و لپ‌تاپ متصل شده و با سیم کار می‌کند. محدوده دقت این ماوس حدود 3200 است. به همین دلیل برای انجام کارهای روزمره با کامپیوتر انتخاب بسیار خوبی به نظر می‌رسد. همچنین در ساخت این مدل ماوس از حسگر اپتیکال استفاده شده است. ماوس‌هایی با این ویژگی به دلیل حساسیت زیادی که نسبت به حرکت دارند از دقت بالایی برخوردار هستند. از دیگر ویژگی‌های این مدل ماوس می‌توان به طراحی بسیار خاص و منحصربه‌فرد و وزن مناسبش اشاره کرد.\r\n', 'تسکو tsco', 'TM-765GA', 'مشخصات کلی\r\n\r\nوزن\r\n\r\n۱۱۱ گرم\r\n\r\nابعاد\r\n\r\n۱۲۴.۵×۶۶.۴×۳۹.۲ میلی‌متر\r\n\r\nتعداد کلید\r\n\r\n۶ عدد\r\n\r\nمشخصات فنی\r\n\r\nنوع اتصال\r\n\r\nبا سیم\r\n\r\nنوع رابط\r\n\r\nUSB\r\n\r\nنوع حسگر\r\n\r\nاپتیکال\r\n\r\nمحدوده دقت\r\n\r\nبیشتر از ۳۲۰۰\r\n\r\nدقت\r\n\r\nتا ۷۲۰۰ DPI\r\n\r\nطول کابل\r\n\r\n۱۸۰ سانتی‌متر\r\n\r\nجنس کابل\r\n\r\nکنفی\r\n\r\nضربه‌پذیری کلیدها\r\n\r\nتا سه میلیون کلیک\r\n\r\nسازگار با سیستم‌ عامل‌های\r\n\r\nتمامی سیستم عامل‌ها\r\n\r\nسایر قابلیت‌ها\r\n\r\nنورپردازی LED Lighting بدنه / دکمه اختصاصی برای تنظیم DPI\r\n\r\n', '', '210765405004078', '4473251656546', 'uploads/tesco.jpg', 1, 452100.00, 525000.00, 0, 1, NULL, 'active', '2025-03-23 14:36:50', '2025-03-24 16:54:31'),
(4, '581256', 'موبایل نوکیا 1100', 'خرید اینترنتی گوشی موبایل نوکیا مدل 1100 تک سیم کارت ظرفیت 4 مگابایت و رم 4 مگابایت به همراه مقایسه، بررسی مشخصات و لیست قیمت امروز در فروشگاه اینترنتی ...', 'نوکیا', '1100', '4 مگابایت و رم 4 مگابایت به همراه مقایسه،', '', '', 'tel-C45C97', 'uploads/3.JPG', 4, 1100000.00, 1200000.00, 0, 1, NULL, 'active', '2025-03-23 15:22:24', '2025-03-24 18:37:57');

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
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `key`, `value`, `type`, `description`, `updated_at`) VALUES
(1, 'company_name', 'شرکت پاره سنگ', 'text', 'نام شرکت', '2025-03-23 14:00:13'),
(2, 'company_phone', '', 'text', 'شماره تماس شرکت', '2025-03-23 14:00:13'),
(3, 'company_address', '', 'textarea', 'آدرس شرکت', '2025-03-23 14:00:13'),
(4, 'invoice_prefix', 'INV-', 'text', 'پیشوند شماره فاکتور', '2025-03-23 14:00:13'),
(5, 'tax_rate', '9', 'number', 'درصد مالیات', '2025-03-23 14:00:13'),
(6, 'currency', 'تومان', 'text', 'واحد پول', '2025-03-23 14:00:13');

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
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `full_name`, `email`, `role`, `status`, `last_login`, `created_at`, `updated_at`) VALUES
(1, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'مدیر سیستم', 'admin@example.com', 'admin', 'active', NULL, '2025-03-23 14:00:13', '2025-03-23 14:00:13'),
(2, 'akradim', '$2y$10$9tTHkKeK1OcnTPfsXnb2lu3gdtBNO7XaYfm1OtMjF0ovufhAsYgYa', 'مصطفی اکرادی', 'akradim@gmail.com', 'user', 'active', '2025-03-23 23:48:33', '2025-03-23 14:00:41', '2025-03-23 23:48:33');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `parent_id` (`parent_id`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

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
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `key` (`key`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `inventory_transactions`
--
ALTER TABLE `inventory_transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `invoices`
--
ALTER TABLE `invoices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `invoice_items`
--
ALTER TABLE `invoice_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

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
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

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
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `products_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
