-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Mar 18, 2026 at 06:21 AM
-- Server version: 8.4.3
-- PHP Version: 8.3.16

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `occ_coop`
--

-- --------------------------------------------------------

--
-- Table structure for table `accounts`
--

CREATE TABLE `accounts` (
  `account_id` int NOT NULL,
  `member_id` int NOT NULL,
  `account_type_id` int NOT NULL,
  `account_number` varchar(50) DEFAULT NULL,
  `status` enum('active','closed') DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `account_types`
--

CREATE TABLE `account_types` (
  `account_type_id` int NOT NULL,
  `type_name` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `account_types`
--

INSERT INTO `account_types` (`account_type_id`, `type_name`) VALUES
(2, 'capital_share'),
(3, 'loan'),
(1, 'savings');

-- --------------------------------------------------------

--
-- Table structure for table `email_logs`
--

CREATE TABLE `email_logs` (
  `id` int NOT NULL,
  `loan_id` int NOT NULL,
  `member_email` varchar(255) NOT NULL,
  `email_type` varchar(50) NOT NULL,
  `sent_date` datetime NOT NULL,
  `status` enum('sent','failed') NOT NULL,
  `error_message` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `loans`
--

CREATE TABLE `loans` (
  `loan_id` int NOT NULL,
  `account_id` int NOT NULL,
  `loan_type_id` int NOT NULL,
  `fund_id` int DEFAULT NULL,
  `requested_amount` decimal(12,2) NOT NULL,
  `approved_amount` decimal(12,2) DEFAULT '0.00',
  `service_charge` decimal(12,2) NOT NULL DEFAULT '0.00',
  `insurance` decimal(12,2) NOT NULL DEFAULT '0.00',
  `doc_stamp_fee` decimal(12,2) NOT NULL DEFAULT '0.00',
  `interest_rate` decimal(5,2) NOT NULL,
  `term_value` int NOT NULL,
  `term_unit` enum('days','weeks','months') NOT NULL DEFAULT 'months',
  `payment_frequency` enum('daily','weekly','monthly') NOT NULL DEFAULT 'monthly',
  `status` enum('pending','approved','ongoing','overdue','released','paid','rejected','canceled') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT 'pending',
  `application_date` date NOT NULL,
  `approved_date` date DEFAULT NULL,
  `released_date` date DEFAULT NULL,
  `total_interest` decimal(12,2) DEFAULT '0.00',
  `total_due` decimal(12,2) DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `loan_comaker`
--

CREATE TABLE `loan_comaker` (
  `comaker_id` int NOT NULL,
  `loan_id` int NOT NULL,
  `comaker_member_id` int NOT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `loan_payments`
--

CREATE TABLE `loan_payments` (
  `payment_id` int NOT NULL,
  `loan_id` int NOT NULL,
  `schedule_id` int DEFAULT NULL,
  `account_id` int NOT NULL,
  `amount_paid` decimal(12,2) NOT NULL,
  `principal_paid` decimal(12,2) DEFAULT '0.00',
  `interest_paid` decimal(12,2) DEFAULT '0.00',
  `penalty_paid` decimal(12,2) DEFAULT '0.00',
  `payment_date` date NOT NULL,
  `reference_no` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `loan_schedule`
--

CREATE TABLE `loan_schedule` (
  `schedule_id` int NOT NULL,
  `loan_id` int NOT NULL,
  `due_date` date NOT NULL,
  `principal_due` decimal(12,2) NOT NULL,
  `interest_due` decimal(12,2) NOT NULL DEFAULT '0.00',
  `penalty_due` decimal(12,2) NOT NULL DEFAULT '0.00',
  `total_due` decimal(12,2) NOT NULL,
  `status` enum('pending','ongoing','paid','overdue') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `loan_types`
--

CREATE TABLE `loan_types` (
  `loan_type_id` int NOT NULL,
  `loan_type_name` varchar(100) NOT NULL,
  `interest_rate` decimal(5,2) NOT NULL,
  `term_value` int NOT NULL,
  `term_unit` enum('days','weeks','months') NOT NULL DEFAULT 'months',
  `payment_frequency` enum('daily','weekly','monthly') NOT NULL DEFAULT 'monthly',
  `require_comaker` tinyint(1) NOT NULL DEFAULT '0',
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `loan_types`
--

INSERT INTO `loan_types` (`loan_type_id`, `loan_type_name`, `interest_rate`, `term_value`, `term_unit`, `payment_frequency`, `require_comaker`, `status`, `created_at`) VALUES
(21, 'Personal Loan', 1.50, 12, 'months', 'monthly', 1, 'active', '2026-02-25 07:58:02'),
(22, 'Home Loan', 1.20, 240, 'months', 'monthly', 0, 'active', '2026-02-25 07:58:02'),
(23, 'Character Loan', 3.00, 6, 'months', 'monthly', 0, 'active', '2026-02-25 07:58:02'),
(24, 'Emergency Loan', 3.00, 6, 'months', 'monthly', 0, 'active', '2026-02-25 07:58:02'),
(25, 'Salary Loan', 1.80, 12, 'months', 'monthly', 1, 'active', '2026-02-25 07:58:02');

-- --------------------------------------------------------

--
-- Table structure for table `savings_withdrawal_requests`
--

CREATE TABLE `savings_withdrawal_requests` (
  `request_id` int NOT NULL,
  `account_id` int NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `approved_by` int DEFAULT NULL,
  `date_requested` datetime DEFAULT CURRENT_TIMESTAMP,
  `date_approved` datetime DEFAULT NULL,
  `reference_no` varchar(50) DEFAULT NULL,
  `remarks` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `system_settings`
--

CREATE TABLE `system_settings` (
  `setting_key` varchar(100) NOT NULL,
  `setting_value` varchar(255) NOT NULL,
  `description` text,
  `status` enum('active','inactive') DEFAULT 'active',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `system_settings`
--

INSERT INTO `system_settings` (`setting_key`, `setting_value`, `description`, `status`, `updated_at`) VALUES
('allow_associate_loan', '0', 'Allow associate loan', 'active', '2026-02-16 09:47:50'),
('capital_max_limit', '0', NULL, 'active', '2026-02-20 06:12:28'),
('capital_min_required', '300', NULL, 'active', '2026-02-24 00:12:06'),
('capital_share_interest', '0.78', NULL, 'active', '2026-02-26 00:51:02'),
('capital_share_interest_frequency', 'monthly', NULL, 'active', '2026-03-16 00:37:16'),
('loan_doc_stamp_fee', '0.75', 'Document stamp fee for loans', 'active', '2026-02-27 00:12:10'),
('loan_grace_period_days', '3', 'Days before penalty starts', 'active', '2026-02-21 00:29:11'),
('loan_insurance_fee', '1.10', 'Insurance fee for loans', 'active', '2026-02-24 03:29:06'),
('loan_penalty_frequency', 'monthly', 'daily, weekly, monthly', 'active', '2026-02-21 00:29:11'),
('loan_penalty_type', 'fixed', 'percent or fixed', 'active', '2026-02-23 01:30:53'),
('loan_penalty_value', '21', 'Penalty per overdue period', 'active', '2026-02-27 22:48:32'),
('loan_processing_fee_type', 'percent', 'percent or fixed', 'active', '2026-02-24 02:24:03'),
('loan_processing_fee_value', '2', 'Processing fee value', 'active', '2026-02-27 00:11:54'),
('min_capital_required', '5000', 'Minimum capital required', 'active', '2026-02-22 18:09:31'),
('min_membership_months', '0', 'Minimum membership duration', 'active', '2026-02-22 01:28:21'),
('min_savings_required', '1000', 'Minimum savings required', 'active', '2026-02-23 03:06:04'),
('monthly_savings', '250', 'Mandatory monthly savings contribution', 'active', '2026-02-24 05:09:24'),
('monthly_share_capital', '250', 'Mandatory monthly share capital contribution', 'active', '2026-02-24 05:17:07'),
('require_comaker', '0', 'Require co-maker', 'active', '2026-03-15 15:37:26'),
('savings_interest_frequency', 'monthly', NULL, 'active', '2026-03-15 15:37:26'),
('savings_interest_rate', '0.78', NULL, 'active', '2026-02-24 05:16:18'),
('savings_min_balance', '250', NULL, 'active', '2026-02-24 05:04:35'),
('savings_withdrawal_limit', '3000', NULL, 'active', '2026-03-04 17:50:50');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_beginning_cash`
--

CREATE TABLE `tbl_beginning_cash` (
  `id` int NOT NULL,
  `amount` decimal(12,2) DEFAULT NULL,
  `cash_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `tbl_beginning_cash`
--

INSERT INTO `tbl_beginning_cash` (`id`, `amount`, `cash_date`) VALUES
(1, 2000.00, '2026-01-26');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_cart`
--

CREATE TABLE `tbl_cart` (
  `cart_id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `product_id` int DEFAULT NULL,
  `quantity_order` int DEFAULT NULL,
  `discount_percent` int DEFAULT NULL,
  `discount` decimal(12,2) DEFAULT '0.00',
  `type` tinyint DEFAULT '0',
  `sprice` decimal(12,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_cart2`
--

CREATE TABLE `tbl_cart2` (
  `cart_id` int NOT NULL,
  `product_id` int NOT NULL,
  `quantity_order` int NOT NULL,
  `user_id` int NOT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `discount` int NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `tbl_cart2`
--

INSERT INTO `tbl_cart2` (`cart_id`, `product_id`, `quantity_order`, `user_id`, `price`, `discount`) VALUES
(111, 19, 1, 55, 8.00, 0);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_customer`
--

CREATE TABLE `tbl_customer` (
  `cust_id` int NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `address` text,
  `contact` varchar(100) DEFAULT NULL,
  `field_status` tinyint DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_damage`
--

CREATE TABLE `tbl_damage` (
  `damage_id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `product_id` int DEFAULT NULL,
  `date_damage` datetime DEFAULT CURRENT_TIMESTAMP,
  `quantity_damage` int DEFAULT NULL,
  `notes` text,
  `field_status` tinyint DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_deposits`
--

CREATE TABLE `tbl_deposits` (
  `deposit_id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `date_added` datetime DEFAULT CURRENT_TIMESTAMP,
  `amount` decimal(12,2) DEFAULT NULL,
  `balance` decimal(12,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_expences`
--

CREATE TABLE `tbl_expences` (
  `expences_id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `date_expence` datetime DEFAULT CURRENT_TIMESTAMP,
  `description` text,
  `expence_amount` decimal(12,2) DEFAULT NULL,
  `approve_by` varchar(255) DEFAULT NULL,
  `notes` text,
  `field_status` tinyint DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `tbl_expences`
--

INSERT INTO `tbl_expences` (`expences_id`, `user_id`, `date_expence`, `description`, `expence_amount`, `approve_by`, `notes`, `field_status`) VALUES
(10, 8, '2026-03-09 00:00:00', 'Test1', 50.00, 'admin', '', 0);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_history`
--

CREATE TABLE `tbl_history` (
  `history_id` int NOT NULL,
  `date_history` datetime DEFAULT CURRENT_TIMESTAMP,
  `details` text,
  `history_type` int DEFAULT NULL,
  `field_status` tinyint DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `tbl_history`
--

INSERT INTO `tbl_history` (`history_id`, `date_history`, `details`, `history_type`, `field_status`) VALUES
(1446, '2026-03-18 13:25:06', '{\"user_id\":8}', 26, 0);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_loan_fund`
--

CREATE TABLE `tbl_loan_fund` (
  `fund_id` int NOT NULL,
  `fund_name` varchar(255) NOT NULL,
  `starting_balance` decimal(12,2) NOT NULL,
  `current_balance` decimal(12,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_members`
--

CREATE TABLE `tbl_members` (
  `member_id` int NOT NULL,
  `user_id` int NOT NULL,
  `cust_id` int DEFAULT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `gender` enum('Male','Female','Other') DEFAULT NULL,
  `birthdate` date DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `address` text,
  `membership_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `type` enum('regular','associate') NOT NULL,
  `tin` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_menu`
--

CREATE TABLE `tbl_menu` (
  `menu_id` int NOT NULL,
  `cat_id` int DEFAULT NULL,
  `menu_name` varchar(255) DEFAULT NULL,
  `quantity` decimal(10,2) DEFAULT '0.00',
  `price` decimal(10,2) DEFAULT NULL,
  `counter` int DEFAULT NULL,
  `image_link` varchar(255) DEFAULT NULL,
  `status` tinyint DEFAULT '1',
  `product_code` int DEFAULT NULL,
  `unit` varchar(50) DEFAULT NULL,
  `is_track` tinyint DEFAULT '0',
  `available` tinyint DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_payments`
--

CREATE TABLE `tbl_payments` (
  `payment_id` int NOT NULL,
  `cr_no` int DEFAULT NULL,
  `sales_no` varchar(50) DEFAULT NULL,
  `added_by` int DEFAULT NULL,
  `amount_paid` decimal(12,2) DEFAULT NULL,
  `date_payment` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_products`
--

CREATE TABLE `tbl_products` (
  `product_id` int NOT NULL,
  `cat_id` int DEFAULT '1',
  `product_code` varchar(20) NOT NULL,
  `image` text,
  `product_name` varchar(255) DEFAULT NULL,
  `quantity` int NOT NULL,
  `selling_price` decimal(12,2) DEFAULT NULL,
  `supplier_price` decimal(12,2) DEFAULT NULL,
  `critical_qty` int DEFAULT NULL,
  `unit` varchar(50) DEFAULT NULL,
  `field_status` tinyint DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_product_history`
--

CREATE TABLE `tbl_product_history` (
  `tph_id` int NOT NULL,
  `details` text,
  `details_type` int DEFAULT NULL,
  `product_id` int NOT NULL,
  `hist_date` datetime DEFAULT CURRENT_TIMESTAMP,
  `type` int DEFAULT NULL,
  `qty` int DEFAULT NULL,
  `balance` int DEFAULT NULL,
  `field_status` tinyint DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_receivings`
--

CREATE TABLE `tbl_receivings` (
  `receiving_id` int NOT NULL,
  `receiving_no` varchar(50) DEFAULT NULL,
  `product_id` int DEFAULT NULL,
  `supplier_id` int DEFAULT NULL,
  `user_id` int DEFAULT NULL,
  `receiving_quantity` int DEFAULT NULL,
  `date_received` datetime DEFAULT CURRENT_TIMESTAMP,
  `receiving_price` decimal(12,2) DEFAULT NULL,
  `discount` decimal(12,2) DEFAULT NULL,
  `sub_total` decimal(12,2) DEFAULT NULL,
  `total_amount` decimal(12,2) DEFAULT NULL,
  `field_status` tinyint DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_sales`
--

CREATE TABLE `tbl_sales` (
  `sales_id` int NOT NULL,
  `sales_no` varchar(50) DEFAULT NULL,
  `user_id` int DEFAULT NULL,
  `product_id` int DEFAULT NULL,
  `order_price` decimal(12,2) DEFAULT NULL,
  `quantity_order` decimal(12,2) DEFAULT NULL,
  `subtotal` decimal(12,2) DEFAULT NULL,
  `other_amount` decimal(12,2) DEFAULT '0.00',
  `discount_percent` int DEFAULT NULL,
  `discount` decimal(12,2) DEFAULT '0.00',
  `total_amount` decimal(12,2) DEFAULT NULL,
  `balance` decimal(12,2) DEFAULT '0.00',
  `sales_date` datetime DEFAULT CURRENT_TIMESTAMP,
  `cust_id` int DEFAULT NULL,
  `tax_percent` decimal(5,2) DEFAULT NULL,
  `amount_paid` decimal(12,2) DEFAULT NULL,
  `register` tinyint DEFAULT '0',
  `sales_status` tinyint DEFAULT '1',
  `field_status` tinyint DEFAULT '0',
  `sales_type` tinyint DEFAULT '0',
  `po_no` varchar(100) DEFAULT NULL,
  `delivery_address` text,
  `salesman` varchar(255) DEFAULT NULL,
  `check_no` varchar(100) DEFAULT NULL,
  `check_no2` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_settings`
--

CREATE TABLE `tbl_settings` (
  `settings_id` int NOT NULL,
  `tax` decimal(10,2) DEFAULT NULL,
  `store_id` int DEFAULT NULL,
  `store_name` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `tbl_settings`
--

INSERT INTO `tbl_settings` (`settings_id`, `tax`, `store_id`, `store_name`) VALUES
(1, 12.00, 1, 'lourdes');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_supplier`
--

CREATE TABLE `tbl_supplier` (
  `supplier_id` int NOT NULL,
  `supplier_name` varchar(255) DEFAULT NULL,
  `supplier_contact` varchar(100) DEFAULT NULL,
  `supplier_address` text,
  `field_status` tinyint DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `tbl_supplier`
--

INSERT INTO `tbl_supplier` (`supplier_id`, `supplier_name`, `supplier_contact`, `supplier_address`, `field_status`, `created_at`) VALUES
(1, 'Default', '0992255463', 'Opol', 0, '2026-01-25 00:29:40');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_units`
--

CREATE TABLE `tbl_units` (
  `unit_id` int NOT NULL,
  `unit` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_users`
--

CREATE TABLE `tbl_users` (
  `user_id` int NOT NULL,
  `username` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `usertype` int DEFAULT NULL,
  `fullname` varchar(255) DEFAULT NULL,
  `field_status` tinyint DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `tbl_users`
--

INSERT INTO `tbl_users` (`user_id`, `username`, `password`, `usertype`, `fullname`, `field_status`, `created_at`) VALUES
(8, 'Don', '$2y$10$OkSZm0SVzBZPseBM/VeQl.hDeeqewMGQjDsjlymn7h6CVWwl1qJaO', 1, 'Admin', 0, '2026-01-24 09:21:11');

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `transaction_id` int NOT NULL,
  `account_id` int NOT NULL,
  `transaction_type_id` int NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `reference_no` varchar(100) DEFAULT NULL,
  `remarks` varchar(255) DEFAULT NULL,
  `transaction_date` datetime DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `status` enum('active','voided') DEFAULT 'active',
  `voided_at` datetime DEFAULT NULL,
  `voided_by` int DEFAULT NULL,
  `void_reason` text,
  `reversed_transaction_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transaction_types`
--

CREATE TABLE `transaction_types` (
  `transaction_type_id` int NOT NULL,
  `type_name` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `transaction_types`
--

INSERT INTO `transaction_types` (`transaction_type_id`, `type_name`) VALUES
(6, 'cancelled loan'),
(3, 'capital_share'),
(1, 'deposit'),
(5, 'loan_payment'),
(4, 'loan_release'),
(2, 'withdrawal');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `accounts`
--
ALTER TABLE `accounts`
  ADD PRIMARY KEY (`account_id`),
  ADD UNIQUE KEY `account_number` (`account_number`),
  ADD KEY `member_id` (`member_id`),
  ADD KEY `account_type_id` (`account_type_id`);

--
-- Indexes for table `account_types`
--
ALTER TABLE `account_types`
  ADD PRIMARY KEY (`account_type_id`),
  ADD UNIQUE KEY `type_name` (`type_name`);

--
-- Indexes for table `email_logs`
--
ALTER TABLE `email_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_email_logs_loan_id` (`loan_id`),
  ADD KEY `idx_email_logs_sent_date` (`sent_date`),
  ADD KEY `idx_email_logs_status` (`status`);

--
-- Indexes for table `loans`
--
ALTER TABLE `loans`
  ADD PRIMARY KEY (`loan_id`),
  ADD KEY `fk_loans_account` (`account_id`),
  ADD KEY `fk_loans_type` (`loan_type_id`),
  ADD KEY `fk_loans_fund` (`fund_id`);

--
-- Indexes for table `loan_comaker`
--
ALTER TABLE `loan_comaker`
  ADD PRIMARY KEY (`comaker_id`),
  ADD KEY `fk_comaker_loan` (`loan_id`),
  ADD KEY `fk_comaker_member` (`comaker_member_id`);

--
-- Indexes for table `loan_payments`
--
ALTER TABLE `loan_payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `fk_payment_loan` (`loan_id`),
  ADD KEY `fk_payment_schedule` (`schedule_id`),
  ADD KEY `fk_payment_account` (`account_id`);

--
-- Indexes for table `loan_schedule`
--
ALTER TABLE `loan_schedule`
  ADD PRIMARY KEY (`schedule_id`),
  ADD KEY `fk_schedule_loan` (`loan_id`);

--
-- Indexes for table `loan_types`
--
ALTER TABLE `loan_types`
  ADD PRIMARY KEY (`loan_type_id`);

--
-- Indexes for table `savings_withdrawal_requests`
--
ALTER TABLE `savings_withdrawal_requests`
  ADD PRIMARY KEY (`request_id`),
  ADD KEY `fk_withdrawal_account` (`account_id`),
  ADD KEY `fk_withdrawal_approved_by` (`approved_by`);

--
-- Indexes for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`setting_key`);

--
-- Indexes for table `tbl_beginning_cash`
--
ALTER TABLE `tbl_beginning_cash`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_cart`
--
ALTER TABLE `tbl_cart`
  ADD PRIMARY KEY (`cart_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `tbl_cart2`
--
ALTER TABLE `tbl_cart2`
  ADD PRIMARY KEY (`cart_id`);

--
-- Indexes for table `tbl_customer`
--
ALTER TABLE `tbl_customer`
  ADD PRIMARY KEY (`cust_id`);

--
-- Indexes for table `tbl_damage`
--
ALTER TABLE `tbl_damage`
  ADD PRIMARY KEY (`damage_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `tbl_deposits`
--
ALTER TABLE `tbl_deposits`
  ADD PRIMARY KEY (`deposit_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `tbl_expences`
--
ALTER TABLE `tbl_expences`
  ADD PRIMARY KEY (`expences_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `tbl_history`
--
ALTER TABLE `tbl_history`
  ADD PRIMARY KEY (`history_id`);

--
-- Indexes for table `tbl_loan_fund`
--
ALTER TABLE `tbl_loan_fund`
  ADD PRIMARY KEY (`fund_id`);

--
-- Indexes for table `tbl_members`
--
ALTER TABLE `tbl_members`
  ADD PRIMARY KEY (`member_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `cust_id` (`cust_id`),
  ADD KEY `idx_tin` (`tin`);

--
-- Indexes for table `tbl_menu`
--
ALTER TABLE `tbl_menu`
  ADD PRIMARY KEY (`menu_id`),
  ADD UNIQUE KEY `menu_id` (`menu_id`);

--
-- Indexes for table `tbl_payments`
--
ALTER TABLE `tbl_payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `added_by` (`added_by`);

--
-- Indexes for table `tbl_products`
--
ALTER TABLE `tbl_products`
  ADD PRIMARY KEY (`product_id`),
  ADD UNIQUE KEY `product_code` (`product_code`);

--
-- Indexes for table `tbl_product_history`
--
ALTER TABLE `tbl_product_history`
  ADD PRIMARY KEY (`tph_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `tbl_receivings`
--
ALTER TABLE `tbl_receivings`
  ADD PRIMARY KEY (`receiving_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `supplier_id` (`supplier_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `tbl_sales`
--
ALTER TABLE `tbl_sales`
  ADD PRIMARY KEY (`sales_id`),
  ADD KEY `cust_id` (`cust_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `tbl_settings`
--
ALTER TABLE `tbl_settings`
  ADD PRIMARY KEY (`settings_id`);

--
-- Indexes for table `tbl_supplier`
--
ALTER TABLE `tbl_supplier`
  ADD PRIMARY KEY (`supplier_id`);

--
-- Indexes for table `tbl_units`
--
ALTER TABLE `tbl_units`
  ADD PRIMARY KEY (`unit_id`);

--
-- Indexes for table `tbl_users`
--
ALTER TABLE `tbl_users`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`transaction_id`),
  ADD KEY `account_id` (`account_id`),
  ADD KEY `transaction_type_id` (`transaction_type_id`);

--
-- Indexes for table `transaction_types`
--
ALTER TABLE `transaction_types`
  ADD PRIMARY KEY (`transaction_type_id`),
  ADD UNIQUE KEY `type_name` (`type_name`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `accounts`
--
ALTER TABLE `accounts`
  MODIFY `account_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;

--
-- AUTO_INCREMENT for table `account_types`
--
ALTER TABLE `account_types`
  MODIFY `account_type_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `email_logs`
--
ALTER TABLE `email_logs`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT for table `loans`
--
ALTER TABLE `loans`
  MODIFY `loan_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=171;

--
-- AUTO_INCREMENT for table `loan_comaker`
--
ALTER TABLE `loan_comaker`
  MODIFY `comaker_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `loan_payments`
--
ALTER TABLE `loan_payments`
  MODIFY `payment_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=151;

--
-- AUTO_INCREMENT for table `loan_schedule`
--
ALTER TABLE `loan_schedule`
  MODIFY `schedule_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=844;

--
-- AUTO_INCREMENT for table `loan_types`
--
ALTER TABLE `loan_types`
  MODIFY `loan_type_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `savings_withdrawal_requests`
--
ALTER TABLE `savings_withdrawal_requests`
  MODIFY `request_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=67;

--
-- AUTO_INCREMENT for table `tbl_beginning_cash`
--
ALTER TABLE `tbl_beginning_cash`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tbl_cart`
--
ALTER TABLE `tbl_cart`
  MODIFY `cart_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=318;

--
-- AUTO_INCREMENT for table `tbl_cart2`
--
ALTER TABLE `tbl_cart2`
  MODIFY `cart_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=121;

--
-- AUTO_INCREMENT for table `tbl_customer`
--
ALTER TABLE `tbl_customer`
  MODIFY `cust_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=70;

--
-- AUTO_INCREMENT for table `tbl_damage`
--
ALTER TABLE `tbl_damage`
  MODIFY `damage_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tbl_deposits`
--
ALTER TABLE `tbl_deposits`
  MODIFY `deposit_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tbl_expences`
--
ALTER TABLE `tbl_expences`
  MODIFY `expences_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `tbl_history`
--
ALTER TABLE `tbl_history`
  MODIFY `history_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1447;

--
-- AUTO_INCREMENT for table `tbl_loan_fund`
--
ALTER TABLE `tbl_loan_fund`
  MODIFY `fund_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `tbl_members`
--
ALTER TABLE `tbl_members`
  MODIFY `member_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;

--
-- AUTO_INCREMENT for table `tbl_menu`
--
ALTER TABLE `tbl_menu`
  MODIFY `menu_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_payments`
--
ALTER TABLE `tbl_payments`
  MODIFY `payment_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=78;

--
-- AUTO_INCREMENT for table `tbl_products`
--
ALTER TABLE `tbl_products`
  MODIFY `product_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `tbl_product_history`
--
ALTER TABLE `tbl_product_history`
  MODIFY `tph_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=356;

--
-- AUTO_INCREMENT for table `tbl_receivings`
--
ALTER TABLE `tbl_receivings`
  MODIFY `receiving_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT for table `tbl_sales`
--
ALTER TABLE `tbl_sales`
  MODIFY `sales_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=231;

--
-- AUTO_INCREMENT for table `tbl_settings`
--
ALTER TABLE `tbl_settings`
  MODIFY `settings_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tbl_supplier`
--
ALTER TABLE `tbl_supplier`
  MODIFY `supplier_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `tbl_units`
--
ALTER TABLE `tbl_units`
  MODIFY `unit_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_users`
--
ALTER TABLE `tbl_users`
  MODIFY `user_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=82;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `transaction_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=434;

--
-- AUTO_INCREMENT for table `transaction_types`
--
ALTER TABLE `transaction_types`
  MODIFY `transaction_type_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `accounts`
--
ALTER TABLE `accounts`
  ADD CONSTRAINT `accounts_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `tbl_members` (`member_id`),
  ADD CONSTRAINT `accounts_ibfk_2` FOREIGN KEY (`account_type_id`) REFERENCES `account_types` (`account_type_id`);

--
-- Constraints for table `email_logs`
--
ALTER TABLE `email_logs`
  ADD CONSTRAINT `email_logs_ibfk_1` FOREIGN KEY (`loan_id`) REFERENCES `loans` (`loan_id`) ON DELETE CASCADE;

--
-- Constraints for table `loans`
--
ALTER TABLE `loans`
  ADD CONSTRAINT `fk_loans_account` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`account_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_loans_fund` FOREIGN KEY (`fund_id`) REFERENCES `tbl_loan_fund` (`fund_id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_loans_type` FOREIGN KEY (`loan_type_id`) REFERENCES `loan_types` (`loan_type_id`) ON DELETE CASCADE;

--
-- Constraints for table `loan_comaker`
--
ALTER TABLE `loan_comaker`
  ADD CONSTRAINT `fk_comaker_loan` FOREIGN KEY (`loan_id`) REFERENCES `loans` (`loan_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_comaker_member` FOREIGN KEY (`comaker_member_id`) REFERENCES `tbl_members` (`member_id`) ON DELETE CASCADE;

--
-- Constraints for table `loan_payments`
--
ALTER TABLE `loan_payments`
  ADD CONSTRAINT `fk_payment_account` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`account_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_payment_loan` FOREIGN KEY (`loan_id`) REFERENCES `loans` (`loan_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_payment_schedule` FOREIGN KEY (`schedule_id`) REFERENCES `loan_schedule` (`schedule_id`) ON DELETE CASCADE;

--
-- Constraints for table `loan_schedule`
--
ALTER TABLE `loan_schedule`
  ADD CONSTRAINT `fk_schedule_loan` FOREIGN KEY (`loan_id`) REFERENCES `loans` (`loan_id`) ON DELETE CASCADE;

--
-- Constraints for table `savings_withdrawal_requests`
--
ALTER TABLE `savings_withdrawal_requests`
  ADD CONSTRAINT `fk_withdrawal_account` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`account_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_withdrawal_approved_by` FOREIGN KEY (`approved_by`) REFERENCES `tbl_users` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `tbl_cart`
--
ALTER TABLE `tbl_cart`
  ADD CONSTRAINT `tbl_cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `tbl_users` (`user_id`),
  ADD CONSTRAINT `tbl_cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `tbl_products` (`product_id`);

--
-- Constraints for table `tbl_damage`
--
ALTER TABLE `tbl_damage`
  ADD CONSTRAINT `tbl_damage_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `tbl_products` (`product_id`),
  ADD CONSTRAINT `tbl_damage_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `tbl_users` (`user_id`);

--
-- Constraints for table `tbl_deposits`
--
ALTER TABLE `tbl_deposits`
  ADD CONSTRAINT `tbl_deposits_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `tbl_users` (`user_id`);

--
-- Constraints for table `tbl_expences`
--
ALTER TABLE `tbl_expences`
  ADD CONSTRAINT `tbl_expences_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `tbl_users` (`user_id`);

--
-- Constraints for table `tbl_members`
--
ALTER TABLE `tbl_members`
  ADD CONSTRAINT `tbl_members_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `tbl_users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tbl_members_ibfk_2` FOREIGN KEY (`cust_id`) REFERENCES `tbl_customer` (`cust_id`) ON DELETE SET NULL;

--
-- Constraints for table `tbl_payments`
--
ALTER TABLE `tbl_payments`
  ADD CONSTRAINT `tbl_payments_ibfk_1` FOREIGN KEY (`added_by`) REFERENCES `tbl_users` (`user_id`);

--
-- Constraints for table `tbl_product_history`
--
ALTER TABLE `tbl_product_history`
  ADD CONSTRAINT `tbl_product_history_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `tbl_products` (`product_id`);

--
-- Constraints for table `tbl_receivings`
--
ALTER TABLE `tbl_receivings`
  ADD CONSTRAINT `tbl_receivings_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `tbl_products` (`product_id`),
  ADD CONSTRAINT `tbl_receivings_ibfk_2` FOREIGN KEY (`supplier_id`) REFERENCES `tbl_supplier` (`supplier_id`),
  ADD CONSTRAINT `tbl_receivings_ibfk_3` FOREIGN KEY (`user_id`) REFERENCES `tbl_users` (`user_id`);

--
-- Constraints for table `tbl_sales`
--
ALTER TABLE `tbl_sales`
  ADD CONSTRAINT `tbl_sales_ibfk_1` FOREIGN KEY (`cust_id`) REFERENCES `tbl_customer` (`cust_id`),
  ADD CONSTRAINT `tbl_sales_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `tbl_products` (`product_id`),
  ADD CONSTRAINT `tbl_sales_ibfk_3` FOREIGN KEY (`user_id`) REFERENCES `tbl_users` (`user_id`);

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`account_id`),
  ADD CONSTRAINT `transactions_ibfk_2` FOREIGN KEY (`transaction_type_id`) REFERENCES `transaction_types` (`transaction_type_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
