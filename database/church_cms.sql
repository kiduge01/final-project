-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Apr 15, 2026 at 07:57 PM
-- Server version: 9.1.0
-- PHP Version: 8.4.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `church_cms`
--

-- --------------------------------------------------------

--
-- Table structure for table `approval_logs`
--

DROP TABLE IF EXISTS `approval_logs`;
CREATE TABLE IF NOT EXISTS `approval_logs` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `entity_type` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'e.g. procurement, budget',
  `entity_id` bigint UNSIGNED NOT NULL,
  `level_no` tinyint UNSIGNED NOT NULL DEFAULT '1',
  `action` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'submitted, approved, rejected',
  `actor_id` bigint UNSIGNED DEFAULT NULL,
  `notes` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `acted_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_al_entity` (`entity_type`,`entity_id`),
  KEY `idx_al_actor` (`actor_id`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `approval_logs`
--

INSERT INTO `approval_logs` (`id`, `entity_type`, `entity_id`, `level_no`, `action`, `actor_id`, `notes`, `acted_at`) VALUES
(1, 'budget', 1, 1, 'approved', 1, NULL, '2026-04-14 17:04:10'),
(2, 'procurement', 2, 1, 'submitted', 1, 'Request created', '2026-04-14 17:07:36'),
(3, 'procurement', 2, 1, 'approved', 1, 'SAWA', '2026-04-14 17:08:33'),
(4, 'procurement', 2, 1, 'approved', 1, 'Marked as purchased', '2026-04-14 17:08:37'),
(5, 'procurement', 2, 1, 'approved', 1, 'Completed', '2026-04-14 17:08:49'),
(6, 'procurement', 1, 1, 'approved', 1, 'Marked as purchased', '2026-04-15 06:30:46'),
(7, 'budget', 2, 1, 'approved', 2, NULL, '2026-04-15 12:13:55'),
(8, 'budget', 3, 1, 'approved', 2, NULL, '2026-04-15 12:14:44'),
(9, 'procurement', 3, 1, 'submitted', 2, 'Request created', '2026-04-15 12:21:33'),
(10, 'procurement', 3, 1, 'rejected', 1, 'rekebisha bei', '2026-04-15 12:23:06'),
(11, 'procurement', 3, 1, 'rejected', 2, 'Cancelled by user', '2026-04-15 12:24:02'),
(12, 'procurement', 4, 1, 'submitted', 2, 'Request created', '2026-04-15 12:24:32'),
(13, 'procurement', 4, 1, 'approved', 2, '', '2026-04-15 12:26:21'),
(14, 'procurement', 5, 1, 'submitted', 1, 'Request created', '2026-04-15 13:21:21'),
(15, 'procurement', 5, 1, 'approved', 1, '', '2026-04-15 13:22:48'),
(16, 'procurement', 5, 2, 'approved', 2, '', '2026-04-15 13:23:11'),
(17, 'procurement', 5, 1, 'approved', 2, 'Marked as purchased', '2026-04-15 13:54:09'),
(18, 'procurement', 4, 1, 'approved', 2, 'Marked as purchased', '2026-04-15 14:04:22'),
(19, 'procurement', 6, 1, 'submitted', 1, 'Request created', '2026-04-15 15:30:28'),
(20, 'procurement', 6, 1, 'approved', 1, 'nimekubali', '2026-04-15 15:30:40'),
(21, 'procurement', 6, 2, 'approved', 2, '', '2026-04-15 15:31:18'),
(22, 'procurement', 6, 1, 'approved', 2, 'Marked as purchased', '2026-04-15 15:31:25'),
(23, 'budget', 4, 1, 'approved', 1, NULL, '2026-04-15 19:41:28'),
(24, 'budget', 4, 2, 'approved', 1, NULL, '2026-04-15 19:42:33'),
(25, 'procurement', 7, 1, 'submitted', 1, 'Request created', '2026-04-15 19:44:20'),
(26, 'procurement', 7, 1, 'approved', 1, 'done', '2026-04-15 19:45:31'),
(27, 'procurement', 7, 2, 'approved', 1, '', '2026-04-15 19:45:36'),
(28, 'procurement', 7, 1, 'approved', 1, 'Marked as purchased', '2026-04-15 19:46:02');

-- --------------------------------------------------------

--
-- Table structure for table `approval_workflows`
--

DROP TABLE IF EXISTS `approval_workflows`;
CREATE TABLE IF NOT EXISTS `approval_workflows` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `workflow_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `level_no` tinyint UNSIGNED NOT NULL DEFAULT '1',
  `role_id` bigint UNSIGNED NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_workflow_type_level` (`workflow_type`,`level_no`),
  KEY `fk_aw_role` (`role_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `approval_workflows`
--

INSERT INTO `approval_workflows` (`id`, `workflow_type`, `level_no`, `role_id`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'finance_entry', 1, 2, 1, '2026-04-15 11:08:42', '2026-04-15 11:08:42'),
(2, 'budget', 1, 2, 1, '2026-04-15 11:08:42', '2026-04-15 11:08:42'),
(3, 'budget', 2, 7, 1, '2026-04-15 11:08:42', '2026-04-15 11:08:42'),
(5, 'procurement', 1, 1, 1, '2026-04-15 12:25:25', '2026-04-15 12:25:25'),
(6, 'procurement', 2, 2, 1, '2026-04-15 12:25:41', '2026-04-15 12:25:41');

-- --------------------------------------------------------

--
-- Table structure for table `assets`
--

DROP TABLE IF EXISTS `assets`;
CREATE TABLE IF NOT EXISTS `assets` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `asset_tag` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(180) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `purchase_date` date DEFAULT NULL,
  `purchase_value` decimal(14,2) DEFAULT NULL,
  `condition_status` enum('excellent','good','fair','poor','retired') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'good',
  `current_location` varchar(180) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `assigned_to_user_id` bigint UNSIGNED DEFAULT NULL,
  `assigned_event_id` bigint UNSIGNED DEFAULT NULL,
  `warranty_expiry` date DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `asset_tag` (`asset_tag`),
  KEY `fk_assets_user` (`assigned_to_user_id`),
  KEY `fk_assets_event` (`assigned_event_id`),
  KEY `idx_assets_category` (`category`),
  KEY `idx_assets_condition` (`condition_status`),
  KEY `idx_assets_location` (`current_location`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `assets`
--

INSERT INTO `assets` (`id`, `asset_tag`, `name`, `category`, `purchase_date`, `purchase_value`, `condition_status`, `current_location`, `assigned_to_user_id`, `assigned_event_id`, `warranty_expiry`, `is_active`, `notes`, `created_at`, `updated_at`) VALUES
(1, 'AST-001', 'Yamaha Mixer', 'Audio Equipment', '2024-02-10', 2400000.00, 'good', 'Media Room', NULL, NULL, NULL, 1, NULL, '2026-04-10 10:43:44', '2026-04-10 10:43:44'),
(2, 'AST-002', 'Plastic Chairs (set)', 'Furniture', '2023-05-17', 1200000.00, 'fair', 'Store 1', NULL, NULL, NULL, 1, NULL, '2026-04-10 10:43:44', '2026-04-10 10:43:44');

-- --------------------------------------------------------

--
-- Table structure for table `asset_assignments`
--

DROP TABLE IF EXISTS `asset_assignments`;
CREATE TABLE IF NOT EXISTS `asset_assignments` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `asset_id` bigint UNSIGNED NOT NULL,
  `assigned_type` enum('user','event','location','department') COLLATE utf8mb4_unicode_ci NOT NULL,
  `assigned_user_id` bigint UNSIGNED DEFAULT NULL,
  `assigned_event_id` bigint UNSIGNED DEFAULT NULL,
  `assigned_location` varchar(180) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `assigned_department_id` bigint UNSIGNED DEFAULT NULL,
  `assigned_from` datetime NOT NULL,
  `assigned_to` datetime DEFAULT NULL,
  `assigned_by` bigint UNSIGNED NOT NULL,
  `notes` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_asset_assignment_user` (`assigned_user_id`),
  KEY `fk_asset_assignment_event` (`assigned_event_id`),
  KEY `fk_asset_assignment_assigned_by` (`assigned_by`),
  KEY `idx_asset_assignments_asset_id` (`asset_id`),
  KEY `idx_asset_assignments_type` (`assigned_type`),
  KEY `idx_asset_assign_dept` (`assigned_department_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `asset_assignments`
--

INSERT INTO `asset_assignments` (`id`, `asset_id`, `assigned_type`, `assigned_user_id`, `assigned_event_id`, `assigned_location`, `assigned_department_id`, `assigned_from`, `assigned_to`, `assigned_by`, `notes`, `created_at`, `updated_at`) VALUES
(1, 1, 'event', NULL, NULL, NULL, NULL, '2026-03-30 15:30:00', '2026-04-15 18:14:45', 3, 'Assigned for Youth Outreach', '2026-04-10 10:43:44', '2026-04-15 15:14:45'),
(2, 2, 'department', NULL, NULL, NULL, 16, '2026-04-15 18:14:28', NULL, 1, '', '2026-04-15 15:14:28', '2026-04-15 15:14:28'),
(3, 1, 'department', NULL, NULL, NULL, 16, '2026-04-15 18:14:45', NULL, 1, '', '2026-04-15 15:14:45', '2026-04-15 15:14:45');

-- --------------------------------------------------------

--
-- Table structure for table `attendance_snapshots`
--

DROP TABLE IF EXISTS `attendance_snapshots`;
CREATE TABLE IF NOT EXISTS `attendance_snapshots` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `service_date` date NOT NULL,
  `service_name` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `service_type` enum('sunday_service','midweek','prayer','youth_service','special','other') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'sunday_service',
  `men_count` int UNSIGNED NOT NULL DEFAULT '0',
  `women_count` int UNSIGNED NOT NULL DEFAULT '0',
  `children_count` int UNSIGNED NOT NULL DEFAULT '0',
  `youth_count` int UNSIGNED NOT NULL DEFAULT '0',
  `guests_count` int UNSIGNED NOT NULL DEFAULT '0',
  `total_count` int UNSIGNED NOT NULL DEFAULT '0',
  `notes` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_attendance_snapshots_date` (`service_date`),
  KEY `idx_attendance_snapshots_type` (`service_type`),
  KEY `fk_attendance_snapshots_created_by` (`created_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

DROP TABLE IF EXISTS `audit_logs`;
CREATE TABLE IF NOT EXISTS `audit_logs` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `actor_user_id` bigint UNSIGNED DEFAULT NULL,
  `module_name` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `action_name` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `entity_type` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `entity_id` bigint UNSIGNED DEFAULT NULL,
  `change_summary` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `old_values` json DEFAULT NULL,
  `new_values` json DEFAULT NULL,
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_audit_logs_actor` (`actor_user_id`),
  KEY `idx_audit_module_action` (`module_name`,`action_name`),
  KEY `idx_audit_entity` (`entity_type`,`entity_id`),
  KEY `idx_audit_created_at` (`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=97 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`id`, `actor_user_id`, `module_name`, `action_name`, `entity_type`, `entity_id`, `change_summary`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 1, 'finance', 'create', 'finance_entries', 7, 'Recorded finance entry', NULL, '{\"amount\": \"6000000\", \"entry_no\": \"FIN-20260411-125\", \"member_id\": \"\", \"entry_date\": \"2026-04-05\", \"category_id\": \"2\", \"description\": \"Sadaka\", \"payment_method\": \"cash\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-11 15:03:02'),
(2, 1, 'finance', 'create', 'finance_entries', 8, 'Recorded finance entry', NULL, '{\"amount\": \"600000\", \"entry_no\": \"FIN-20260411-449\", \"member_id\": \"\", \"entry_date\": \"2026-04-01\", \"category_id\": \"3\", \"description\": \"Donation\", \"payment_method\": \"mobile_money\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-11 15:04:00'),
(4, 1, 'attendance', 'create', 'guests', 1, 'Registered guest: James Philip', NULL, '{\"phone\": \"+255764559664\", \"location\": \"Ilala\", \"last_name\": \"Philip\", \"first_name\": \"James\", \"visit_type\": \"first_time\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-13 12:39:35'),
(5, 1, 'attendance', 'create', 'guests', 2, 'Registered guest: James Philip', NULL, '{\"phone\": \"+255764559664\", \"location\": \"Ilala\", \"last_name\": \"Philip\", \"first_name\": \"James\", \"visit_type\": \"first_time\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-13 12:39:36'),
(6, 1, 'attendance', 'create', 'guests', 3, 'Registered guest: Daniel Salum', NULL, '{\"phone\": \"+255697235627\", \"location\": \"Ubungo\", \"last_name\": \"Salum\", \"first_name\": \"Daniel\", \"visit_type\": \"returning\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-13 12:45:43'),
(7, NULL, 'department', 'login_failed', 'department_head', NULL, 'Failed login attempt for: vijana@tagmsasani.com', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-13 13:36:06'),
(9, 1, 'finance', 'create', 'finance_entries', 9, 'Recorded finance entry', NULL, '{\"amount\": \"400000\", \"entry_no\": \"FIN-20260413-765\", \"member_id\": \"\", \"entry_date\": \"2026-04-12\", \"category_id\": \"6\", \"description\": \"Elimu ya Biblia na mafunzo\", \"payment_method\": \"mobile_money\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-13 13:42:51'),
(10, 1, 'finance', 'approved', 'finance_entries', 9, 'Finance entry approved', NULL, '{\"amount\": \"400000.00\", \"decision\": \"approved\", \"event_id\": 0}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-13 13:51:12'),
(11, 1, 'finance', 'approved', 'finance_entries', 9, 'Finance entry approved', NULL, '{\"amount\": \"400000.00\", \"decision\": \"approved\", \"event_id\": 0}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-13 13:51:43'),
(12, 1, 'events', 'create', 'events', 1, 'Created event from quick modal', NULL, '{\"date\": \"2026-04-12\", \"time\": \"18:20\", \"title\": \"Sunday\", \"budget\": \"\", \"location\": \"TCRIC\", \"send_sms\": 0, \"event_type\": \"service\", \"send_email\": 0, \"description\": \"Thanks Giving\", \"usher_on_duty\": \"Samwel\", \"pastor_on_duty\": \"Maheda\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-13 14:20:40'),
(13, 1, 'events', 'create', 'events', 2, 'Created event', NULL, '{\"date\": \"2026-04-19\", \"time\": \"07:00\", \"title\": \"Morning Service\", \"budget\": \"\", \"location\": \"TCRIC\", \"send_sms\": 0, \"event_type\": \"service\", \"send_email\": 0, \"description\": \"\", \"usher_on_duty\": \"Samwel\", \"pastor_on_duty\": \"Maheda\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-13 14:41:12'),
(14, 1, 'events', 'create', 'events', 3, 'Created event', NULL, '{\"date\": \"2026-04-12\", \"time\": \"10:30\", \"title\": \"Afternoon Service\", \"budget\": \"\", \"location\": \"TCRIC\", \"send_sms\": 0, \"event_type\": \"service\", \"send_email\": 0, \"description\": \"\", \"usher_on_duty\": \"Samwel\", \"pastor_on_duty\": \"Maheda Joseph\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-13 15:12:21'),
(17, 1, 'events', 'create', 'events', 8, 'Created event', NULL, '{\"date\": \"2026-04-16\", \"time\": \"00:01\", \"title\": \"sad\", \"budget\": \"\", \"location\": \"asda\", \"send_sms\": 1, \"event_type\": \"service\", \"send_email\": 1, \"description\": \"asda\", \"usher_on_duty\": \"adsa\", \"pastor_on_duty\": \"ads\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-14 07:01:59'),
(18, 1, 'events', 'create', 'events', 12, 'Created event', NULL, '{\"date\": \"2026-04-15\", \"time\": \"01:02\", \"title\": \"sadasd\", \"budget\": \"1000000\", \"location\": \"ada\", \"send_sms\": 0, \"event_type\": \"service\", \"send_email\": 0, \"description\": \"\", \"usher_on_duty\": \"asd\", \"pastor_on_duty\": \"sad\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-14 07:09:06'),
(19, 1, 'events', 'delete', 'events', 3, 'Event deleted', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-14 07:50:13'),
(20, 1, 'events', 'delete', 'events', 1, 'Event deleted', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-14 07:50:18'),
(21, 1, 'events', 'delete', 'events', 9, 'Event deleted', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-14 07:50:21'),
(22, 1, 'events', 'delete', 'events', 10, 'Event deleted', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-14 07:50:23'),
(23, 1, 'events', 'delete', 'events', 2, 'Event deleted', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-14 07:50:52'),
(24, 1, 'events', 'delete', 'events', 5, 'Event deleted', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-14 07:50:54'),
(25, 1, 'events', 'delete', 'events', 4, 'Event deleted', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-14 07:50:56'),
(26, 1, 'events', 'delete', 'events', 8, 'Event deleted', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-14 07:50:58'),
(27, 1, 'events', 'delete', 'events', 7, 'Event deleted', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-14 07:51:01'),
(28, 1, 'events', 'delete', 'events', 6, 'Event deleted', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-14 07:51:06'),
(29, 1, 'events', 'delete', 'events', 12, 'Event deleted', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-14 07:51:07'),
(30, 1, 'events', 'delete', 'events', 11, 'Event deleted', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-14 07:51:10'),
(31, 1, 'events', 'create', 'events', 13, 'Created event', NULL, '{\"date\": \"2026-04-18\", \"time\": \"00:59\", \"title\": \"SEMINA YA VIJANA\", \"budget\": \"100000\", \"location\": \"dar\", \"send_sms\": 1, \"event_type\": \"seminar\", \"send_email\": 1, \"description\": \"SAMPLE SMS FROM THEY\", \"usher_on_duty\": \"john\", \"pastor_on_duty\": \"nick\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-14 07:52:15'),
(32, 1, 'events', 'communicate', 'events', 13, 'Sent event communication', NULL, '{\"message\": \"JJJ\", \"send_sms\": 1, \"send_email\": 0}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-14 08:02:44'),
(33, 1, 'events', 'send_budget_to_finance', 'finance_entries', 10, 'Sent event budget to finance for approval', NULL, '{\"amount\": 100000, \"event_id\": 13, \"items_count\": 1}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-14 08:03:01'),
(34, 1, 'events', 'delete', 'events', 13, 'Event deleted', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-14 08:03:17'),
(35, 1, 'finance', 'approved', 'finance_entries', 10, 'Finance entry approved', NULL, '{\"amount\": \"100000.00\", \"decision\": \"approved\", \"event_id\": 0}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-14 08:03:33'),
(44, 1, 'settings', 'update_user', 'users', 2, 'User #2 updated', NULL, '{\"email\": \"finance@kanisa.local\", \"phone\": \"+255700000002\", \"role_id\": 2, \"password\": \"Admin@1234\", \"full_name\": \"Finance Officer\", \"is_active\": 1}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-14 13:43:30'),
(45, 2, 'events', 'create', 'events', 14, 'Created event', NULL, '{\"date\": \"2026-04-18\", \"time\": \"19:57\", \"title\": \"njj\", \"budget\": \"10000\", \"location\": \"dar es salaam\", \"send_sms\": 0, \"event_type\": \"service\", \"send_email\": 1, \"description\": \"sdfds\", \"usher_on_duty\": \"\", \"pastor_on_duty\": \"\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-14 13:57:55'),
(46, 2, 'events', 'send_budget_to_finance', 'finance_entries', 11, 'Sent event budget to finance for approval', NULL, '{\"amount\": 10000, \"event_id\": 14, \"items_count\": 1}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-14 14:09:42'),
(47, 1, 'finance', 'create_budget', 'department_budgets', 1, 'Created budget', NULL, '{\"event_id\": \"\", \"department\": \"Ujenzi\", \"category_id\": \"4\", \"description\": \"UJEZI WA KANISA \", \"fiscal_month\": \"2026-05\", \"planned_amount\": \"1000000\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-14 16:39:34'),
(48, 1, 'finance', 'budget_approved', 'department_budgets', 1, 'Budget approved', NULL, '{\"decision\": \"approved\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-14 17:04:10'),
(49, 1, 'procurement', 'create_request', 'purchase_requests', 2, 'PR PR-20260414-085 created', NULL, '{\"total\": 730000}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-14 17:07:36'),
(50, 1, 'procurement', 'request_approved', 'purchase_requests', 2, 'PR approved', NULL, '{\"decision\": \"approved\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-14 17:08:33'),
(51, 1, 'procurement', 'mark_purchased', 'purchase_requests', 2, 'PR marked purchased, expenses created', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-14 17:08:37'),
(52, 1, 'procurement', 'complete', 'purchase_requests', 2, 'PR completed', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-14 17:08:49'),
(53, 1, 'procurement', 'mark_purchased', 'purchase_requests', 1, 'PR marked purchased, expenses created', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-15 06:30:46'),
(54, 1, 'finance', 'budget_expense_added', 'budget_expenses', 4, 'Budget expense added', NULL, '{\"notes\": \"PESA YA USAFIRI ALIPEWA JOHN \", \"amount\": \"170000\", \"item_name\": \"usafiri\", \"expense_date\": \"2026-04-14\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-15 06:32:39'),
(55, 1, 'finance', 'budget_expense_added', 'budget_expenses', 5, 'Budget expense added', NULL, '{\"notes\": \"KAPEWA PETER\", \"amount\": \"100000\", \"item_name\": \"CHAKULA CHA MAFUNDI \", \"expense_date\": \"2026-04-15\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-15 06:33:05'),
(56, 1, 'finance', 'budget_closed', 'department_budgets', 1, 'Budget closed and posted to expenses', NULL, '{\"remaining\": 0, \"total_used\": 1000000, \"planned_amount\": 1000000, \"finance_entry_id\": 12}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-15 06:54:34'),
(57, 1, 'finance', 'create', 'finance_entries', 13, 'Recorded finance entry', NULL, '{\"amount\": \"2000000\", \"entry_no\": \"FIN-20260415-144\", \"member_id\": \"\", \"entry_date\": \"2026-04-04\", \"category_id\": \"2\", \"description\": \"hh\", \"payment_method\": \"mobile_money\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-15 06:56:47'),
(58, 1, 'finance', 'create', 'finance_entries', 14, 'Recorded finance entry', NULL, '{\"amount\": \"100000\", \"entry_no\": \"FIN-20260415-203\", \"member_id\": \"102\", \"entry_date\": \"2026-04-15\", \"category_id\": \"3\", \"description\": \"jjj\", \"payment_method\": \"cash\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-15 06:57:40'),
(59, 1, 'finance', 'create', 'finance_entries', 15, 'Recorded finance entry', NULL, '{\"amount\": \"100000\", \"entry_date\": \"2026-04-16\", \"category_id\": \"3\", \"description\": \"hh\", \"payment_method\": \"bank_transfer\", \"church_account_id\": \"3\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-15 08:12:38'),
(60, 1, 'finance', 'approved', 'finance_entries', 11, 'Finance entry approved', NULL, '{\"amount\": \"10000.00\", \"decision\": \"approved\", \"event_id\": 14}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-15 10:57:47'),
(61, 2, 'finance', 'create_budget', 'department_budgets', 2, 'Created budget', NULL, '{\"event_id\": \"\", \"department\": \"Ibada\", \"category_id\": \"5\", \"description\": \"UJEZI WA KANISA \", \"fiscal_month\": \"2026-12\", \"planned_amount\": \"50000\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-15 12:13:30'),
(62, 2, 'finance', 'budget_approved', 'department_budgets', 2, 'Budget approved', NULL, '{\"decision\": \"approved\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-15 12:13:55'),
(63, 2, 'finance', 'create_budget', 'department_budgets', 3, 'Created budget', NULL, '{\"event_id\": \"14\", \"department\": \"Huduma\", \"category_id\": \"4\", \"description\": \"UJEZI WA KANISA \", \"fiscal_month\": \"2026-11\", \"planned_amount\": \"10000\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-15 12:14:30'),
(64, 2, 'finance', 'budget_approved', 'department_budgets', 3, 'Budget approved', NULL, '{\"decision\": \"approved\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-15 12:14:44'),
(65, 2, 'procurement', 'create_request', 'purchase_requests', 3, 'PR PR-20260415-291 created', NULL, '{\"total\": 50000}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-15 12:21:33'),
(66, 1, 'procurement', 'request_rejected', 'purchase_requests', 3, 'PR rejected', NULL, '{\"decision\": \"rejected\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-15 12:23:06'),
(67, 2, 'procurement', 'cancel', 'purchase_requests', 3, 'PR cancelled', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-15 12:24:02'),
(68, 2, 'procurement', 'create_request', 'purchase_requests', 4, 'PR PR-20260415-470 created', NULL, '{\"total\": 39000}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-15 12:24:32'),
(69, 2, 'procurement', 'request_approved', 'purchase_requests', 4, 'PR approved', NULL, '{\"decision\": \"approved\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-15 12:26:21'),
(70, 1, 'procurement', 'create_request', 'purchase_requests', 5, 'PR PR-20260415-351 created', NULL, '{\"total\": 10000}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-15 13:21:21'),
(71, 1, 'procurement', 'request_approved', 'purchase_requests', 5, 'PR approved (L1)', NULL, '{\"level\": 1, \"decision\": \"approved\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-15 13:22:48'),
(72, 2, 'procurement', 'request_approved', 'purchase_requests', 5, 'PR approved (L2)', NULL, '{\"level\": 2, \"decision\": \"approved\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-15 13:23:11'),
(73, 2, 'procurement', 'mark_purchased', 'purchase_requests', 5, 'PR marked purchased, expenses created', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-15 13:54:09'),
(74, 2, 'procurement', 'mark_purchased', 'purchase_requests', 4, 'PR marked purchased, expenses created', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-15 14:04:22'),
(75, 1, 'assets', 'assign', 'asset_assignments', 0, 'Asset assigned', NULL, '{\"notes\": \"\", \"assigned_type\": \"department\", \"department_id\": \"16\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-15 15:14:28'),
(76, 1, 'assets', 'assign', 'asset_assignments', 0, 'Asset assigned', NULL, '{\"notes\": \"\", \"assigned_type\": \"department\", \"department_id\": \"16\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-15 15:14:45'),
(80, 1, 'events', 'delete', 'events', 14, 'Event deleted', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-15 15:29:51'),
(81, 1, 'procurement', 'create_request', 'purchase_requests', 6, 'PR PR-20260415-149 created', NULL, '{\"total\": 10000}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-15 15:30:28'),
(82, 1, 'procurement', 'request_approved', 'purchase_requests', 6, 'PR approved (L1)', NULL, '{\"level\": 1, \"decision\": \"approved\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-15 15:30:40'),
(83, 2, 'procurement', 'request_approved', 'purchase_requests', 6, 'PR approved (L2)', NULL, '{\"level\": 2, \"decision\": \"approved\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-15 15:31:18'),
(84, 2, 'procurement', 'mark_purchased', 'purchase_requests', 6, 'PR marked purchased, expenses created', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-15 15:31:25'),
(85, 2, 'finance', 'budget_closed', 'department_budgets', 2, 'Budget closed and posted to expenses', NULL, '{\"remaining\": 1000, \"total_used\": 49000, \"planned_amount\": 50000, \"finance_entry_id\": 16}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-15 15:32:04'),
(87, 2, 'finance', 'create_budget', 'department_budgets', 4, 'Created budget', NULL, '{\"event_id\": \"\", \"department\": \"Ujenzi\", \"category_id\": \"4\", \"description\": \"UJENZI WA MABWENI\", \"fiscal_month\": \"2026-05\", \"planned_amount\": \"10000000\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-15 19:37:48'),
(88, 1, 'settings', 'update_user', 'users', 3, 'User #3 updated', NULL, '{\"email\": \"secretary@kanisa.local\", \"phone\": \"+255700000003\", \"role_id\": 3, \"password\": \"Admin@1234\", \"full_name\": \"Church Secretary\", \"is_active\": 1}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-15 19:40:26'),
(89, 1, 'finance', 'budget_approved', 'department_budgets', 4, 'Budget approved (L1)', NULL, '{\"level\": 1, \"decision\": \"approved\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-15 19:41:28'),
(90, 1, 'finance', 'budget_approved', 'department_budgets', 4, 'Budget approved (L2)', NULL, '{\"level\": 2, \"decision\": \"approved\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-15 19:42:33'),
(91, 1, 'procurement', 'create_request', 'purchase_requests', 7, 'PR PR-20260415-465 created', NULL, '{\"total\": 6350000}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-15 19:44:20'),
(92, 1, 'procurement', 'request_approved', 'purchase_requests', 7, 'PR approved (L1)', NULL, '{\"level\": 1, \"decision\": \"approved\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-15 19:45:31'),
(93, 1, 'procurement', 'request_approved', 'purchase_requests', 7, 'PR approved (L2)', NULL, '{\"level\": 2, \"decision\": \"approved\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-15 19:45:36'),
(94, 1, 'procurement', 'mark_purchased', 'purchase_requests', 7, 'PR marked purchased, expenses created', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-15 19:46:02'),
(96, 1, 'members', 'import', 'members', NULL, 'Imported 102 members from xlsx file', NULL, '{\"skipped\": 0, \"inserted\": 102}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-15 19:52:03');

-- --------------------------------------------------------

--
-- Table structure for table `budget_expenses`
--

DROP TABLE IF EXISTS `budget_expenses`;
CREATE TABLE IF NOT EXISTS `budget_expenses` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `budget_id` bigint UNSIGNED NOT NULL,
  `item_name` varchar(220) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(14,2) NOT NULL DEFAULT '0.00',
  `expense_date` date NOT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `source_type` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'manual, procurement',
  `source_id` bigint UNSIGNED DEFAULT NULL,
  `recorded_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_be_budget` (`budget_id`),
  KEY `idx_be_date` (`expense_date`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `budget_expenses`
--

INSERT INTO `budget_expenses` (`id`, `budget_id`, `item_name`, `amount`, `expense_date`, `notes`, `source_type`, `source_id`, `recorded_by`, `created_at`, `updated_at`) VALUES
(1, 1, 'MBAO 2 BY 2', 200000.00, '2026-04-14', 'From PR PR-20260414-085', 'procurement', 2, 1, '2026-04-14 17:08:37', '2026-04-14 17:08:37'),
(2, 1, 'MISUMARI', 300000.00, '2026-04-14', 'From PR PR-20260414-085', 'procurement', 2, 1, '2026-04-14 17:08:37', '2026-04-14 17:08:37'),
(3, 1, 'BATI  ZA MSAUZI', 230000.00, '2026-04-14', 'From PR PR-20260414-085', 'procurement', 2, 1, '2026-04-14 17:08:37', '2026-04-14 17:08:37'),
(4, 1, 'usafiri', 170000.00, '2026-04-14', 'PESA YA USAFIRI ALIPEWA JOHN', NULL, NULL, 1, '2026-04-15 06:32:39', '2026-04-15 06:32:39'),
(5, 1, 'CHAKULA CHA MAFUNDI', 100000.00, '2026-04-15', 'KAPEWA PETER', NULL, NULL, 1, '2026-04-15 06:33:05', '2026-04-15 06:33:05'),
(6, 3, 'MBAO 2 BY 2', 10000.00, '2026-04-15', 'From PR PR-20260415-351', 'procurement', 5, 2, '2026-04-15 13:54:09', '2026-04-15 13:54:09'),
(7, 2, 'MBAO 2 BY 2', 39000.00, '2026-04-15', 'From PR PR-20260415-470', 'procurement', 4, 2, '2026-04-15 14:04:22', '2026-04-15 14:04:22'),
(8, 2, 'MBAO 2 BY 2', 10000.00, '2026-04-15', 'From PR PR-20260415-149', 'procurement', 6, 2, '2026-04-15 15:31:25', '2026-04-15 15:31:25'),
(9, 4, 'MBAO 2 BY 2', 350000.00, '2026-04-15', 'From PR PR-20260415-465', 'procurement', 7, 1, '2026-04-15 19:46:02', '2026-04-15 19:46:02'),
(10, 4, 'BATI', 6000000.00, '2026-04-15', 'From PR PR-20260415-465', 'procurement', 7, 1, '2026-04-15 19:46:02', '2026-04-15 19:46:02');

-- --------------------------------------------------------

--
-- Table structure for table `church_accounts`
--

DROP TABLE IF EXISTS `church_accounts`;
CREATE TABLE IF NOT EXISTS `church_accounts` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `account_name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `account_type` enum('cash','bank','mobile_money') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'bank',
  `bank_name` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `account_number` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `opening_balance` decimal(14,2) NOT NULL DEFAULT '0.00',
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_church_accounts_type` (`account_type`),
  KEY `idx_church_accounts_active` (`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `church_accounts`
--

INSERT INTO `church_accounts` (`id`, `account_name`, `account_type`, `bank_name`, `account_number`, `opening_balance`, `description`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Pesa Taslimu (Cash Box)', 'cash', NULL, NULL, 0.00, 'Main church cash box', 1, '2026-04-15 07:09:59', '2026-04-15 07:09:59'),
(2, 'M-Pesa', 'mobile_money', 'Vodacom', NULL, 0.00, 'Church M-Pesa account', 1, '2026-04-15 07:09:59', '2026-04-15 07:09:59'),
(3, 'CRDB Bank', 'bank', 'CRDB Bank', NULL, 0.00, 'Church main bank account', 1, '2026-04-15 07:09:59', '2026-04-15 07:09:59');

-- --------------------------------------------------------

--
-- Table structure for table `church_settings`
--

DROP TABLE IF EXISTS `church_settings`;
CREATE TABLE IF NOT EXISTS `church_settings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `setting_value` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `church_settings`
--

INSERT INTO `church_settings` (`id`, `setting_key`, `setting_value`, `updated_at`) VALUES
(1, 'church_name', 'TAG MSASANI', '2026-04-10 15:07:41'),
(2, 'location', 'Dar es Salaam, Tanzania', '2026-04-10 15:07:41'),
(3, 'phone', '', '2026-04-10 15:07:41'),
(4, 'email', '', '2026-04-10 15:07:41'),
(5, 'address', '', '2026-04-10 15:07:41'),
(6, 'pastor_name', '', '2026-04-10 15:07:41'),
(7, 'founded_year', '', '2026-04-10 15:07:41');

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

DROP TABLE IF EXISTS `departments`;
CREATE TABLE IF NOT EXISTS `departments` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `head_email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `head_user_id` bigint UNSIGNED DEFAULT NULL COMMENT 'Optional department head (user)',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `head_name` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  UNIQUE KEY `head_email` (`head_email`),
  KEY `fk_departments_head` (`head_user_id`),
  KEY `idx_departments_active` (`is_active`),
  KEY `idx_departments_head_email` (`head_email`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`id`, `name`, `description`, `head_email`, `password`, `head_user_id`, `is_active`, `created_at`, `updated_at`, `head_name`) VALUES
(1, 'Ibada', 'Idara ya ibada na muziki', NULL, NULL, NULL, 1, '2026-04-10 13:35:06', '2026-04-10 13:35:06', NULL),
(2, 'Vijana', 'Idara ya vijana', NULL, NULL, NULL, 1, '2026-04-10 13:35:06', '2026-04-10 13:35:06', NULL),
(3, 'Ujenzi', 'Mradi wa ujenzi wa kanisa', NULL, NULL, NULL, 1, '2026-04-10 13:35:06', '2026-04-10 13:35:06', NULL),
(4, 'Huduma', 'Huduma za jamii na misaada', NULL, NULL, NULL, 1, '2026-04-10 13:35:06', '2026-04-10 13:35:06', NULL),
(5, 'Elimu', 'Elimu ya Biblia na mafunzo', 'elimu@tagmsasani.com', '$2y$10$KQiUU9Q4aSDw2CDvq2amd.EKfwwgdrIfztc.ysXjC3rcl7iizB6o2', NULL, 1, '2026-04-10 13:35:06', '2026-04-11 11:33:36', NULL),
(6, 'Utawala', 'Utawala na usimamizi wa kanisa', NULL, NULL, NULL, 1, '2026-04-10 13:35:06', '2026-04-10 13:35:06', NULL),
(13, '', NULL, NULL, NULL, NULL, 0, '2026-04-10 15:18:28', '2026-04-10 15:40:50', NULL),
(16, 'Media', 'Media and Evangelism', 'makimbiliotv1@gmail.com', '$2a$12$z/q2XQZ12AngPu7kkJKkXeJUs72P8v4kha1WNPkImdOZQSOneiLcy', NULL, 1, '2026-04-10 15:39:55', '2026-04-14 08:24:21', 'Patrick Maganga');

-- --------------------------------------------------------

--
-- Table structure for table `department_budgets`
--

DROP TABLE IF EXISTS `department_budgets`;
CREATE TABLE IF NOT EXISTS `department_budgets` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `department` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category_id` bigint UNSIGNED DEFAULT NULL,
  `fiscal_month` varchar(7) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'YYYY-MM',
  `planned_amount` decimal(14,2) NOT NULL DEFAULT '0.00',
  `actual_amount` decimal(14,2) NOT NULL DEFAULT '0.00',
  `actual_notes` text COLLATE utf8mb4_unicode_ci,
  `reserved_amount` decimal(14,2) NOT NULL DEFAULT '0.00',
  `spent_amount` decimal(14,2) NOT NULL DEFAULT '0.00',
  `status` enum('draft','submitted','approved','rejected','expenses_added','closed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `submitted_by` bigint UNSIGNED DEFAULT NULL,
  `approved_by` bigint UNSIGNED DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `closed_at` datetime DEFAULT NULL,
  `closed_by` bigint UNSIGNED DEFAULT NULL,
  `finance_entry_id` bigint UNSIGNED DEFAULT NULL,
  `event_id` bigint UNSIGNED DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_db_dept` (`department`),
  KEY `idx_db_month` (`fiscal_month`),
  KEY `idx_db_status` (`status`),
  KEY `idx_db_event` (`event_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `department_budgets`
--

INSERT INTO `department_budgets` (`id`, `department`, `category_id`, `fiscal_month`, `planned_amount`, `actual_amount`, `actual_notes`, `reserved_amount`, `spent_amount`, `status`, `submitted_by`, `approved_by`, `approved_at`, `closed_at`, `closed_by`, `finance_entry_id`, `event_id`, `description`, `notes`, `created_at`, `updated_at`) VALUES
(1, 'Ujenzi', 4, '2026-05', 1000000.00, 1000000.00, NULL, 0.00, 0.00, 'closed', 1, 1, '2026-04-14 20:04:10', '2026-04-15 09:54:34', 1, 12, NULL, 'UJEZI WA KANISA', '', '2026-04-14 16:39:34', '2026-04-15 06:54:34'),
(2, 'Ibada', 5, '2026-12', 50000.00, 49000.00, NULL, 0.00, 0.00, 'closed', 2, 2, '2026-04-15 15:13:55', '2026-04-15 18:32:04', 2, 16, NULL, 'UJEZI WA KANISA', '', '2026-04-15 12:13:30', '2026-04-15 15:32:04'),
(3, 'Huduma', 4, '2026-11', 10000.00, 10000.00, NULL, 0.00, 0.00, 'expenses_added', 2, 2, '2026-04-15 15:14:44', NULL, NULL, NULL, 14, 'UJEZI WA KANISA', '', '2026-04-15 12:14:30', '2026-04-15 13:54:09'),
(4, 'Ujenzi', 4, '2026-05', 10000000.00, 6350000.00, NULL, 0.00, 0.00, 'expenses_added', 2, 1, '2026-04-15 22:42:33', NULL, NULL, NULL, NULL, 'UJENZI WA MABWENI', '', '2026-04-15 19:37:48', '2026-04-15 19:46:02');

-- --------------------------------------------------------

--
-- Table structure for table `department_contributions`
--

DROP TABLE IF EXISTS `department_contributions`;
CREATE TABLE IF NOT EXISTS `department_contributions` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `department_id` bigint UNSIGNED NOT NULL,
  `member_id` bigint UNSIGNED DEFAULT NULL,
  `contributor_name` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `amount` decimal(14,2) NOT NULL,
  `payment_method` enum('cash','mpesa','cheque','bank_transfer','other') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'cash',
  `purpose` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contribution_date` date NOT NULL,
  `recorded_by` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_dept_contrib_dept` (`department_id`),
  KEY `idx_dept_contrib_date` (`contribution_date`),
  KEY `idx_dept_contrib_member` (`member_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `department_contributions`
--

INSERT INTO `department_contributions` (`id`, `department_id`, `member_id`, `contributor_name`, `amount`, `payment_method`, `purpose`, `contribution_date`, `recorded_by`, `created_at`) VALUES
(1, 16, 40, NULL, 16.00, 'cash', NULL, '2026-04-15', 'Media', '2026-04-15 15:28:30');

-- --------------------------------------------------------

--
-- Table structure for table `department_finance_records`
--

DROP TABLE IF EXISTS `department_finance_records`;
CREATE TABLE IF NOT EXISTS `department_finance_records` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `department_id` bigint UNSIGNED NOT NULL,
  `type` enum('income','expense') COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(14,2) NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `entry_date` date NOT NULL,
  `recorded_by` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Name of department head at time of entry',
  `deleted_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_dept_fin_rec_dept` (`department_id`),
  KEY `idx_dept_fin_rec_type` (`type`),
  KEY `idx_dept_fin_rec_date` (`entry_date`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `department_finance_records`
--

INSERT INTO `department_finance_records` (`id`, `department_id`, `type`, `category`, `amount`, `description`, `entry_date`, `recorded_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 16, 'income', 'Offering', 10000.00, NULL, '2026-04-14', 'Media', NULL, '2026-04-14 11:44:19', '2026-04-14 11:44:19'),
(2, 16, 'expense', 'Utilities', 200.00, 'asS', '2026-04-14', 'Media', NULL, '2026-04-14 11:44:49', '2026-04-14 11:44:49');

-- --------------------------------------------------------

--
-- Table structure for table `department_leaders`
--

DROP TABLE IF EXISTS `department_leaders`;
CREATE TABLE IF NOT EXISTS `department_leaders` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `department_id` bigint UNSIGNED NOT NULL,
  `member_id` bigint UNSIGNED DEFAULT NULL COMMENT 'FK to members table',
  `leader_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `leader_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bio` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_dept_leader_member_type` (`department_id`,`member_id`,`leader_type`),
  KEY `fk_dept_leaders_member` (`member_id`),
  KEY `idx_dept_leaders_department` (`department_id`),
  KEY `idx_dept_leaders_active` (`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `department_leaders`
--

INSERT INTO `department_leaders` (`id`, `department_id`, `member_id`, `leader_type`, `leader_name`, `email`, `phone`, `bio`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 16, NULL, 'Vice Chairperson', 'Aaron John', 'aaron.j@yahoo.com', '+255733234085', NULL, 1, '2026-04-14 11:50:46', '2026-04-14 11:50:46'),
(2, 16, 6, 'Vice Chairperson', 'Nickson Mmary', 'nickmmary6@gmail.com', '+255068521530', NULL, 1, '2026-04-14 11:51:26', '2026-04-14 11:51:26');

-- --------------------------------------------------------

--
-- Table structure for table `department_members`
--

DROP TABLE IF EXISTS `department_members`;
CREATE TABLE IF NOT EXISTS `department_members` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `department_id` bigint UNSIGNED NOT NULL,
  `member_id` bigint UNSIGNED NOT NULL,
  `assigned_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `notes` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Role or additional info (e.g., Worship Leader, Youth Coordinator)',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_dept_member` (`department_id`,`member_id`),
  KEY `idx_dept_members_department` (`department_id`),
  KEY `idx_dept_members_member` (`member_id`),
  KEY `idx_dept_members_assigned_date` (`assigned_date`)
) ENGINE=InnoDB AUTO_INCREMENT=218 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `department_members`
--

INSERT INTO `department_members` (`id`, `department_id`, `member_id`, `assigned_date`, `notes`, `created_at`, `updated_at`) VALUES
(1, 5, 4, '2026-04-11 14:21:48', NULL, '2026-04-11 14:21:48', '2026-04-11 14:21:48'),
(2, 5, 5, '2026-04-11 14:22:46', NULL, '2026-04-11 14:22:46', '2026-04-11 14:22:46'),
(3, 16, 6, '2026-04-14 11:30:10', NULL, '2026-04-14 11:30:10', '2026-04-14 11:30:10'),
(4, 16, 7, '2026-04-14 11:40:16', NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(5, 16, 8, '2026-04-14 11:40:16', NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(6, 16, 9, '2026-04-14 11:40:16', NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(8, 16, 11, '2026-04-14 11:40:16', NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(9, 16, 12, '2026-04-14 11:40:16', NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(10, 16, 13, '2026-04-14 11:40:16', NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(11, 16, 14, '2026-04-14 11:40:16', NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(12, 16, 15, '2026-04-14 11:40:16', NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(13, 16, 16, '2026-04-14 11:40:16', NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(14, 16, 17, '2026-04-14 11:40:16', NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(15, 16, 18, '2026-04-14 11:40:16', NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(16, 16, 19, '2026-04-14 11:40:16', NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(17, 16, 20, '2026-04-14 11:40:16', NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(18, 16, 21, '2026-04-14 11:40:16', NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(19, 16, 22, '2026-04-14 11:40:16', NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(20, 16, 23, '2026-04-14 11:40:16', NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(21, 16, 24, '2026-04-14 11:40:16', NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(22, 16, 25, '2026-04-14 11:40:16', NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(23, 16, 26, '2026-04-14 11:40:16', NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(24, 16, 27, '2026-04-14 11:40:16', NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(25, 16, 28, '2026-04-14 11:40:16', NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(26, 16, 29, '2026-04-14 11:40:16', NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(27, 16, 30, '2026-04-14 11:40:16', NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(28, 16, 31, '2026-04-14 11:40:16', NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(29, 16, 32, '2026-04-14 11:40:16', NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(30, 16, 33, '2026-04-14 11:40:16', NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(31, 16, 34, '2026-04-14 11:40:16', NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(32, 16, 35, '2026-04-14 11:40:16', NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(33, 16, 36, '2026-04-14 11:40:16', NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(34, 16, 37, '2026-04-14 11:40:16', NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(35, 16, 38, '2026-04-14 11:40:16', NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(36, 16, 39, '2026-04-14 11:40:16', NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(37, 16, 40, '2026-04-14 11:40:16', NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(38, 16, 41, '2026-04-14 11:40:16', NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(39, 16, 42, '2026-04-14 11:40:16', NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(40, 16, 43, '2026-04-14 11:40:16', NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(41, 16, 44, '2026-04-14 11:40:16', NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(42, 16, 45, '2026-04-14 11:40:16', NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(43, 16, 46, '2026-04-14 11:40:16', NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(44, 16, 47, '2026-04-14 11:40:16', NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(45, 16, 48, '2026-04-14 11:40:16', NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(46, 16, 49, '2026-04-14 11:40:16', NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(47, 16, 50, '2026-04-14 11:40:16', NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(48, 16, 51, '2026-04-14 11:40:16', NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(49, 16, 52, '2026-04-14 11:40:16', NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(50, 16, 53, '2026-04-14 11:40:16', NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(51, 16, 54, '2026-04-14 11:40:16', NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(52, 16, 55, '2026-04-14 11:40:16', NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(53, 16, 56, '2026-04-14 11:40:16', NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(54, 16, 57, '2026-04-14 11:40:16', NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(55, 16, 58, '2026-04-14 11:40:16', NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(56, 16, 59, '2026-04-14 11:40:16', NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(57, 16, 60, '2026-04-14 11:40:16', NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(58, 16, 61, '2026-04-14 11:40:16', NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(59, 16, 62, '2026-04-14 11:40:16', NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(60, 16, 63, '2026-04-14 11:40:16', NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(61, 16, 64, '2026-04-14 11:40:16', NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(62, 16, 65, '2026-04-14 11:40:16', NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(63, 16, 66, '2026-04-14 11:40:16', NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(64, 16, 67, '2026-04-14 11:40:16', NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(65, 16, 68, '2026-04-14 11:40:16', NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(66, 16, 69, '2026-04-14 11:40:16', NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(67, 16, 70, '2026-04-14 11:40:16', NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(68, 16, 71, '2026-04-14 11:40:16', NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(69, 16, 72, '2026-04-14 11:40:16', NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(70, 16, 73, '2026-04-14 11:40:16', NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(71, 16, 74, '2026-04-14 11:40:16', NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(72, 16, 75, '2026-04-14 11:40:16', NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(73, 16, 76, '2026-04-14 11:40:16', NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(74, 16, 77, '2026-04-14 11:40:16', NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(75, 16, 78, '2026-04-14 11:40:16', NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(76, 16, 79, '2026-04-14 11:40:16', NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(77, 16, 80, '2026-04-14 11:40:16', NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(78, 16, 81, '2026-04-14 11:40:16', NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(79, 16, 82, '2026-04-14 11:40:16', NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(80, 16, 83, '2026-04-14 11:40:16', NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(81, 16, 84, '2026-04-14 11:40:16', NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(82, 16, 85, '2026-04-14 11:40:16', NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(83, 16, 86, '2026-04-14 11:40:16', NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(84, 16, 87, '2026-04-14 11:40:16', NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(85, 16, 88, '2026-04-14 11:40:16', NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(86, 16, 89, '2026-04-14 11:40:16', NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(87, 16, 90, '2026-04-14 11:40:16', NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(88, 16, 91, '2026-04-14 11:40:16', NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(89, 16, 92, '2026-04-14 11:40:16', NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(90, 16, 93, '2026-04-14 11:40:16', NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(91, 16, 94, '2026-04-14 11:40:16', NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(92, 16, 95, '2026-04-14 11:40:16', NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(93, 16, 96, '2026-04-14 11:40:16', NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(94, 16, 97, '2026-04-14 11:40:16', NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(95, 16, 98, '2026-04-14 11:40:16', NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(96, 16, 99, '2026-04-14 11:40:16', NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(97, 16, 100, '2026-04-14 11:40:16', NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(98, 16, 101, '2026-04-14 11:40:16', NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(99, 16, 102, '2026-04-14 11:40:16', NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(100, 16, 103, '2026-04-14 11:40:16', NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(101, 16, 104, '2026-04-14 11:40:16', NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(102, 16, 105, '2026-04-14 11:40:16', NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(103, 16, 106, '2026-04-14 11:40:16', NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(104, 16, 107, '2026-04-14 11:40:16', NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(105, 16, 108, '2026-04-14 11:40:16', NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(208, 16, 211, '2026-04-14 11:42:14', NULL, '2026-04-14 11:42:14', '2026-04-14 11:42:14'),
(209, 16, 212, '2026-04-14 11:42:14', NULL, '2026-04-14 11:42:14', '2026-04-14 11:42:14'),
(210, 16, 213, '2026-04-14 11:42:14', NULL, '2026-04-14 11:42:14', '2026-04-14 11:42:14'),
(211, 16, 214, '2026-04-14 11:42:14', NULL, '2026-04-14 11:42:14', '2026-04-14 11:42:14'),
(212, 16, 215, '2026-04-14 11:42:14', NULL, '2026-04-14 11:42:14', '2026-04-14 11:42:14'),
(213, 16, 216, '2026-04-14 11:42:14', NULL, '2026-04-14 11:42:14', '2026-04-14 11:42:14'),
(214, 16, 217, '2026-04-14 11:42:14', NULL, '2026-04-14 11:42:14', '2026-04-14 11:42:14'),
(215, 16, 218, '2026-04-14 11:42:14', NULL, '2026-04-14 11:42:14', '2026-04-14 11:42:14'),
(216, 16, 219, '2026-04-14 11:42:14', NULL, '2026-04-14 11:42:14', '2026-04-14 11:42:14'),
(217, 16, 220, '2026-04-14 11:42:14', NULL, '2026-04-14 11:42:14', '2026-04-14 11:42:14');

-- --------------------------------------------------------

--
-- Table structure for table `department_reports`
--

DROP TABLE IF EXISTS `department_reports`;
CREATE TABLE IF NOT EXISTS `department_reports` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `department_id` bigint UNSIGNED NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` longtext COLLATE utf8mb4_unicode_ci,
  `report_date` date NOT NULL,
  `category` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('draft','submitted','approved','rejected') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `submitted_at` datetime DEFAULT NULL,
  `reviewed_by` bigint UNSIGNED DEFAULT NULL,
  `reviewed_at` datetime DEFAULT NULL,
  `review_notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_dept_reports_reviewed_by` (`reviewed_by`),
  KEY `idx_dept_reports_department` (`department_id`),
  KEY `idx_dept_reports_status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `department_reports`
--

INSERT INTO `department_reports` (`id`, `department_id`, `title`, `description`, `report_date`, `category`, `status`, `submitted_at`, `reviewed_by`, `reviewed_at`, `review_notes`, `created_at`, `updated_at`) VALUES
(1, 16, 'KKK', NULL, '2026-04-14', 'Monthly', 'draft', NULL, NULL, NULL, NULL, '2026-04-14 12:17:15', '2026-04-14 12:17:15'),
(2, 16, 'AASASD', NULL, '2026-04-14', 'Monthly', 'submitted', '2026-04-14 15:18:37', NULL, NULL, NULL, '2026-04-14 12:18:31', '2026-04-14 12:18:37');

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

DROP TABLE IF EXISTS `events`;
CREATE TABLE IF NOT EXISTS `events` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `event_code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(180) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `event_type` enum('service','seminar','meeting','appointment','other') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'other',
  `start_datetime` datetime NOT NULL,
  `end_datetime` datetime NOT NULL,
  `venue` varchar(180) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `location` varchar(180) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `organizer_user_id` bigint UNSIGNED DEFAULT NULL,
  `target_group_id` bigint UNSIGNED DEFAULT NULL,
  `expected_attendance` int UNSIGNED DEFAULT NULL,
  `status` enum('draft','planned','ongoing','completed','cancelled') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `budget_total` decimal(14,2) NOT NULL DEFAULT '0.00',
  `budget_status` enum('draft','pending_approval','approved','rejected','in_progress','completed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `budget_approved_by` bigint UNSIGNED DEFAULT NULL,
  `budget_approved_at` datetime DEFAULT NULL,
  `budget_locked_at` datetime DEFAULT NULL,
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `pastor_on_duty` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `usher_on_duty` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `event_code` (`event_code`),
  KEY `fk_events_organizer` (`organizer_user_id`),
  KEY `fk_events_target_group` (`target_group_id`),
  KEY `idx_events_start` (`start_datetime`),
  KEY `idx_events_status` (`status`),
  KEY `idx_events_category` (`event_type`),
  KEY `idx_events_budget_status` (`budget_status`),
  KEY `fk_events_budget_approved_by` (`budget_approved_by`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `event_attendance`
--

DROP TABLE IF EXISTS `event_attendance`;
CREATE TABLE IF NOT EXISTS `event_attendance` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `event_id` bigint UNSIGNED NOT NULL,
  `member_id` bigint UNSIGNED NOT NULL,
  `status` enum('registered','present','absent') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'registered',
  `check_in_datetime` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_event_member` (`event_id`,`member_id`),
  KEY `idx_event_att_member` (`member_id`),
  KEY `idx_event_att_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `event_budget_items`
--

DROP TABLE IF EXISTS `event_budget_items`;
CREATE TABLE IF NOT EXISTS `event_budget_items` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `event_id` bigint UNSIGNED NOT NULL,
  `item_type` enum('income','expense') COLLATE utf8mb4_unicode_ci NOT NULL,
  `budget_status` enum('draft','pending_approval','approved','rejected','in_progress','completed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `item_name` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL,
  `planned_amount` decimal(14,2) NOT NULL,
  `actual_amount` decimal(14,2) NOT NULL DEFAULT '0.00',
  `notes` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `approved_by` bigint UNSIGNED DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `rejection_reason` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `idx_event_budget_event_id` (`event_id`),
  KEY `idx_event_budget_type` (`item_type`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `event_finance_links`
--

DROP TABLE IF EXISTS `event_finance_links`;
CREATE TABLE IF NOT EXISTS `event_finance_links` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `event_id` bigint UNSIGNED NOT NULL,
  `finance_entry_id` bigint UNSIGNED NOT NULL,
  `relation_type` enum('income','expense') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'expense',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_event_finance` (`event_id`,`finance_entry_id`),
  KEY `idx_event_finance_links_event_id` (`event_id`),
  KEY `idx_event_finance_links_entry_id` (`finance_entry_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `event_tasks`
--

DROP TABLE IF EXISTS `event_tasks`;
CREATE TABLE IF NOT EXISTS `event_tasks` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `event_id` bigint UNSIGNED NOT NULL,
  `title` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL,
  `details` text COLLATE utf8mb4_unicode_ci,
  `assigned_to_user_id` bigint UNSIGNED NOT NULL,
  `due_datetime` datetime DEFAULT NULL,
  `task_status` enum('todo','in_progress','done','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'todo',
  `priority` enum('low','medium','high') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'medium',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_event_tasks_user` (`assigned_to_user_id`),
  KEY `idx_event_tasks_event_id` (`event_id`),
  KEY `idx_event_tasks_status` (`task_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `finance_categories`
--

DROP TABLE IF EXISTS `finance_categories`;
CREATE TABLE IF NOT EXISTS `finance_categories` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `category_type` enum('income','expense') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_system` tinyint(1) NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  KEY `idx_finance_categories_type` (`category_type`),
  KEY `idx_finance_categories_active` (`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `finance_categories`
--

INSERT INTO `finance_categories` (`id`, `category_type`, `code`, `name`, `description`, `is_system`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'income', 'TITHE', 'Tithe', 'Regular member tithe', 1, 1, '2026-04-10 10:43:44', '2026-04-10 10:43:44'),
(2, 'income', 'OFFERING', 'Offering', 'Service and event offerings', 1, 1, '2026-04-10 10:43:44', '2026-04-10 10:43:44'),
(3, 'income', 'DONATION', 'Donation', 'External/internal donations', 1, 1, '2026-04-10 10:43:44', '2026-04-10 10:43:44'),
(4, 'expense', 'PROCUREMENT', 'Procurement Expense', 'Expenses from approved procurement', 1, 1, '2026-04-10 10:43:44', '2026-04-10 10:43:44'),
(5, 'expense', 'MAINTENANCE', 'Maintenance', 'Asset maintenance and repair', 1, 1, '2026-04-10 10:43:44', '2026-04-10 10:43:44'),
(6, 'expense', 'EVENT_EXPENSE', 'Event Expense', 'Event operational expenses', 1, 1, '2026-04-10 10:43:44', '2026-04-10 10:43:44');

-- --------------------------------------------------------

--
-- Table structure for table `finance_entries`
--

DROP TABLE IF EXISTS `finance_entries`;
CREATE TABLE IF NOT EXISTS `finance_entries` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `entry_no` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `entry_date` date NOT NULL,
  `category_id` bigint UNSIGNED NOT NULL,
  `amount` decimal(14,2) NOT NULL,
  `payment_method` enum('cash','mobile_money','bank_transfer','card','other') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `reference_no` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `source_type` enum('manual','event','procurement','system') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'manual',
  `source_id` bigint UNSIGNED DEFAULT NULL,
  `event_id` bigint UNSIGNED DEFAULT NULL,
  `member_id` bigint UNSIGNED DEFAULT NULL,
  `supplier_id` bigint UNSIGNED DEFAULT NULL,
  `purchase_order_id` bigint UNSIGNED DEFAULT NULL,
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `recorded_by` bigint UNSIGNED NOT NULL,
  `approved_by` bigint UNSIGNED DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `approval_status` enum('pending','approved','rejected') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `rejection_count` tinyint UNSIGNED NOT NULL DEFAULT '0',
  `rejection_reason` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `department_id` bigint UNSIGNED DEFAULT NULL,
  `church_account_id` bigint UNSIGNED DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `entry_no` (`entry_no`),
  KEY `fk_finance_entries_member` (`member_id`),
  KEY `fk_finance_entries_supplier` (`supplier_id`),
  KEY `fk_finance_entries_po` (`purchase_order_id`),
  KEY `fk_finance_entries_recorded_by` (`recorded_by`),
  KEY `fk_finance_entries_approved_by` (`approved_by`),
  KEY `idx_finance_entries_date` (`entry_date`),
  KEY `idx_finance_entries_category_id` (`category_id`),
  KEY `idx_finance_entries_event_id` (`event_id`),
  KEY `idx_finance_entries_source` (`source_type`,`source_id`),
  KEY `idx_finance_entries_dept` (`department_id`),
  KEY `idx_fe_church_account` (`church_account_id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `finance_entries`
--

INSERT INTO `finance_entries` (`id`, `entry_no`, `entry_date`, `category_id`, `amount`, `payment_method`, `reference_no`, `source_type`, `source_id`, `event_id`, `member_id`, `supplier_id`, `purchase_order_id`, `description`, `recorded_by`, `approved_by`, `approved_at`, `approval_status`, `rejection_count`, `rejection_reason`, `created_at`, `updated_at`, `department_id`, `church_account_id`) VALUES
(1, 'FIN-2026-101', '2026-03-26', 4, 780000.00, 'bank_transfer', NULL, 'procurement', 1, NULL, NULL, 1, 1, 'Procurement expense for Youth Outreach', 2, NULL, NULL, 'approved', 0, NULL, '2026-04-10 10:43:44', '2026-04-10 10:43:44', NULL, NULL),
(2, 'FIN-2026-102', '2026-03-30', 2, 1240000.00, 'cash', NULL, 'event', 1, NULL, NULL, NULL, NULL, 'Youth Outreach event offering', 2, NULL, NULL, 'approved', 0, NULL, '2026-04-10 10:43:44', '2026-04-10 10:43:44', NULL, NULL),
(3, '123', '2026-04-11', 2, 800000.00, 'cash', NULL, 'manual', NULL, NULL, NULL, NULL, NULL, 'church income', 1, NULL, NULL, 'approved', 0, NULL, '2026-04-11 14:56:08', '2026-04-11 14:56:08', NULL, NULL),
(5, 'FIN-20260411-957', '2026-04-11', 2, 800000.00, 'cash', NULL, 'manual', NULL, NULL, 5, NULL, NULL, 'church income', 1, NULL, NULL, 'approved', 0, NULL, '2026-04-11 14:56:37', '2026-04-11 14:56:37', NULL, NULL),
(6, 'FIN-20260411-739', '2026-04-11', 2, 800000.00, 'cash', NULL, 'manual', NULL, NULL, 5, NULL, NULL, 'church income', 1, NULL, NULL, 'approved', 0, NULL, '2026-04-11 14:57:50', '2026-04-11 14:57:50', NULL, NULL),
(7, 'FIN-20260411-125', '2026-04-05', 2, 6000000.00, 'cash', NULL, 'manual', NULL, NULL, NULL, NULL, NULL, 'Sadaka', 1, NULL, NULL, 'approved', 0, NULL, '2026-04-11 15:03:02', '2026-04-11 15:03:02', NULL, NULL),
(8, 'FIN-20260411-449', '2026-04-01', 3, 600000.00, 'mobile_money', NULL, 'manual', NULL, NULL, NULL, NULL, NULL, 'Donation', 1, NULL, NULL, 'approved', 0, NULL, '2026-04-11 15:04:00', '2026-04-11 15:04:00', NULL, NULL),
(9, 'FIN-20260413-765', '2026-04-12', 6, 400000.00, 'mobile_money', NULL, 'manual', NULL, NULL, NULL, NULL, NULL, 'Elimu ya Biblia na mafunzo', 1, 1, '2026-04-13 16:51:43', 'approved', 0, NULL, '2026-04-13 13:42:51', '2026-04-13 13:51:43', NULL, NULL),
(10, 'FIN-20260414-0010', '2026-04-14', 6, 100000.00, 'cash', NULL, 'event', 13, NULL, NULL, NULL, NULL, 'Event budget: SEMINA YA VIJANA | Items: Event Budget (TZS 100,000)', 1, 1, '2026-04-14 11:03:33', 'approved', 0, NULL, '2026-04-14 08:03:01', '2026-04-14 08:03:33', NULL, NULL),
(11, 'FIN-20260414-0011', '2026-04-14', 6, 10000.00, 'cash', NULL, 'event', 14, NULL, NULL, NULL, NULL, 'Event budget: njj | Items: Event Budget (TZS 10,000)', 2, 1, '2026-04-15 13:57:47', 'approved', 0, NULL, '2026-04-14 14:09:42', '2026-04-15 10:57:47', NULL, NULL),
(12, 'BDGT-202605-001', '2026-05-01', 4, 1000000.00, 'cash', NULL, 'system', NULL, NULL, NULL, NULL, NULL, 'Budget Expenses: Ujenzi (2026-05) — UJEZI WA KANISA UPANDE WA CHOO [Used: TZS 1,000,000 / Budget: TZS 1,000,000]', 1, 1, '2026-04-15 09:54:34', 'approved', 0, NULL, '2026-04-15 06:54:34', '2026-04-15 06:54:34', NULL, NULL),
(13, 'FIN-20260415-144', '2026-04-04', 2, 2000000.00, 'mobile_money', NULL, 'manual', NULL, NULL, NULL, NULL, NULL, 'hh', 1, NULL, NULL, 'approved', 0, NULL, '2026-04-15 06:56:47', '2026-04-15 06:56:47', NULL, NULL),
(14, 'FIN-20260415-203', '2026-04-15', 3, 100000.00, 'cash', NULL, 'manual', NULL, NULL, 102, NULL, NULL, 'jjj', 1, NULL, NULL, 'approved', 0, NULL, '2026-04-15 06:57:40', '2026-04-15 06:57:40', NULL, NULL),
(15, 'FIN-20260416-001', '2026-04-16', 3, 100000.00, 'bank_transfer', NULL, 'manual', NULL, NULL, NULL, NULL, NULL, 'hh', 1, NULL, NULL, 'approved', 0, NULL, '2026-04-15 08:12:38', '2026-04-15 08:12:38', NULL, 3),
(16, 'BDGT-202612-002', '2026-12-01', 5, 49000.00, 'cash', NULL, 'system', NULL, NULL, NULL, NULL, NULL, 'Budget Expenses: Ibada (2026-12) [Used: TZS 49,000 / Budget: TZS 50,000]', 2, 2, '2026-04-15 18:32:04', 'approved', 0, NULL, '2026-04-15 15:32:04', '2026-04-15 15:32:04', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `groups`
--

DROP TABLE IF EXISTS `groups`;
CREATE TABLE IF NOT EXISTS `groups` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `leader_member_id` bigint UNSIGNED DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `fk_groups_leader_member` (`leader_member_id`),
  KEY `idx_groups_active` (`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `groups`
--

INSERT INTO `groups` (`id`, `name`, `description`, `leader_member_id`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Youth', 'Youth ministry', NULL, 1, '2026-04-10 10:43:44', '2026-04-10 10:43:44'),
(2, 'Choir', 'Worship choir team', NULL, 1, '2026-04-10 10:43:44', '2026-04-10 10:43:44'),
(3, 'Women Fellowship', 'Women ministry', NULL, 1, '2026-04-10 10:43:44', '2026-04-10 10:43:44');

-- --------------------------------------------------------

--
-- Table structure for table `guests`
--

DROP TABLE IF EXISTS `guests`;
CREATE TABLE IF NOT EXISTS `guests` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `guest_code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `first_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `location` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `invited_by_member_id` bigint UNSIGNED DEFAULT NULL,
  `invited_by_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `service_date` date NOT NULL,
  `visit_type` enum('first_time','returning','referred') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'first_time',
  `email` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `age_group` enum('child','teen','youth','adult','senior') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `status` enum('registered','visited','converted','inactive') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'registered',
  `follow_up_date` date DEFAULT NULL,
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `guest_code` (`guest_code`),
  KEY `fk_guests_invited_by` (`invited_by_member_id`),
  KEY `fk_guests_created_by` (`created_by`),
  KEY `idx_guests_phone` (`phone`),
  KEY `idx_guests_service_date` (`service_date`),
  KEY `idx_guests_status` (`status`),
  KEY `idx_guests_location` (`location`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `guests`
--

INSERT INTO `guests` (`id`, `guest_code`, `first_name`, `last_name`, `phone`, `location`, `invited_by_member_id`, `invited_by_name`, `service_date`, `visit_type`, `email`, `age_group`, `notes`, `status`, `follow_up_date`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'GU-2026-00001', 'James', 'Philip', '+255764559664', 'Ilala', NULL, 'Samweli', '2026-04-12', 'first_time', 'johnkiduge@gmail.com', NULL, '', 'registered', '2026-04-12', 1, '2026-04-13 12:39:35', '2026-04-13 12:39:35'),
(3, 'GU-2026-00002', 'Daniel', 'Salum', '+255697235627', 'Ubungo', NULL, NULL, '2026-04-12', 'returning', 'makimbiliotv1@gmail.com', 'adult', '', 'registered', '2026-04-12', 1, '2026-04-13 12:45:43', '2026-04-13 12:45:43');

-- --------------------------------------------------------

--
-- Table structure for table `login_attempts`
--

DROP TABLE IF EXISTS `login_attempts`;
CREATE TABLE IF NOT EXISTS `login_attempts` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `identifier` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'email or phone used for login',
  `attempted_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_login_attempts_identifier` (`identifier`),
  KEY `idx_login_attempts_time` (`attempted_at`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `login_attempts`
--

INSERT INTO `login_attempts` (`id`, `identifier`, `attempted_at`) VALUES
(13, 'masoud@tanrail.co.tz', '2026-04-15 10:01:20'),
(15, 'admin@hasheem.com', '2026-04-15 22:38:25');

-- --------------------------------------------------------

--
-- Table structure for table `maintenance_logs`
--

DROP TABLE IF EXISTS `maintenance_logs`;
CREATE TABLE IF NOT EXISTS `maintenance_logs` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `asset_id` bigint UNSIGNED NOT NULL,
  `maintenance_type` enum('routine','repair','inspection','replacement') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `issue_description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `action_taken` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `service_provider` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `maintenance_cost` decimal(14,2) NOT NULL DEFAULT '0.00',
  `maintenance_date` date NOT NULL,
  `next_due_date` date DEFAULT NULL,
  `created_by` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_maintenance_created_by` (`created_by`),
  KEY `idx_maintenance_asset_id` (`asset_id`),
  KEY `idx_maintenance_date` (`maintenance_date`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `maintenance_logs`
--

INSERT INTO `maintenance_logs` (`id`, `asset_id`, `maintenance_type`, `issue_description`, `action_taken`, `service_provider`, `maintenance_cost`, `maintenance_date`, `next_due_date`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 1, 'repair', 'Channel noise on input 3', 'Internal board cleaned and connector replaced', 'AudioFix TZ', 180000.00, '2026-03-20', NULL, 2, '2026-04-10 10:43:44', '2026-04-10 10:43:44');

-- --------------------------------------------------------

--
-- Table structure for table `members`
--

DROP TABLE IF EXISTS `members`;
CREATE TABLE IF NOT EXISTS `members` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `member_code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `first_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `gender` enum('male','female','other') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `date_of_birth` date DEFAULT NULL,
  `marital_status` enum('single','married','widowed','divorced') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `alt_phone` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `physical_address` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ward` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `district` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `region` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `emergency_contact_name` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `emergency_contact_phone` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `baptism_date` date DEFAULT NULL,
  `join_date` date NOT NULL,
  `member_status` enum('active','inactive','transferred','deceased') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `updated_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `member_code` (`member_code`),
  UNIQUE KEY `uq_members_phone` (`phone`),
  KEY `fk_members_created_by` (`created_by`),
  KEY `fk_members_updated_by` (`updated_by`),
  KEY `idx_members_name` (`last_name`,`first_name`),
  KEY `idx_members_status` (`member_status`),
  KEY `idx_members_phone` (`phone`),
  KEY `idx_members_region` (`region`)
) ENGINE=InnoDB AUTO_INCREMENT=323 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `members`
--

INSERT INTO `members` (`id`, `member_code`, `first_name`, `last_name`, `gender`, `date_of_birth`, `marital_status`, `phone`, `alt_phone`, `email`, `physical_address`, `ward`, `district`, `region`, `emergency_contact_name`, `emergency_contact_phone`, `baptism_date`, `join_date`, `member_status`, `notes`, `created_by`, `updated_by`, `created_at`, `updated_at`) VALUES
(1, 'MBR-0001', 'Neema', 'Mushi', 'female', NULL, NULL, '+255712345001', NULL, NULL, NULL, 'Mikocheni', 'Kinondoni', 'Dar es Salaam', NULL, NULL, NULL, '2025-02-10', 'active', NULL, 3, NULL, '2026-04-10 10:43:44', '2026-04-10 10:43:44'),
(2, 'MBR-0002', 'Daniel', 'Mhando', 'male', NULL, NULL, '+255712345002', NULL, NULL, NULL, 'Kariakoo', 'Ilala', 'Dar es Salaam', NULL, NULL, NULL, '2024-11-20', 'active', NULL, 3, NULL, '2026-04-10 10:43:44', '2026-04-10 10:43:44'),
(3, 'MBR-0003', 'Asha', 'Kweka', 'female', NULL, NULL, '+255712345003', NULL, NULL, NULL, 'Sakina', 'Arusha Urban', 'Arusha', NULL, NULL, NULL, '2023-09-01', 'active', NULL, 3, NULL, '2026-04-10 10:43:44', '2026-04-10 10:43:44'),
(4, 'MBR-2026-0001', 'John', 'Kiduge', 'male', '2026-04-11', NULL, '0697235627', NULL, NULL, 'mbezi', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-11', 'active', NULL, NULL, NULL, '2026-04-11 14:21:48', '2026-04-11 14:21:48'),
(5, 'MBR-2026-0002', 'Michael', 'Philip', 'male', '2004-12-27', NULL, '0764559664', NULL, 'johnkiduge@gmail.com', 'Ubungo Nhc', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-11', 'active', NULL, NULL, NULL, '2026-04-11 14:22:46', '2026-04-11 14:22:46'),
(6, 'MBR-2026-0003', 'Nickson', 'Mmary', 'male', '1999-01-14', NULL, '+255068521530', NULL, 'nickmmary6@gmail.com', 'UBUNGO', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-14', 'active', NULL, NULL, NULL, '2026-04-14 11:30:10', '2026-04-14 11:30:10'),
(7, 'MBR-2026-0004', 'John', 'Doe', 'male', '1990-01-15', NULL, '+255712345678', NULL, 'john@example.com', 'Dar es Salaam', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-14', 'active', NULL, NULL, NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(8, 'MBR-2026-0005', 'Jane', 'Smith', 'female', '1985-05-20', NULL, '+255787654321', NULL, 'jane.smith@example.com', 'Arusha', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-14', 'active', NULL, NULL, NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(9, 'MBR-2026-0006', 'James', 'Mwita', 'male', '1992-03-10', NULL, '+255756123001', NULL, 'james.mwita@gmail.com', 'Mwanza', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-14', 'active', NULL, NULL, NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(11, 'MBR-2026-0008', 'Peter', 'John', 'male', '1995-11-05', NULL, '+255765432003', NULL, 'peter.john@outlook.com', 'Arusha', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-14', 'active', NULL, NULL, NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(12, 'MBR-2026-0009', 'Sarah', 'Francis', 'female', '1993-09-18', NULL, '+255784567004', NULL, 'sarah.francis@gmail.com', 'Mbeya', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-14', 'active', NULL, NULL, NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(13, 'MBR-2026-0010', 'Daniel', 'Charles', 'male', '1980-12-30', NULL, '+255756789005', NULL, 'daniel.c@yahoo.com', 'Dar es Salaam', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-14', 'active', NULL, NULL, NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(14, 'MBR-2026-0011', 'Agnes', 'Paul', 'female', '2000-04-25', NULL, '+255713456006', NULL, 'agnes.paul@gmail.com', 'Tanga', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-14', 'active', NULL, NULL, NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(15, 'MBR-2026-0012', 'Emmanuel', 'Simon', 'male', '1987-06-14', NULL, '+255767890007', NULL, 'emmanuel.s@outlook.com', 'Zanzibar', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-14', 'active', NULL, NULL, NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(16, 'MBR-2026-0013', 'Veronica', 'Peter', 'female', '1998-08-09', NULL, '+255789012008', NULL, 'veronica.p@gmail.com', 'Morogoro', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-14', 'active', NULL, NULL, NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(17, 'MBR-2026-0014', 'Joseph', 'Lucas', 'male', '1991-10-19', NULL, '+255714567009', NULL, 'joseph.lucas@yahoo.com', 'Dodoma', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-14', 'active', NULL, NULL, NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(18, 'MBR-2026-0015', 'Monica', 'Thomas', 'female', '1996-02-27', NULL, '+255768901010', NULL, 'monica.t@gmail.com', 'Arusha', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-14', 'active', NULL, NULL, NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(19, 'MBR-2026-0016', 'Richard', 'Michael', 'male', '1984-11-02', NULL, '+255756789011', NULL, 'richard.m@outlook.com', 'Mwanza', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-14', 'active', NULL, NULL, NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(20, 'MBR-2026-0017', 'Catherine', 'William', 'female', '1999-03-15', NULL, '+255790123012', NULL, 'catherine.w@gmail.com', 'Kilimanjaro', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-14', 'active', NULL, NULL, NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(21, 'MBR-2026-0018', 'Patrick', 'Martin', 'male', '1979-07-25', NULL, '+255715678013', NULL, 'patrick.m@yahoo.com', 'Dar es Salaam', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-14', 'active', NULL, NULL, NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(22, 'MBR-2026-0019', 'Beatrice', 'Daudi', 'female', '2001-12-03', NULL, '+255769012014', NULL, 'beatrice.d@gmail.com', 'Tanga', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-14', 'active', NULL, NULL, NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(23, 'MBR-2026-0020', 'Samuel', 'Augustine', 'male', '1993-05-17', NULL, '+255757890015', NULL, 'samuel.a@outlook.com', 'Iringa', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-14', 'active', NULL, NULL, NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(24, 'MBR-2026-0021', 'Flora', 'Emmanuel', 'female', '1989-09-08', NULL, '+255791234016', NULL, 'flora.e@gmail.com', 'Pwani', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-14', 'active', NULL, NULL, NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(25, 'MBR-2026-0022', 'Isaac', 'Peter', 'male', '1994-01-21', NULL, '+255716789017', NULL, 'isaac.p@yahoo.com', 'Shinyanga', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-14', 'active', NULL, NULL, NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(26, 'MBR-2026-0023', 'Lilian', 'George', 'female', '1997-06-10', NULL, '+255770123018', NULL, 'lilian.g@gmail.com', 'Ruvuma', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-14', 'active', NULL, NULL, NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(27, 'MBR-2026-0024', 'Mark', 'John', 'male', '1986-04-14', NULL, '+255758901019', NULL, 'mark.j@outlook.com', 'Kigoma', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-14', 'active', NULL, NULL, NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(28, 'MBR-2026-0025', 'Martha', 'Joseph', 'female', '2002-08-26', NULL, '+255792345020', NULL, 'martha.j@gmail.com', 'Singida', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-14', 'active', NULL, NULL, NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(29, 'MBR-2026-0026', 'Barnabas', 'Francis', 'male', '1983-12-07', NULL, '+255717890021', NULL, 'barnabas.f@yahoo.com', 'Dar es Salaam', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-14', 'active', NULL, NULL, NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(30, 'MBR-2026-0027', 'Rebeca', 'Charles', 'female', '1990-02-19', NULL, '+255771234022', NULL, 'rebeca.c@gmail.com', 'Lindi', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-14', 'active', NULL, NULL, NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(31, 'MBR-2026-0028', 'Stephen', 'Andrew', 'male', '1995-10-31', NULL, '+255759012023', NULL, 'stephen.a@outlook.com', 'Mtwara', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-14', 'active', NULL, NULL, NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(32, 'MBR-2026-0029', 'Ruth', 'Patrick', 'female', '1982-05-13', NULL, '+255793456024', NULL, 'ruth.p@gmail.com', 'Tabora', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-14', 'active', NULL, NULL, NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(33, 'MBR-2026-0030', 'Timothy', 'Simon', 'male', '1998-07-24', NULL, '+255718123025', NULL, 'timothy.s@yahoo.com', 'Morogoro', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-14', 'active', NULL, NULL, NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(34, 'MBR-2026-0031', 'Naomi', 'James', 'female', '2003-11-08', NULL, '+255772345026', NULL, 'naomi.j@gmail.com', 'Dar es Salaam', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-14', 'active', NULL, NULL, NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(35, 'MBR-2026-0032', 'Phillip', 'Daniel', 'male', '1985-09-16', NULL, '+255760456027', NULL, 'phillip.d@outlook.com', 'Arusha', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-14', 'active', NULL, NULL, NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(36, 'MBR-2026-0033', 'Esther', 'John', 'female', '1991-03-28', NULL, '+255794567028', NULL, 'esther.j@gmail.com', 'Tanga', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-14', 'active', NULL, NULL, NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(37, 'MBR-2026-0034', 'David', 'Samson', 'male', '1996-12-14', NULL, '+255719678029', NULL, 'david.s@yahoo.com', 'Zanzibar', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-14', 'active', NULL, NULL, NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(38, 'MBR-2026-0035', 'Hannah', 'Isaac', 'female', '1981-08-05', NULL, '+255773789030', NULL, 'hannah.i@gmail.com', 'Mbeya', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-14', 'active', NULL, NULL, NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(39, 'MBR-2026-0036', 'Simon', 'Luke', 'male', '1999-04-19', NULL, '+255761890031', NULL, 'simon.l@outlook.com', 'Dodoma', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-14', 'active', NULL, NULL, NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(40, 'MBR-2026-0037', 'Deborah', 'Paul', 'female', '2004-01-07', NULL, '+255795901032', NULL, 'deborah.p@gmail.com', 'Arusha', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-14', 'active', NULL, NULL, NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(41, 'MBR-2026-0038', 'Moses', 'John', 'male', '1992-06-23', NULL, '+255720012033', NULL, 'moses.j@yahoo.com', 'Mwanza', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-14', 'active', NULL, NULL, NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(42, 'MBR-2026-0039', 'Sarah', 'Matthew', 'female', '1987-10-11', NULL, '+255774123034', NULL, 'sarah.m@gmail.com', 'Tanga', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-14', 'active', NULL, NULL, NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(43, 'MBR-2026-0040', 'Abraham', 'Mark', 'male', '1994-02-28', NULL, '+255762234035', NULL, 'abraham.m@outlook.com', 'Dar es Salaam', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-14', 'active', NULL, NULL, NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(44, 'MBR-2026-0041', 'Rebecca', 'Luke', 'female', '2000-07-15', NULL, '+255796345036', NULL, 'rebecca.l@gmail.com', 'Kilimanjaro', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-14', 'active', NULL, NULL, NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(45, 'MBR-2026-0042', 'Nathan', 'Joseph', 'male', '1989-12-09', NULL, '+255721456037', NULL, 'nathan.j@yahoo.com', 'Pwani', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-14', 'active', NULL, NULL, NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(46, 'MBR-2026-0043', 'Elizabeth', 'David', 'female', '1995-04-03', NULL, '+255775567038', NULL, 'elizabeth.d@gmail.com', 'Mbeya', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-14', 'active', NULL, NULL, NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(47, 'MBR-2026-0044', 'Thomas', 'Richard', 'male', '1984-09-25', NULL, '+255763678039', NULL, 'thomas.r@outlook.com', 'Morogoro', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-14', 'active', NULL, NULL, NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(48, 'MBR-2026-0045', 'Mary', 'Joseph', 'female', '1997-01-18', NULL, '+255797789040', NULL, 'mary.j@gmail.com', 'Arusha', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-14', 'active', NULL, NULL, NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(49, 'MBR-2026-0046', 'John', 'Barnabas', 'male', '2001-08-29', NULL, '+255722890041', NULL, 'john.b@yahoo.com', 'Dar es Salaam', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-14', 'active', NULL, NULL, NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(50, 'MBR-2026-0047', 'Dorothy', 'Simon', 'female', '1990-05-07', NULL, '+255776901042', NULL, 'dorothy.s@gmail.com', 'Tabora', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-14', 'active', NULL, NULL, NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(51, 'MBR-2026-0048', 'Paul', 'George', 'male', '1993-11-20', NULL, '+255764012043', NULL, 'paul.g@outlook.com', 'Iringa', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-14', 'active', NULL, NULL, NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(52, 'MBR-2026-0049', 'Martha', 'Peter', 'female', '1986-03-12', NULL, '+255798123044', NULL, 'martha.p@gmail.com', 'Tanga', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-14', 'active', NULL, NULL, NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(53, 'MBR-2026-0050', 'Andrew', 'Thomas', 'male', '1999-06-25', NULL, '+255723234045', NULL, 'andrew.t@yahoo.com', 'Zanzibar', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-14', 'active', NULL, NULL, NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(54, 'MBR-2026-0051', 'Alice', 'John', 'female', '2002-10-14', NULL, '+255777345046', NULL, 'alice.j@gmail.com', 'Ruvuma', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-14', 'active', NULL, NULL, NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(55, 'MBR-2026-0052', 'Simon', 'James', 'male', '1988-02-07', NULL, '+255765456047', NULL, 'simon.j@outlook.com', 'Dodoma', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-14', 'active', NULL, NULL, NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(56, 'MBR-2026-0053', 'Susan', 'David', 'female', '1996-09-29', NULL, '+255799567048', NULL, 'susan.d@gmail.com', 'Mwanza', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-14', 'active', NULL, NULL, NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(57, 'MBR-2026-0054', 'Lucas', 'Paul', 'male', '1991-07-21', NULL, '+255724678049', NULL, 'lucas.p@yahoo.com', 'Arusha', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-14', 'active', NULL, NULL, NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(58, 'MBR-2026-0055', 'Lydia', 'Mark', 'female', '1983-12-15', NULL, '+255778789050', NULL, 'lydia.m@gmail.com', 'Dar es Salaam', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-14', 'active', NULL, NULL, NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(59, 'MBR-2026-0056', 'Victor', 'John', 'male', '2003-05-09', NULL, '+255766890051', NULL, 'victor.j@outlook.com', 'Kigoma', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-14', 'active', NULL, NULL, NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(60, 'MBR-2026-0057', 'Anna', 'Joseph', 'female', '1994-01-31', NULL, '+255700901052', NULL, 'anna.j@gmail.com', 'Mbeya', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-14', 'active', NULL, NULL, NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(61, 'MBR-2026-0058', 'Micheal', 'Francis', 'male', '1980-09-04', NULL, '+255725012053', NULL, 'micheal.f@yahoo.com', 'Tabora', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-14', 'active', NULL, NULL, NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(62, 'MBR-2026-0059', 'Phoebe', 'Andrew', 'female', '1998-04-17', NULL, '+255779123054', NULL, 'phoebe.a@gmail.com', 'Pwani', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-14', 'active', NULL, NULL, NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(63, 'MBR-2026-0060', 'Elijah', 'Matthew', 'male', '2000-08-26', NULL, '+255767234055', NULL, 'elijah.m@outlook.com', 'Morogoro', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-14', 'active', NULL, NULL, NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(64, 'MBR-2026-0061', 'Joy', 'Paul', 'female', '1992-12-08', NULL, '+255701345056', NULL, 'joy.p@gmail.com', 'Tanga', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-14', 'active', NULL, NULL, NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(65, 'MBR-2026-0062', 'Zachariah', 'John', 'male', '1987-03-22', NULL, '+255726456057', NULL, 'zachariah.j@yahoo.com', 'Arusha', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-14', 'active', NULL, NULL, NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(66, 'MBR-2026-0063', 'Ruth', 'Samuel', 'female', '1995-06-14', NULL, '+255780567058', NULL, 'ruth.s@gmail.com', 'Dodoma', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-14', 'active', NULL, NULL, NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(67, 'MBR-2026-0064', 'Jared', 'Simon', 'male', '2001-11-03', NULL, '+255768678059', NULL, 'jared.s@outlook.com', 'Kilimanjaro', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-14', 'active', NULL, NULL, NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(68, 'MBR-2026-0065', 'Deborah', 'James', 'female', '1989-02-19', NULL, '+255702789060', NULL, 'deborah.j@gmail.com', 'Mwanza', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-14', 'active', NULL, NULL, NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(69, 'MBR-2026-0066', 'Solomon', 'David', 'male', '1997-10-10', NULL, '+255727890061', NULL, 'solomon.d@yahoo.com', 'Dar es Salaam', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-14', 'active', NULL, NULL, NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(70, 'MBR-2026-0067', 'Esther', 'Michael', 'female', '2004-04-28', NULL, '+255781901062', NULL, 'esther.m@gmail.com', 'Iringa', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-14', 'active', NULL, NULL, NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(71, 'MBR-2026-0068', 'Noah', 'Peter', 'male', '1993-08-16', NULL, '+255769012063', NULL, 'noah.p@outlook.com', 'Tanga', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-14', 'active', NULL, NULL, NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(72, 'MBR-2026-0069', 'Miriam', 'Joseph', 'female', '1985-01-05', NULL, '+255703123064', NULL, 'miriam.j@gmail.com', 'Arusha', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-14', 'active', NULL, NULL, NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(73, 'MBR-2026-0070', 'Caleb', 'John', 'male', '1999-07-19', NULL, '+255728234065', NULL, 'caleb.j@yahoo.com', 'Zanzibar', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-14', 'active', NULL, NULL, NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(74, 'MBR-2026-0071', 'Naomi', 'Thomas', 'female', '2002-12-12', NULL, '+255782345066', NULL, 'naomi.t@gmail.com', 'Mbeya', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-14', 'active', NULL, NULL, NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(75, 'MBR-2026-0072', 'Job', 'Francis', 'male', '1991-04-25', NULL, '+255770456067', NULL, 'job.f@outlook.com', 'Dodoma', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-14', 'active', NULL, NULL, NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(76, 'MBR-2026-0073', 'Rachel', 'Andrew', 'female', '1996-09-08', NULL, '+255704567068', NULL, 'rachel.a@gmail.com', 'Pwani', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-14', 'active', NULL, NULL, NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(77, 'MBR-2026-0074', 'Joel', 'Simon', 'male', '1982-06-30', NULL, '+255729678069', NULL, 'joel.s@yahoo.com', 'Morogoro', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-14', 'active', NULL, NULL, NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(78, 'MBR-2026-0075', 'Hannah', 'Mark', 'female', '2000-03-14', NULL, '+255783789070', NULL, 'hannah.m@gmail.com', 'Tanga', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-14', 'active', NULL, NULL, NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(79, 'MBR-2026-0076', 'Amos', 'Peter', 'male', '1994-11-27', NULL, '+255771890071', NULL, 'amos.p@outlook.com', 'Arusha', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-14', 'active', NULL, NULL, NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(80, 'MBR-2026-0077', 'Judith', 'John', 'female', '1988-08-03', NULL, '+255705901072', NULL, 'judith.j@gmail.com', 'Dar es Salaam', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-14', 'active', NULL, NULL, NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(81, 'MBR-2026-0078', 'Gideon', 'Paul', 'male', '1997-02-20', NULL, '+255730012073', NULL, 'gideon.p@yahoo.com', 'Ruvuma', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-14', 'active', NULL, NULL, NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(82, 'MBR-2026-0079', 'Tabitha', 'James', 'female', '2003-10-16', NULL, '+255784123074', NULL, 'tabitha.j@gmail.com', 'Kigoma', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-14', 'active', NULL, NULL, NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(83, 'MBR-2026-0080', 'Titus', 'David', 'male', '1990-05-09', NULL, '+255772234075', NULL, 'titus.d@outlook.com', 'Tabora', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-14', 'active', NULL, NULL, NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(84, 'MBR-2026-0081', 'Naomi', 'Simon', 'female', '1984-09-23', NULL, '+255706345076', NULL, 'naomi.s@gmail.com', 'Mwanza', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-14', 'active', NULL, NULL, NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(85, 'MBR-2026-0082', 'Samson', 'John', 'male', '1998-12-04', NULL, '+255731456077', NULL, 'samson.j@yahoo.com', 'Dodoma', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-14', 'active', NULL, NULL, NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(86, 'MBR-2026-0083', 'Dinah', 'Mark', 'female', '2001-07-28', NULL, '+255785567078', NULL, 'dinah.m@gmail.com', 'Arusha', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-14', 'active', NULL, NULL, NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(87, 'MBR-2026-0084', 'Reuben', 'Joseph', 'male', '1995-03-17', NULL, '+255773678079', NULL, 'reuben.j@outlook.com', 'Pwani', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-14', 'active', NULL, NULL, NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(88, 'MBR-2026-0085', 'Leah', 'Peter', 'female', '1986-11-10', NULL, '+255707789080', NULL, 'leah.p@gmail.com', 'Tanga', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-14', 'active', NULL, NULL, NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(89, 'MBR-2026-0086', 'Ezekiel', 'Andrew', 'male', '1992-06-05', NULL, '+255732890081', NULL, 'ezeekiel.a@yahoo.com', 'Zanzibar', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-14', 'active', NULL, NULL, NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(90, 'MBR-2026-0087', 'Martha', 'Paul', 'female', '1999-01-22', NULL, '+255786901082', NULL, 'martha.p@gmail.com', 'Kilimanjaro', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-14', 'active', NULL, NULL, NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(91, 'MBR-2026-0088', 'Gad', 'Simon', 'male', '2004-09-14', NULL, '+255774012083', NULL, 'gad.s@outlook.com', 'Mbeya', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-14', 'active', NULL, NULL, NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(92, 'MBR-2026-0089', 'Sarah', 'David', 'female', '1993-04-07', NULL, '+255708123084', NULL, 'sarah.d@gmail.com', 'Dar es Salaam', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-14', 'active', NULL, NULL, NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(93, 'MBR-2026-0090', 'Aaron', 'John', 'male', '1981-08-19', NULL, '+255733234085', NULL, 'aaron.j@yahoo.com', 'Morogoro', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-14', 'active', NULL, NULL, NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(94, 'MBR-2026-0091', 'Hagar', 'Francis', 'female', '1996-12-01', NULL, '+255787345086', NULL, 'hagar.f@gmail.com', 'Tanga', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-14', 'active', NULL, NULL, NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(95, 'MBR-2026-0092', 'Asher', 'Thomas', 'male', '2000-05-24', NULL, '+255775456087', NULL, 'asher.t@outlook.com', 'Arusha', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-14', 'active', NULL, NULL, NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(96, 'MBR-2026-0093', 'Keturah', 'James', 'female', '1989-10-13', NULL, '+255709567088', NULL, 'keturah.j@gmail.com', 'Dodoma', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-14', 'active', NULL, NULL, NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(97, 'MBR-2026-0094', 'Dan', 'Joseph', 'male', '1994-03-08', NULL, '+255734678089', NULL, 'dan.j@yahoo.com', 'Mwanza', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-14', 'active', NULL, NULL, NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(98, 'MBR-2026-0095', 'Priscilla', 'Mark', 'female', '1997-07-30', NULL, '+255788789090', NULL, 'priscilla.m@gmail.com', 'Ruvuma', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-14', 'active', NULL, NULL, NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(99, 'MBR-2026-0096', 'Enoch', 'Peter', 'male', '2002-01-18', NULL, '+255776890091', NULL, 'enoch.p@outlook.com', 'Tabora', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-14', 'active', NULL, NULL, NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(100, 'MBR-2026-0097', 'Rebecca', 'Andrew', 'female', '1991-09-26', NULL, '+255710901092', NULL, 'rebecca.a@gmail.com', 'Arusha', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-14', 'active', NULL, NULL, NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(101, 'MBR-2026-0098', 'Nehemiah', 'Paul', 'male', '1987-05-11', NULL, '+255735012093', NULL, 'nehemiah.p@yahoo.com', 'Dar es Salaam', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-14', 'active', NULL, NULL, NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(102, 'MBR-2026-0099', 'Susanna', 'John', 'female', '1999-12-04', NULL, '+255789123094', NULL, 'susanna.j@gmail.com', 'Kigoma', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-14', 'active', NULL, NULL, NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(103, 'MBR-2026-0100', 'Malachi', 'Simon', 'male', '2003-08-22', NULL, '+255777234095', NULL, 'malachi.s@outlook.com', 'Mbeya', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-14', 'active', NULL, NULL, NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(104, 'MBR-2026-0101', 'Jemima', 'David', 'female', '1995-02-15', NULL, '+255711345096', NULL, 'jemima.d@gmail.com', 'Pwani', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-14', 'active', NULL, NULL, NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(105, 'MBR-2026-0102', 'Obadiah', 'Mark', 'male', '1983-10-28', NULL, '+255736456097', NULL, 'obadiah.m@yahoo.com', 'Tanga', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-14', 'active', NULL, NULL, NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(106, 'MBR-2026-0103', 'Zipporah', 'James', 'female', '2001-06-19', NULL, '+255790567098', NULL, 'zipporah.j@gmail.com', 'Dodoma', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-14', 'active', NULL, NULL, NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(107, 'MBR-2026-0104', 'Uriah', 'Peter', 'male', '1998-04-09', NULL, '+255778678099', NULL, 'uriah.p@outlook.com', 'Arusha', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-14', 'active', NULL, NULL, NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(108, 'MBR-2026-0105', 'Hadassah', 'John', 'female', '2004-11-23', NULL, '+255712789100', NULL, 'hadassah.j@gmail.com', 'Dar es Salaam', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-14', 'active', NULL, NULL, NULL, '2026-04-14 11:40:16', '2026-04-14 11:40:16'),
(211, 'MBR-2026-0208', 'Baraka', 'John', 'male', '1993-03-12', NULL, '+255756789101', NULL, 'baraka.john@gmail.com', 'Dar es Salaam', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-14', 'active', NULL, NULL, NULL, '2026-04-14 11:42:14', '2026-04-14 11:42:14'),
(212, 'MBR-2026-0209', 'Upendo', 'Charles', 'female', '1995-07-24', NULL, '+255712345102', NULL, 'upendo.c@yahoo.com', 'Arusha', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-14', 'active', NULL, NULL, NULL, '2026-04-14 11:42:14', '2026-04-14 11:42:14'),
(213, 'MBR-2026-0210', 'Juma', 'Hassan', 'male', '1990-11-05', NULL, '+255767890103', NULL, 'juma.hassan@outlook.com', 'Zanzibar', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-14', 'active', NULL, NULL, NULL, '2026-04-14 11:42:14', '2026-04-14 11:42:14'),
(214, 'MBR-2026-0211', 'Neema', 'Peter', 'female', '1998-09-18', NULL, '+255784567104', NULL, 'neema.peter@gmail.com', 'Mbeya', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-14', 'active', NULL, NULL, NULL, '2026-04-14 11:42:14', '2026-04-14 11:42:14'),
(215, 'MBR-2026-0212', 'Hamza', 'Iddi', 'male', '1992-04-30', NULL, '+255756123105', NULL, 'hamza.iddi@yahoo.com', 'Tanga', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-14', 'active', NULL, NULL, NULL, '2026-04-14 11:42:14', '2026-04-14 11:42:14'),
(216, 'MBR-2026-0213', 'Mwanaidi', 'Omar', 'female', '2001-01-25', NULL, '+255789012106', NULL, 'mwanaidi.omar@gmail.com', 'Dodoma', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-14', 'active', NULL, NULL, NULL, '2026-04-14 11:42:14', '2026-04-14 11:42:14'),
(217, 'MBR-2026-0214', 'Ramadhan', 'Salum', 'male', '1988-08-14', NULL, '+255713456107', NULL, 'ramadhan.salum@outlook.com', 'Morogoro', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-14', 'active', NULL, NULL, NULL, '2026-04-14 11:42:14', '2026-04-14 11:42:14'),
(218, 'MBR-2026-0215', 'Rehema', 'Kassim', 'female', '1996-12-09', NULL, '+255768901108', NULL, 'rehema.kassim@gmail.com', 'Mwanza', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-14', 'active', NULL, NULL, NULL, '2026-04-14 11:42:14', '2026-04-14 11:42:14'),
(219, 'MBR-2026-0216', 'Salim', 'Abdallah', 'male', '1994-06-20', NULL, '+255756789109', NULL, 'salim.abdallah@yahoo.com', 'Iringa', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-14', 'active', NULL, NULL, NULL, '2026-04-14 11:42:14', '2026-04-14 11:42:14'),
(220, 'MBR-2026-0217', 'Zainabu', 'Rashid', 'female', '2000-03-17', NULL, '+255790123110', NULL, 'zainabu.rashid@gmail.com', 'Arusha', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-14', 'active', NULL, NULL, NULL, '2026-04-14 11:42:14', '2026-04-14 11:42:14'),
(221, 'MBR-2026-0221', 'John', 'Doe', 'male', NULL, NULL, '255712345678', NULL, 'john@example.com', 'Dar es Salaam', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-15', 'active', NULL, 1, 1, '2026-04-15 19:52:03', '2026-04-15 19:52:03'),
(222, 'MBR-2026-0222', 'Jane', 'Smith', 'female', NULL, NULL, '255787654321', NULL, 'jane.smith@example.com', 'Arusha', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-15', 'active', NULL, 1, 1, '2026-04-15 19:52:03', '2026-04-15 19:52:03'),
(223, 'MBR-2026-0223', 'James', 'Mwita', 'male', NULL, NULL, '255756123001', NULL, 'james.mwita@gmail.com', 'Mwanza', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-15', 'active', NULL, 1, 1, '2026-04-15 19:52:03', '2026-04-15 19:52:03'),
(224, 'MBR-2026-0224', 'Grace', 'Joseph', 'female', NULL, NULL, '255712345002', NULL, 'grace.joseph@yahoo.com', 'Dodoma', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-15', 'active', NULL, 1, 1, '2026-04-15 19:52:03', '2026-04-15 19:52:03'),
(225, 'MBR-2026-0225', 'Peter', 'John', 'male', NULL, NULL, '255765432003', NULL, 'peter.john@outlook.com', 'Arusha', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-15', 'active', NULL, 1, 1, '2026-04-15 19:52:03', '2026-04-15 19:52:03'),
(226, 'MBR-2026-0226', 'Sarah', 'Francis', 'female', NULL, NULL, '255784567004', NULL, 'sarah.francis@gmail.com', 'Mbeya', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-15', 'active', NULL, 1, 1, '2026-04-15 19:52:03', '2026-04-15 19:52:03'),
(227, 'MBR-2026-0227', 'Daniel', 'Charles', 'male', NULL, NULL, '255756789005', NULL, 'daniel.c@yahoo.com', 'Dar es Salaam', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-15', 'active', NULL, 1, 1, '2026-04-15 19:52:03', '2026-04-15 19:52:03'),
(228, 'MBR-2026-0228', 'Agnes', 'Paul', 'female', NULL, NULL, '255713456006', NULL, 'agnes.paul@gmail.com', 'Tanga', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-15', 'active', NULL, 1, 1, '2026-04-15 19:52:03', '2026-04-15 19:52:03'),
(229, 'MBR-2026-0229', 'Emmanuel', 'Simon', 'male', NULL, NULL, '255767890007', NULL, 'emmanuel.s@outlook.com', 'Zanzibar', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-15', 'active', NULL, 1, 1, '2026-04-15 19:52:03', '2026-04-15 19:52:03'),
(230, 'MBR-2026-0230', 'Veronica', 'Peter', 'female', NULL, NULL, '255789012008', NULL, 'veronica.p@gmail.com', 'Morogoro', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-15', 'active', NULL, 1, 1, '2026-04-15 19:52:03', '2026-04-15 19:52:03'),
(231, 'MBR-2026-0231', 'Joseph', 'Lucas', 'male', NULL, NULL, '255714567009', NULL, 'joseph.lucas@yahoo.com', 'Dodoma', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-15', 'active', NULL, 1, 1, '2026-04-15 19:52:03', '2026-04-15 19:52:03'),
(232, 'MBR-2026-0232', 'Monica', 'Thomas', 'female', NULL, NULL, '255768901010', NULL, 'monica.t@gmail.com', 'Arusha', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-15', 'active', NULL, 1, 1, '2026-04-15 19:52:03', '2026-04-15 19:52:03'),
(233, 'MBR-2026-0233', 'Richard', 'Michael', 'male', NULL, NULL, '255756789011', NULL, 'richard.m@outlook.com', 'Mwanza', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-15', 'active', NULL, 1, 1, '2026-04-15 19:52:03', '2026-04-15 19:52:03'),
(234, 'MBR-2026-0234', 'Catherine', 'William', 'female', NULL, NULL, '255790123012', NULL, 'catherine.w@gmail.com', 'Kilimanjaro', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-15', 'active', NULL, 1, 1, '2026-04-15 19:52:03', '2026-04-15 19:52:03'),
(235, 'MBR-2026-0235', 'Patrick', 'Martin', 'male', NULL, NULL, '255715678013', NULL, 'patrick.m@yahoo.com', 'Dar es Salaam', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-15', 'active', NULL, 1, 1, '2026-04-15 19:52:03', '2026-04-15 19:52:03'),
(236, 'MBR-2026-0236', 'Beatrice', 'Daudi', 'female', NULL, NULL, '255769012014', NULL, 'beatrice.d@gmail.com', 'Tanga', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-15', 'active', NULL, 1, 1, '2026-04-15 19:52:03', '2026-04-15 19:52:03'),
(237, 'MBR-2026-0237', 'Samuel', 'Augustine', 'male', NULL, NULL, '255757890015', NULL, 'samuel.a@outlook.com', 'Iringa', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-15', 'active', NULL, 1, 1, '2026-04-15 19:52:03', '2026-04-15 19:52:03'),
(238, 'MBR-2026-0238', 'Flora', 'Emmanuel', 'female', NULL, NULL, '255791234016', NULL, 'flora.e@gmail.com', 'Pwani', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-15', 'active', NULL, 1, 1, '2026-04-15 19:52:03', '2026-04-15 19:52:03'),
(239, 'MBR-2026-0239', 'Isaac', 'Peter', 'male', NULL, NULL, '255716789017', NULL, 'isaac.p@yahoo.com', 'Shinyanga', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-15', 'active', NULL, 1, 1, '2026-04-15 19:52:03', '2026-04-15 19:52:03'),
(240, 'MBR-2026-0240', 'Lilian', 'George', 'female', NULL, NULL, '255770123018', NULL, 'lilian.g@gmail.com', 'Ruvuma', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-15', 'active', NULL, 1, 1, '2026-04-15 19:52:03', '2026-04-15 19:52:03'),
(241, 'MBR-2026-0241', 'Mark', 'John', 'male', NULL, NULL, '255758901019', NULL, 'mark.j@outlook.com', 'Kigoma', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-15', 'active', NULL, 1, 1, '2026-04-15 19:52:03', '2026-04-15 19:52:03'),
(242, 'MBR-2026-0242', 'Martha', 'Joseph', 'female', NULL, NULL, '255792345020', NULL, 'martha.j@gmail.com', 'Singida', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-15', 'active', NULL, 1, 1, '2026-04-15 19:52:03', '2026-04-15 19:52:03'),
(243, 'MBR-2026-0243', 'Barnabas', 'Francis', 'male', NULL, NULL, '255717890021', NULL, 'barnabas.f@yahoo.com', 'Dar es Salaam', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-15', 'active', NULL, 1, 1, '2026-04-15 19:52:03', '2026-04-15 19:52:03'),
(244, 'MBR-2026-0244', 'Rebeca', 'Charles', 'female', NULL, NULL, '255771234022', NULL, 'rebeca.c@gmail.com', 'Lindi', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-15', 'active', NULL, 1, 1, '2026-04-15 19:52:03', '2026-04-15 19:52:03'),
(245, 'MBR-2026-0245', 'Stephen', 'Andrew', 'male', NULL, NULL, '255759012023', NULL, 'stephen.a@outlook.com', 'Mtwara', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-15', 'active', NULL, 1, 1, '2026-04-15 19:52:03', '2026-04-15 19:52:03'),
(246, 'MBR-2026-0246', 'Ruth', 'Patrick', 'female', NULL, NULL, '255793456024', NULL, 'ruth.p@gmail.com', 'Tabora', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-15', 'active', NULL, 1, 1, '2026-04-15 19:52:03', '2026-04-15 19:52:03'),
(247, 'MBR-2026-0247', 'Timothy', 'Simon', 'male', NULL, NULL, '255718123025', NULL, 'timothy.s@yahoo.com', 'Morogoro', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-15', 'active', NULL, 1, 1, '2026-04-15 19:52:03', '2026-04-15 19:52:03'),
(248, 'MBR-2026-0248', 'Naomi', 'James', 'female', NULL, NULL, '255772345026', NULL, 'naomi.j@gmail.com', 'Dar es Salaam', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-15', 'active', NULL, 1, 1, '2026-04-15 19:52:03', '2026-04-15 19:52:03'),
(249, 'MBR-2026-0249', 'Phillip', 'Daniel', 'male', NULL, NULL, '255760456027', NULL, 'phillip.d@outlook.com', 'Arusha', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-15', 'active', NULL, 1, 1, '2026-04-15 19:52:03', '2026-04-15 19:52:03'),
(250, 'MBR-2026-0250', 'Esther', 'John', 'female', NULL, NULL, '255794567028', NULL, 'esther.j@gmail.com', 'Tanga', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-15', 'active', NULL, 1, 1, '2026-04-15 19:52:03', '2026-04-15 19:52:03'),
(251, 'MBR-2026-0251', 'David', 'Samson', 'male', NULL, NULL, '255719678029', NULL, 'david.s@yahoo.com', 'Zanzibar', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-15', 'active', NULL, 1, 1, '2026-04-15 19:52:03', '2026-04-15 19:52:03'),
(252, 'MBR-2026-0252', 'Hannah', 'Isaac', 'female', NULL, NULL, '255773789030', NULL, 'hannah.i@gmail.com', 'Mbeya', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-15', 'active', NULL, 1, 1, '2026-04-15 19:52:03', '2026-04-15 19:52:03'),
(253, 'MBR-2026-0253', 'Simon', 'Luke', 'male', NULL, NULL, '255761890031', NULL, 'simon.l@outlook.com', 'Dodoma', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-15', 'active', NULL, 1, 1, '2026-04-15 19:52:03', '2026-04-15 19:52:03'),
(254, 'MBR-2026-0254', 'Deborah', 'Paul', 'female', NULL, NULL, '255795901032', NULL, 'deborah.p@gmail.com', 'Arusha', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-15', 'active', NULL, 1, 1, '2026-04-15 19:52:03', '2026-04-15 19:52:03'),
(255, 'MBR-2026-0255', 'Moses', 'John', 'male', NULL, NULL, '255720012033', NULL, 'moses.j@yahoo.com', 'Mwanza', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-15', 'active', NULL, 1, 1, '2026-04-15 19:52:03', '2026-04-15 19:52:03'),
(256, 'MBR-2026-0256', 'Sarah', 'Matthew', 'female', NULL, NULL, '255774123034', NULL, 'sarah.m@gmail.com', 'Tanga', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-15', 'active', NULL, 1, 1, '2026-04-15 19:52:03', '2026-04-15 19:52:03'),
(257, 'MBR-2026-0257', 'Abraham', 'Mark', 'male', NULL, NULL, '255762234035', NULL, 'abraham.m@outlook.com', 'Dar es Salaam', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-15', 'active', NULL, 1, 1, '2026-04-15 19:52:03', '2026-04-15 19:52:03'),
(258, 'MBR-2026-0258', 'Rebecca', 'Luke', 'female', NULL, NULL, '255796345036', NULL, 'rebecca.l@gmail.com', 'Kilimanjaro', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-15', 'active', NULL, 1, 1, '2026-04-15 19:52:03', '2026-04-15 19:52:03'),
(259, 'MBR-2026-0259', 'Nathan', 'Joseph', 'male', NULL, NULL, '255721456037', NULL, 'nathan.j@yahoo.com', 'Pwani', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-15', 'active', NULL, 1, 1, '2026-04-15 19:52:03', '2026-04-15 19:52:03'),
(260, 'MBR-2026-0260', 'Elizabeth', 'David', 'female', NULL, NULL, '255775567038', NULL, 'elizabeth.d@gmail.com', 'Mbeya', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-15', 'active', NULL, 1, 1, '2026-04-15 19:52:03', '2026-04-15 19:52:03'),
(261, 'MBR-2026-0261', 'Thomas', 'Richard', 'male', NULL, NULL, '255763678039', NULL, 'thomas.r@outlook.com', 'Morogoro', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-15', 'active', NULL, 1, 1, '2026-04-15 19:52:03', '2026-04-15 19:52:03'),
(262, 'MBR-2026-0262', 'Mary', 'Joseph', 'female', NULL, NULL, '255797789040', NULL, 'mary.j@gmail.com', 'Arusha', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-15', 'active', NULL, 1, 1, '2026-04-15 19:52:03', '2026-04-15 19:52:03'),
(263, 'MBR-2026-0263', 'John', 'Barnabas', 'male', NULL, NULL, '255722890041', NULL, 'john.b@yahoo.com', 'Dar es Salaam', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-15', 'active', NULL, 1, 1, '2026-04-15 19:52:03', '2026-04-15 19:52:03'),
(264, 'MBR-2026-0264', 'Dorothy', 'Simon', 'female', NULL, NULL, '255776901042', NULL, 'dorothy.s@gmail.com', 'Tabora', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-15', 'active', NULL, 1, 1, '2026-04-15 19:52:03', '2026-04-15 19:52:03'),
(265, 'MBR-2026-0265', 'Paul', 'George', 'male', NULL, NULL, '255764012043', NULL, 'paul.g@outlook.com', 'Iringa', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-15', 'active', NULL, 1, 1, '2026-04-15 19:52:03', '2026-04-15 19:52:03'),
(266, 'MBR-2026-0266', 'Martha', 'Peter', 'female', NULL, NULL, '255798123044', NULL, 'martha.p@gmail.com', 'Tanga', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-15', 'active', NULL, 1, 1, '2026-04-15 19:52:03', '2026-04-15 19:52:03'),
(267, 'MBR-2026-0267', 'Andrew', 'Thomas', 'male', NULL, NULL, '255723234045', NULL, 'andrew.t@yahoo.com', 'Zanzibar', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-15', 'active', NULL, 1, 1, '2026-04-15 19:52:03', '2026-04-15 19:52:03'),
(268, 'MBR-2026-0268', 'Alice', 'John', 'female', NULL, NULL, '255777345046', NULL, 'alice.j@gmail.com', 'Ruvuma', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-15', 'active', NULL, 1, 1, '2026-04-15 19:52:03', '2026-04-15 19:52:03'),
(269, 'MBR-2026-0269', 'Simon', 'James', 'male', NULL, NULL, '255765456047', NULL, 'simon.j@outlook.com', 'Dodoma', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-15', 'active', NULL, 1, 1, '2026-04-15 19:52:03', '2026-04-15 19:52:03'),
(270, 'MBR-2026-0270', 'Susan', 'David', 'female', NULL, NULL, '255799567048', NULL, 'susan.d@gmail.com', 'Mwanza', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-15', 'active', NULL, 1, 1, '2026-04-15 19:52:03', '2026-04-15 19:52:03'),
(271, 'MBR-2026-0271', 'Lucas', 'Paul', 'male', NULL, NULL, '255724678049', NULL, 'lucas.p@yahoo.com', 'Arusha', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-15', 'active', NULL, 1, 1, '2026-04-15 19:52:03', '2026-04-15 19:52:03'),
(272, 'MBR-2026-0272', 'Lydia', 'Mark', 'female', NULL, NULL, '255778789050', NULL, 'lydia.m@gmail.com', 'Dar es Salaam', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-15', 'active', NULL, 1, 1, '2026-04-15 19:52:03', '2026-04-15 19:52:03'),
(273, 'MBR-2026-0273', 'Victor', 'John', 'male', NULL, NULL, '255766890051', NULL, 'victor.j@outlook.com', 'Kigoma', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-15', 'active', NULL, 1, 1, '2026-04-15 19:52:03', '2026-04-15 19:52:03'),
(274, 'MBR-2026-0274', 'Anna', 'Joseph', 'female', NULL, NULL, '255700901052', NULL, 'anna.j@gmail.com', 'Mbeya', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-15', 'active', NULL, 1, 1, '2026-04-15 19:52:03', '2026-04-15 19:52:03'),
(275, 'MBR-2026-0275', 'Micheal', 'Francis', 'male', NULL, NULL, '255725012053', NULL, 'micheal.f@yahoo.com', 'Tabora', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-15', 'active', NULL, 1, 1, '2026-04-15 19:52:03', '2026-04-15 19:52:03'),
(276, 'MBR-2026-0276', 'Phoebe', 'Andrew', 'female', NULL, NULL, '255779123054', NULL, 'phoebe.a@gmail.com', 'Pwani', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-15', 'active', NULL, 1, 1, '2026-04-15 19:52:03', '2026-04-15 19:52:03'),
(277, 'MBR-2026-0277', 'Elijah', 'Matthew', 'male', NULL, NULL, '255767234055', NULL, 'elijah.m@outlook.com', 'Morogoro', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-15', 'active', NULL, 1, 1, '2026-04-15 19:52:03', '2026-04-15 19:52:03'),
(278, 'MBR-2026-0278', 'Joy', 'Paul', 'female', NULL, NULL, '255701345056', NULL, 'joy.p@gmail.com', 'Tanga', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-15', 'active', NULL, 1, 1, '2026-04-15 19:52:03', '2026-04-15 19:52:03'),
(279, 'MBR-2026-0279', 'Zachariah', 'John', 'male', NULL, NULL, '255726456057', NULL, 'zachariah.j@yahoo.com', 'Arusha', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-15', 'active', NULL, 1, 1, '2026-04-15 19:52:03', '2026-04-15 19:52:03'),
(280, 'MBR-2026-0280', 'Ruth', 'Samuel', 'female', NULL, NULL, '255780567058', NULL, 'ruth.s@gmail.com', 'Dodoma', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-15', 'active', NULL, 1, 1, '2026-04-15 19:52:03', '2026-04-15 19:52:03'),
(281, 'MBR-2026-0281', 'Jared', 'Simon', 'male', NULL, NULL, '255768678059', NULL, 'jared.s@outlook.com', 'Kilimanjaro', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-15', 'active', NULL, 1, 1, '2026-04-15 19:52:03', '2026-04-15 19:52:03'),
(282, 'MBR-2026-0282', 'Deborah', 'James', 'female', NULL, NULL, '255702789060', NULL, 'deborah.j@gmail.com', 'Mwanza', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-15', 'active', NULL, 1, 1, '2026-04-15 19:52:03', '2026-04-15 19:52:03'),
(283, 'MBR-2026-0283', 'Solomon', 'David', 'male', NULL, NULL, '255727890061', NULL, 'solomon.d@yahoo.com', 'Dar es Salaam', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-15', 'active', NULL, 1, 1, '2026-04-15 19:52:03', '2026-04-15 19:52:03'),
(284, 'MBR-2026-0284', 'Esther', 'Michael', 'female', NULL, NULL, '255781901062', NULL, 'esther.m@gmail.com', 'Iringa', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-15', 'active', NULL, 1, 1, '2026-04-15 19:52:03', '2026-04-15 19:52:03'),
(285, 'MBR-2026-0285', 'Noah', 'Peter', 'male', NULL, NULL, '255769012063', NULL, 'noah.p@outlook.com', 'Tanga', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-15', 'active', NULL, 1, 1, '2026-04-15 19:52:03', '2026-04-15 19:52:03'),
(286, 'MBR-2026-0286', 'Miriam', 'Joseph', 'female', NULL, NULL, '255703123064', NULL, 'miriam.j@gmail.com', 'Arusha', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-15', 'active', NULL, 1, 1, '2026-04-15 19:52:03', '2026-04-15 19:52:03'),
(287, 'MBR-2026-0287', 'Caleb', 'John', 'male', NULL, NULL, '255728234065', NULL, 'caleb.j@yahoo.com', 'Zanzibar', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-15', 'active', NULL, 1, 1, '2026-04-15 19:52:03', '2026-04-15 19:52:03'),
(288, 'MBR-2026-0288', 'Naomi', 'Thomas', 'female', NULL, NULL, '255782345066', NULL, 'naomi.t@gmail.com', 'Mbeya', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-15', 'active', NULL, 1, 1, '2026-04-15 19:52:03', '2026-04-15 19:52:03'),
(289, 'MBR-2026-0289', 'Job', 'Francis', 'male', NULL, NULL, '255770456067', NULL, 'job.f@outlook.com', 'Dodoma', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-15', 'active', NULL, 1, 1, '2026-04-15 19:52:03', '2026-04-15 19:52:03'),
(290, 'MBR-2026-0290', 'Rachel', 'Andrew', 'female', NULL, NULL, '255704567068', NULL, 'rachel.a@gmail.com', 'Pwani', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-15', 'active', NULL, 1, 1, '2026-04-15 19:52:03', '2026-04-15 19:52:03'),
(291, 'MBR-2026-0291', 'Joel', 'Simon', 'male', NULL, NULL, '255729678069', NULL, 'joel.s@yahoo.com', 'Morogoro', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-15', 'active', NULL, 1, 1, '2026-04-15 19:52:03', '2026-04-15 19:52:03'),
(292, 'MBR-2026-0292', 'Hannah', 'Mark', 'female', NULL, NULL, '255783789070', NULL, 'hannah.m@gmail.com', 'Tanga', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-15', 'active', NULL, 1, 1, '2026-04-15 19:52:03', '2026-04-15 19:52:03'),
(293, 'MBR-2026-0293', 'Amos', 'Peter', 'male', NULL, NULL, '255771890071', NULL, 'amos.p@outlook.com', 'Arusha', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-15', 'active', NULL, 1, 1, '2026-04-15 19:52:03', '2026-04-15 19:52:03'),
(294, 'MBR-2026-0294', 'Judith', 'John', 'female', NULL, NULL, '255705901072', NULL, 'judith.j@gmail.com', 'Dar es Salaam', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-15', 'active', NULL, 1, 1, '2026-04-15 19:52:03', '2026-04-15 19:52:03'),
(295, 'MBR-2026-0295', 'Gideon', 'Paul', 'male', NULL, NULL, '255730012073', NULL, 'gideon.p@yahoo.com', 'Ruvuma', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-15', 'active', NULL, 1, 1, '2026-04-15 19:52:03', '2026-04-15 19:52:03'),
(296, 'MBR-2026-0296', 'Tabitha', 'James', 'female', NULL, NULL, '255784123074', NULL, 'tabitha.j@gmail.com', 'Kigoma', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-15', 'active', NULL, 1, 1, '2026-04-15 19:52:03', '2026-04-15 19:52:03'),
(297, 'MBR-2026-0297', 'Titus', 'David', 'male', NULL, NULL, '255772234075', NULL, 'titus.d@outlook.com', 'Tabora', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-15', 'active', NULL, 1, 1, '2026-04-15 19:52:03', '2026-04-15 19:52:03'),
(298, 'MBR-2026-0298', 'Naomi', 'Simon', 'female', NULL, NULL, '255706345076', NULL, 'naomi.s@gmail.com', 'Mwanza', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-15', 'active', NULL, 1, 1, '2026-04-15 19:52:03', '2026-04-15 19:52:03'),
(299, 'MBR-2026-0299', 'Samson', 'John', 'male', NULL, NULL, '255731456077', NULL, 'samson.j@yahoo.com', 'Dodoma', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-15', 'active', NULL, 1, 1, '2026-04-15 19:52:03', '2026-04-15 19:52:03'),
(300, 'MBR-2026-0300', 'Dinah', 'Mark', 'female', NULL, NULL, '255785567078', NULL, 'dinah.m@gmail.com', 'Arusha', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-15', 'active', NULL, 1, 1, '2026-04-15 19:52:03', '2026-04-15 19:52:03'),
(301, 'MBR-2026-0301', 'Reuben', 'Joseph', 'male', NULL, NULL, '255773678079', NULL, 'reuben.j@outlook.com', 'Pwani', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-15', 'active', NULL, 1, 1, '2026-04-15 19:52:03', '2026-04-15 19:52:03'),
(302, 'MBR-2026-0302', 'Leah', 'Peter', 'female', NULL, NULL, '255707789080', NULL, 'leah.p@gmail.com', 'Tanga', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-15', 'active', NULL, 1, 1, '2026-04-15 19:52:03', '2026-04-15 19:52:03'),
(303, 'MBR-2026-0303', 'Ezekiel', 'Andrew', 'male', NULL, NULL, '255732890081', NULL, 'ezeekiel.a@yahoo.com', 'Zanzibar', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-15', 'active', NULL, 1, 1, '2026-04-15 19:52:03', '2026-04-15 19:52:03'),
(304, 'MBR-2026-0304', 'Martha', 'Paul', 'female', NULL, NULL, '255786901082', NULL, 'martha.p@gmail.com', 'Kilimanjaro', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-15', 'active', NULL, 1, 1, '2026-04-15 19:52:03', '2026-04-15 19:52:03'),
(305, 'MBR-2026-0305', 'Gad', 'Simon', 'male', NULL, NULL, '255774012083', NULL, 'gad.s@outlook.com', 'Mbeya', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-15', 'active', NULL, 1, 1, '2026-04-15 19:52:03', '2026-04-15 19:52:03'),
(306, 'MBR-2026-0306', 'Sarah', 'David', 'female', NULL, NULL, '255708123084', NULL, 'sarah.d@gmail.com', 'Dar es Salaam', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-15', 'active', NULL, 1, 1, '2026-04-15 19:52:03', '2026-04-15 19:52:03');
INSERT INTO `members` (`id`, `member_code`, `first_name`, `last_name`, `gender`, `date_of_birth`, `marital_status`, `phone`, `alt_phone`, `email`, `physical_address`, `ward`, `district`, `region`, `emergency_contact_name`, `emergency_contact_phone`, `baptism_date`, `join_date`, `member_status`, `notes`, `created_by`, `updated_by`, `created_at`, `updated_at`) VALUES
(307, 'MBR-2026-0307', 'Aaron', 'John', 'male', NULL, NULL, '255733234085', NULL, 'aaron.j@yahoo.com', 'Morogoro', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-15', 'active', NULL, 1, 1, '2026-04-15 19:52:03', '2026-04-15 19:52:03'),
(308, 'MBR-2026-0308', 'Hagar', 'Francis', 'female', NULL, NULL, '255787345086', NULL, 'hagar.f@gmail.com', 'Tanga', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-15', 'active', NULL, 1, 1, '2026-04-15 19:52:03', '2026-04-15 19:52:03'),
(309, 'MBR-2026-0309', 'Asher', 'Thomas', 'male', NULL, NULL, '255775456087', NULL, 'asher.t@outlook.com', 'Arusha', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-15', 'active', NULL, 1, 1, '2026-04-15 19:52:03', '2026-04-15 19:52:03'),
(310, 'MBR-2026-0310', 'Keturah', 'James', 'female', NULL, NULL, '255709567088', NULL, 'keturah.j@gmail.com', 'Dodoma', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-15', 'active', NULL, 1, 1, '2026-04-15 19:52:03', '2026-04-15 19:52:03'),
(311, 'MBR-2026-0311', 'Dan', 'Joseph', 'male', NULL, NULL, '255734678089', NULL, 'dan.j@yahoo.com', 'Mwanza', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-15', 'active', NULL, 1, 1, '2026-04-15 19:52:03', '2026-04-15 19:52:03'),
(312, 'MBR-2026-0312', 'Priscilla', 'Mark', 'female', NULL, NULL, '255788789090', NULL, 'priscilla.m@gmail.com', 'Ruvuma', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-15', 'active', NULL, 1, 1, '2026-04-15 19:52:03', '2026-04-15 19:52:03'),
(313, 'MBR-2026-0313', 'Enoch', 'Peter', 'male', NULL, NULL, '255776890091', NULL, 'enoch.p@outlook.com', 'Tabora', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-15', 'active', NULL, 1, 1, '2026-04-15 19:52:03', '2026-04-15 19:52:03'),
(314, 'MBR-2026-0314', 'Rebecca', 'Andrew', 'female', NULL, NULL, '255710901092', NULL, 'rebecca.a@gmail.com', 'Arusha', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-15', 'active', NULL, 1, 1, '2026-04-15 19:52:03', '2026-04-15 19:52:03'),
(315, 'MBR-2026-0315', 'Nehemiah', 'Paul', 'male', NULL, NULL, '255735012093', NULL, 'nehemiah.p@yahoo.com', 'Dar es Salaam', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-15', 'active', NULL, 1, 1, '2026-04-15 19:52:03', '2026-04-15 19:52:03'),
(316, 'MBR-2026-0316', 'Susanna', 'John', 'female', NULL, NULL, '255789123094', NULL, 'susanna.j@gmail.com', 'Kigoma', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-15', 'active', NULL, 1, 1, '2026-04-15 19:52:03', '2026-04-15 19:52:03'),
(317, 'MBR-2026-0317', 'Malachi', 'Simon', 'male', NULL, NULL, '255777234095', NULL, 'malachi.s@outlook.com', 'Mbeya', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-15', 'active', NULL, 1, 1, '2026-04-15 19:52:03', '2026-04-15 19:52:03'),
(318, 'MBR-2026-0318', 'Jemima', 'David', 'female', NULL, NULL, '255711345096', NULL, 'jemima.d@gmail.com', 'Pwani', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-15', 'active', NULL, 1, 1, '2026-04-15 19:52:03', '2026-04-15 19:52:03'),
(319, 'MBR-2026-0319', 'Obadiah', 'Mark', 'male', NULL, NULL, '255736456097', NULL, 'obadiah.m@yahoo.com', 'Tanga', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-15', 'active', NULL, 1, 1, '2026-04-15 19:52:03', '2026-04-15 19:52:03'),
(320, 'MBR-2026-0320', 'Zipporah', 'James', 'female', NULL, NULL, '255790567098', NULL, 'zipporah.j@gmail.com', 'Dodoma', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-15', 'active', NULL, 1, 1, '2026-04-15 19:52:03', '2026-04-15 19:52:03'),
(321, 'MBR-2026-0321', 'Uriah', 'Peter', 'male', NULL, NULL, '255778678099', NULL, 'uriah.p@outlook.com', 'Arusha', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-15', 'active', NULL, 1, 1, '2026-04-15 19:52:03', '2026-04-15 19:52:03'),
(322, 'MBR-2026-0322', 'Hadassah', 'John', 'female', NULL, NULL, '255712789100', NULL, 'hadassah.j@gmail.com', 'Dar es Salaam', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-15', 'active', NULL, 1, 1, '2026-04-15 19:52:03', '2026-04-15 19:52:03');

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

DROP TABLE IF EXISTS `permissions`;
CREATE TABLE IF NOT EXISTS `permissions` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'e.g. members.create',
  `module` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'e.g. members',
  `description` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `idx_permissions_module` (`module`)
) ENGINE=InnoDB AUTO_INCREMENT=97 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `module`, `description`, `created_at`) VALUES
(1, 'members.view', 'members', 'View member list and profiles', '2026-04-14 13:20:21'),
(2, 'members.create', 'members', 'Add new members', '2026-04-14 13:20:21'),
(3, 'members.edit', 'members', 'Edit member details', '2026-04-14 13:20:21'),
(4, 'members.delete', 'members', 'Delete/deactivate members', '2026-04-14 13:20:21'),
(5, 'members.export', 'members', 'Export member data to CSV', '2026-04-14 13:20:21'),
(6, 'departments.view', 'departments', 'View departments', '2026-04-14 13:20:21'),
(7, 'departments.create', 'departments', 'Create new departments', '2026-04-14 13:20:21'),
(8, 'departments.edit', 'departments', 'Edit department details', '2026-04-14 13:20:21'),
(9, 'departments.delete', 'departments', 'Delete departments', '2026-04-14 13:20:21'),
(10, 'events.view', 'events', 'View events and calendar', '2026-04-14 13:20:21'),
(11, 'events.create', 'events', 'Create new events', '2026-04-14 13:20:21'),
(12, 'events.edit', 'events', 'Edit event details', '2026-04-14 13:20:21'),
(13, 'events.delete', 'events', 'Delete events', '2026-04-14 13:20:21'),
(14, 'attendance.view', 'attendance', 'View attendance records', '2026-04-14 13:20:21'),
(15, 'attendance.record', 'attendance', 'Record attendance', '2026-04-14 13:20:21'),
(16, 'finance.view', 'finance', 'View finance records and reports', '2026-04-14 13:20:21'),
(17, 'finance.create', 'finance', 'Create income/expense entries', '2026-04-14 13:20:21'),
(18, 'finance.approve', 'finance', 'Approve finance entries', '2026-04-14 13:20:21'),
(19, 'finance.delete', 'finance', 'Delete finance entries', '2026-04-14 13:20:21'),
(20, 'procurement.view', 'procurement', 'View purchase requests', '2026-04-14 13:20:21'),
(21, 'procurement.create', 'procurement', 'Create purchase requests', '2026-04-14 13:20:21'),
(22, 'procurement.approve', 'procurement', 'Approve/reject purchase requests', '2026-04-14 13:20:21'),
(23, 'procurement.complete', 'procurement', 'Mark orders as complete', '2026-04-14 13:20:21'),
(24, 'assets.view', 'assets', 'View asset register', '2026-04-14 13:20:21'),
(25, 'assets.create', 'assets', 'Add new assets', '2026-04-14 13:20:21'),
(26, 'assets.edit', 'assets', 'Edit asset details', '2026-04-14 13:20:21'),
(27, 'assets.delete', 'assets', 'Delete/retire assets', '2026-04-14 13:20:21'),
(28, 'communication.view', 'communication', 'View messages and broadcasts', '2026-04-14 13:20:21'),
(29, 'communication.send', 'communication', 'Send messages and broadcasts', '2026-04-14 13:20:21'),
(30, 'reports.view', 'reports', 'View all reports', '2026-04-14 13:20:21'),
(31, 'reports.export', 'reports', 'Export reports to CSV/Excel', '2026-04-14 13:20:21'),
(32, 'settings.manage', 'settings', 'Manage system settings, users, and roles', '2026-04-14 13:20:21');

-- --------------------------------------------------------

--
-- Table structure for table `pledges`
--

DROP TABLE IF EXISTS `pledges`;
CREATE TABLE IF NOT EXISTS `pledges` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `pledge_no` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `member_id` bigint UNSIGNED NOT NULL,
  `campaign` varchar(180) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `total_amount` decimal(14,2) NOT NULL,
  `paid_amount` decimal(14,2) NOT NULL DEFAULT '0.00',
  `pledge_date` date NOT NULL,
  `due_date` date DEFAULT NULL,
  `status` enum('active','completed','cancelled','overdue') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_by` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pledge_no` (`pledge_no`),
  KEY `fk_pledges_created_by` (`created_by`),
  KEY `idx_pledges_member_id` (`member_id`),
  KEY `idx_pledges_status` (`status`),
  KEY `idx_pledges_date` (`pledge_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pledge_payments`
--

DROP TABLE IF EXISTS `pledge_payments`;
CREATE TABLE IF NOT EXISTS `pledge_payments` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `pledge_id` bigint UNSIGNED NOT NULL,
  `finance_entry_id` bigint UNSIGNED NOT NULL,
  `amount` decimal(14,2) NOT NULL,
  `payment_date` date NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_pledge_payments_entry` (`finance_entry_id`),
  KEY `idx_pledge_payments_pledge_id` (`pledge_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `procurement_approvals`
--

DROP TABLE IF EXISTS `procurement_approvals`;
CREATE TABLE IF NOT EXISTS `procurement_approvals` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `purchase_request_id` bigint UNSIGNED NOT NULL,
  `level_no` tinyint UNSIGNED NOT NULL,
  `approver_user_id` bigint UNSIGNED NOT NULL,
  `decision` enum('pending','approved','rejected') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `decision_notes` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `decided_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_procurement_request_level` (`purchase_request_id`,`level_no`),
  KEY `fk_procurement_approval_user` (`approver_user_id`),
  KEY `idx_procurement_approval_decision` (`decision`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `procurement_approvals`
--

INSERT INTO `procurement_approvals` (`id`, `purchase_request_id`, `level_no`, `approver_user_id`, `decision`, `decision_notes`, `decided_at`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 2, 'approved', 'Approved within event budget', '2026-03-25 10:15:00', '2026-04-10 10:43:44', '2026-04-10 10:43:44');

-- --------------------------------------------------------

--
-- Table structure for table `purchase_orders`
--

DROP TABLE IF EXISTS `purchase_orders`;
CREATE TABLE IF NOT EXISTS `purchase_orders` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `po_no` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `purchase_request_id` bigint UNSIGNED NOT NULL,
  `supplier_id` bigint UNSIGNED NOT NULL,
  `issued_by` bigint UNSIGNED NOT NULL,
  `issue_date` date NOT NULL,
  `expected_delivery_date` date DEFAULT NULL,
  `po_status` enum('draft','issued','partially_received','received','cancelled') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `subtotal` decimal(14,2) NOT NULL DEFAULT '0.00',
  `tax_amount` decimal(14,2) NOT NULL DEFAULT '0.00',
  `total_amount` decimal(14,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `po_no` (`po_no`),
  KEY `fk_purchase_orders_request` (`purchase_request_id`),
  KEY `fk_purchase_orders_issued_by` (`issued_by`),
  KEY `idx_purchase_orders_status` (`po_status`),
  KEY `idx_purchase_orders_supplier_id` (`supplier_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `purchase_orders`
--

INSERT INTO `purchase_orders` (`id`, `po_no`, `purchase_request_id`, `supplier_id`, `issued_by`, `issue_date`, `expected_delivery_date`, `po_status`, `subtotal`, `tax_amount`, `total_amount`, `created_at`, `updated_at`) VALUES
(1, 'PO-2026-017', 1, 1, 2, '2026-03-25', '2026-03-29', 'issued', 700000.00, 80000.00, 780000.00, '2026-04-10 10:43:44', '2026-04-10 10:43:44');

-- --------------------------------------------------------

--
-- Table structure for table `purchase_order_items`
--

DROP TABLE IF EXISTS `purchase_order_items`;
CREATE TABLE IF NOT EXISTS `purchase_order_items` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `purchase_order_id` bigint UNSIGNED NOT NULL,
  `item_name` varchar(180) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantity` decimal(12,2) NOT NULL,
  `unit_price` decimal(14,2) NOT NULL,
  `line_total` decimal(14,2) GENERATED ALWAYS AS ((`quantity` * `unit_price`)) STORED,
  `received_quantity` decimal(12,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_po_items_po_id` (`purchase_order_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `purchase_order_items`
--

INSERT INTO `purchase_order_items` (`id`, `purchase_order_id`, `item_name`, `quantity`, `unit_price`, `received_quantity`, `created_at`, `updated_at`) VALUES
(1, 1, 'Flyers Print', 1.00, 280000.00, 1.00, '2026-04-10 10:43:44', '2026-04-10 10:43:44'),
(2, 1, 'Audio Cables', 5.00, 100000.00, 3.00, '2026-04-10 10:43:44', '2026-04-10 10:43:44');

-- --------------------------------------------------------

--
-- Table structure for table `purchase_requests`
--

DROP TABLE IF EXISTS `purchase_requests`;
CREATE TABLE IF NOT EXISTS `purchase_requests` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `request_no` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `requested_by` bigint UNSIGNED NOT NULL,
  `department` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `purpose` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `estimated_cost` decimal(14,2) NOT NULL,
  `event_id` bigint UNSIGNED DEFAULT NULL,
  `budget_id` bigint UNSIGNED DEFAULT NULL,
  `vendor_name` varchar(180) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `approved_by` bigint UNSIGNED DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `rejection_reason` text COLLATE utf8mb4_unicode_ci,
  `completed_at` datetime DEFAULT NULL,
  `requested_date` date NOT NULL,
  `required_by_date` date DEFAULT NULL,
  `status` enum('draft','submitted','approved','rejected','purchased','completed','cancelled','ordered','closed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `request_no` (`request_no`),
  KEY `fk_purchase_requests_user` (`requested_by`),
  KEY `idx_purchase_requests_status` (`status`),
  KEY `idx_purchase_requests_date` (`requested_date`),
  KEY `idx_purchase_requests_event_id` (`event_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `purchase_requests`
--

INSERT INTO `purchase_requests` (`id`, `request_no`, `requested_by`, `department`, `purpose`, `estimated_cost`, `event_id`, `budget_id`, `vendor_name`, `approved_by`, `approved_at`, `rejection_reason`, `completed_at`, `requested_date`, `required_by_date`, `status`, `created_at`, `updated_at`) VALUES
(1, 'PR-2026-031', 3, 'Youth Ministry', 'Sound and publicity materials', 780000.00, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-15 09:30:46', '2026-03-24', '2026-03-29', 'purchased', '2026-04-10 10:43:44', '2026-04-15 06:30:46'),
(2, 'PR-20260414-085', 1, 'Ujenzi', 'JUMA', 730000.00, NULL, 1, 'MO FUNRI', 1, '2026-04-14 20:08:33', NULL, '2026-04-14 20:08:49', '2026-04-14', NULL, 'completed', '2026-04-14 17:07:36', '2026-04-14 17:08:49'),
(3, 'PR-20260415-291', 2, 'Ibada', 'JUMA', 50000.00, NULL, 2, '', 1, '2026-04-15 15:23:06', 'rekebisha bei', NULL, '2026-04-15', NULL, 'cancelled', '2026-04-15 12:21:33', '2026-04-15 12:24:02'),
(4, 'PR-20260415-470', 2, 'Ibada', 'JUMA', 39000.00, NULL, 2, 'MO FUNRI', 2, '2026-04-15 15:26:21', NULL, '2026-04-15 17:04:22', '2026-04-15', NULL, 'purchased', '2026-04-15 12:24:32', '2026-04-15 14:04:22'),
(5, 'PR-20260415-351', 1, 'Huduma', 'JUMA', 10000.00, NULL, 3, 'MO FUNRI', 2, '2026-04-15 16:23:11', NULL, '2026-04-15 16:54:09', '2026-04-15', NULL, 'purchased', '2026-04-15 13:21:21', '2026-04-15 13:54:09'),
(6, 'PR-20260415-149', 1, 'Ibada', 'JUMA', 10000.00, NULL, 2, 'MO FUNRI', 2, '2026-04-15 18:31:18', NULL, '2026-04-15 18:31:25', '2026-04-15', NULL, 'purchased', '2026-04-15 15:30:28', '2026-04-15 15:31:25'),
(7, 'PR-20260415-465', 1, 'Ujenzi', 'NICK', 6350000.00, NULL, 4, 'MO FUNRI', 1, '2026-04-15 22:45:36', NULL, '2026-04-15 22:46:02', '2026-04-15', NULL, 'purchased', '2026-04-15 19:44:20', '2026-04-15 19:46:02');

-- --------------------------------------------------------

--
-- Table structure for table `purchase_request_items`
--

DROP TABLE IF EXISTS `purchase_request_items`;
CREATE TABLE IF NOT EXISTS `purchase_request_items` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `purchase_request_id` bigint UNSIGNED NOT NULL,
  `item_name` varchar(220) COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantity` decimal(12,2) NOT NULL DEFAULT '1.00',
  `estimated_unit_cost` decimal(14,2) NOT NULL DEFAULT '0.00',
  `line_total` decimal(14,2) GENERATED ALWAYS AS ((`quantity` * `estimated_unit_cost`)) STORED,
  `notes` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_pri_pr` (`purchase_request_id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `purchase_request_items`
--

INSERT INTO `purchase_request_items` (`id`, `purchase_request_id`, `item_name`, `quantity`, `estimated_unit_cost`, `notes`, `created_at`) VALUES
(1, 2, 'MBAO 2 BY 2', 20.00, 10000.00, '', '2026-04-14 17:07:36'),
(2, 2, 'MISUMARI', 10.00, 30000.00, '', '2026-04-14 17:07:36'),
(3, 2, 'BATI  ZA MSAUZI', 10.00, 23000.00, '', '2026-04-14 17:07:36'),
(4, 3, 'MBAO 2 BY 2', 5.00, 10000.00, '', '2026-04-15 12:21:33'),
(5, 4, 'MBAO 2 BY 2', 1.00, 39000.00, '', '2026-04-15 12:24:32'),
(6, 5, 'MBAO 2 BY 2', 1.00, 10000.00, '', '2026-04-15 13:21:21'),
(7, 6, 'MBAO 2 BY 2', 1.00, 10000.00, '', '2026-04-15 15:30:28'),
(8, 7, 'MBAO 2 BY 2', 100.00, 3500.00, '', '2026-04-15 19:44:20'),
(9, 7, 'BATI', 300.00, 20000.00, '', '2026-04-15 19:44:20');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
CREATE TABLE IF NOT EXISTS `roles` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `description`, `created_at`, `updated_at`) VALUES
(1, 'Admin', 'Full access across all modules', '2026-04-10 10:43:44', '2026-04-10 10:43:44'),
(2, 'Finance Officer', 'Manages income, expenses, and reports', '2026-04-10 10:43:44', '2026-04-10 10:43:44'),
(3, 'Secretary', 'Manages members, attendance, communication, events', '2026-04-10 10:43:44', '2026-04-10 10:43:44'),
(4, 'Standard User', 'Limited operational access', '2026-04-10 10:43:44', '2026-04-10 10:43:44'),
(5, 'Event Organizer', 'Can create events and request budgets', '2026-04-10 14:39:11', '2026-04-10 14:39:11'),
(6, 'Procurement Officer', 'Handles purchase requests and vendor processing', '2026-04-10 14:39:11', '2026-04-10 14:39:11'),
(7, 'Approver', 'Authority to approve budgets and procurement (e.g., Pastor/Manager)', '2026-04-10 14:39:11', '2026-04-10 14:39:11');

-- --------------------------------------------------------

--
-- Table structure for table `role_permissions`
--

DROP TABLE IF EXISTS `role_permissions`;
CREATE TABLE IF NOT EXISTS `role_permissions` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `role_id` bigint UNSIGNED NOT NULL,
  `permission_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_role_perm` (`role_id`,`permission_id`),
  KEY `fk_rp_perm` (`permission_id`),
  KEY `idx_rp_role_id` (`role_id`)
) ENGINE=InnoDB AUTO_INCREMENT=163 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `role_permissions`
--

INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `created_at`) VALUES
(1, 1, 24, '2026-04-14 13:20:21'),
(2, 1, 25, '2026-04-14 13:20:21'),
(3, 1, 26, '2026-04-14 13:20:21'),
(4, 1, 27, '2026-04-14 13:20:21'),
(5, 1, 14, '2026-04-14 13:20:21'),
(6, 1, 15, '2026-04-14 13:20:21'),
(7, 1, 28, '2026-04-14 13:20:21'),
(8, 1, 29, '2026-04-14 13:20:21'),
(9, 1, 6, '2026-04-14 13:20:21'),
(10, 1, 7, '2026-04-14 13:20:21'),
(11, 1, 8, '2026-04-14 13:20:21'),
(12, 1, 9, '2026-04-14 13:20:21'),
(13, 1, 10, '2026-04-14 13:20:21'),
(14, 1, 11, '2026-04-14 13:20:21'),
(15, 1, 12, '2026-04-14 13:20:21'),
(16, 1, 13, '2026-04-14 13:20:21'),
(17, 1, 16, '2026-04-14 13:20:21'),
(18, 1, 17, '2026-04-14 13:20:21'),
(19, 1, 18, '2026-04-14 13:20:21'),
(20, 1, 19, '2026-04-14 13:20:21'),
(21, 1, 1, '2026-04-14 13:20:21'),
(22, 1, 2, '2026-04-14 13:20:21'),
(23, 1, 3, '2026-04-14 13:20:21'),
(24, 1, 4, '2026-04-14 13:20:21'),
(25, 1, 5, '2026-04-14 13:20:21'),
(26, 1, 20, '2026-04-14 13:20:21'),
(27, 1, 21, '2026-04-14 13:20:21'),
(28, 1, 22, '2026-04-14 13:20:21'),
(29, 1, 23, '2026-04-14 13:20:21'),
(30, 1, 30, '2026-04-14 13:20:21'),
(31, 1, 31, '2026-04-14 13:20:21'),
(32, 1, 32, '2026-04-14 13:20:21'),
(64, 2, 24, '2026-04-14 13:20:21'),
(65, 2, 10, '2026-04-14 13:20:21'),
(66, 2, 18, '2026-04-14 13:20:21'),
(67, 2, 17, '2026-04-14 13:20:21'),
(68, 2, 19, '2026-04-14 13:20:21'),
(69, 2, 16, '2026-04-14 13:20:21'),
(70, 2, 1, '2026-04-14 13:20:21'),
(71, 2, 22, '2026-04-14 13:20:21'),
(72, 2, 23, '2026-04-14 13:20:21'),
(73, 2, 21, '2026-04-14 13:20:21'),
(74, 2, 20, '2026-04-14 13:20:21'),
(75, 2, 31, '2026-04-14 13:20:21'),
(76, 2, 30, '2026-04-14 13:20:21'),
(94, 4, 14, '2026-04-14 13:20:21'),
(95, 4, 6, '2026-04-14 13:20:21'),
(96, 4, 10, '2026-04-14 13:20:21'),
(97, 4, 1, '2026-04-14 13:20:21'),
(105, 5, 15, '2026-04-14 13:22:28'),
(106, 5, 14, '2026-04-14 13:22:28'),
(107, 5, 29, '2026-04-14 13:22:28'),
(108, 5, 28, '2026-04-14 13:22:28'),
(109, 5, 11, '2026-04-14 13:22:28'),
(110, 5, 12, '2026-04-14 13:22:28'),
(111, 5, 10, '2026-04-14 13:22:28'),
(112, 5, 1, '2026-04-14 13:22:28'),
(113, 5, 30, '2026-04-14 13:22:28'),
(120, 6, 24, '2026-04-14 13:22:28'),
(121, 6, 10, '2026-04-14 13:22:28'),
(122, 6, 16, '2026-04-14 13:22:28'),
(123, 6, 1, '2026-04-14 13:22:28'),
(124, 6, 21, '2026-04-14 13:22:28'),
(125, 6, 20, '2026-04-14 13:22:28'),
(126, 6, 30, '2026-04-14 13:22:28'),
(127, 7, 10, '2026-04-14 13:22:28'),
(128, 7, 18, '2026-04-14 13:22:28'),
(129, 7, 16, '2026-04-14 13:22:28'),
(130, 7, 22, '2026-04-14 13:22:28'),
(131, 7, 23, '2026-04-14 13:22:28'),
(132, 7, 20, '2026-04-14 13:22:28'),
(133, 7, 31, '2026-04-14 13:22:28'),
(134, 7, 30, '2026-04-14 13:22:28'),
(146, 3, 15, '2026-04-15 19:39:31'),
(147, 3, 14, '2026-04-15 19:39:31'),
(148, 3, 29, '2026-04-15 19:39:31'),
(149, 3, 28, '2026-04-15 19:39:31'),
(150, 3, 6, '2026-04-15 19:39:31'),
(151, 3, 11, '2026-04-15 19:39:31'),
(152, 3, 12, '2026-04-15 19:39:31'),
(153, 3, 10, '2026-04-15 19:39:31'),
(154, 3, 17, '2026-04-15 19:39:31'),
(155, 3, 16, '2026-04-15 19:39:31'),
(156, 3, 2, '2026-04-15 19:39:31'),
(157, 3, 3, '2026-04-15 19:39:31'),
(158, 3, 5, '2026-04-15 19:39:31'),
(159, 3, 1, '2026-04-15 19:39:31'),
(160, 3, 21, '2026-04-15 19:39:31'),
(161, 3, 20, '2026-04-15 19:39:31'),
(162, 3, 30, '2026-04-15 19:39:31');

-- --------------------------------------------------------

--
-- Table structure for table `sms_logs`
--

DROP TABLE IF EXISTS `sms_logs`;
CREATE TABLE IF NOT EXISTS `sms_logs` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `message_id` bigint UNSIGNED DEFAULT NULL,
  `phone` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message_text` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `message_type` enum('reminder','notification','report','other') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'other',
  `recipient_type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `provider` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'internal',
  `event_id` bigint UNSIGNED DEFAULT NULL,
  `member_id` bigint UNSIGNED DEFAULT NULL,
  `group_id` bigint UNSIGNED DEFAULT NULL,
  `delivery_status` enum('queued','sent','delivered','failed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'queued',
  `sent_by` bigint UNSIGNED NOT NULL,
  `sent_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_sms_member` (`member_id`),
  KEY `fk_sms_sent_by` (`sent_by`),
  KEY `idx_sms_event_id` (`event_id`),
  KEY `idx_sms_status` (`delivery_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `suppliers`
--

DROP TABLE IF EXISTS `suppliers`;
CREATE TABLE IF NOT EXISTS `suppliers` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `supplier_code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `contact_person` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tin_number` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rating` tinyint UNSIGNED DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `supplier_code` (`supplier_code`),
  KEY `idx_suppliers_name` (`name`),
  KEY `idx_suppliers_active` (`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `suppliers`
--

INSERT INTO `suppliers` (`id`, `supplier_code`, `name`, `contact_person`, `phone`, `email`, `tin_number`, `address`, `rating`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'SUP-001', 'Mwangaza Supplies Ltd', 'Elias John', '+255754000111', 'sales@mwangaza.co.tz', NULL, NULL, NULL, 1, '2026-04-10 10:43:44', '2026-04-10 10:43:44');

-- --------------------------------------------------------

--
-- Table structure for table `theme_verses`
--

DROP TABLE IF EXISTS `theme_verses`;
CREATE TABLE IF NOT EXISTS `theme_verses` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `verse_reference` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `verse_text` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `translation` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'SWAHILI',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `display_weight` int UNSIGNED NOT NULL DEFAULT '1',
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_theme_verses_active` (`is_active`),
  KEY `idx_theme_verses_dates` (`start_date`,`end_date`)
) ENGINE=InnoDB AUTO_INCREMENT=101 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `theme_verses`
--

INSERT INTO `theme_verses` (`id`, `verse_reference`, `verse_text`, `translation`, `is_active`, `display_weight`, `start_date`, `end_date`, `created_at`, `updated_at`) VALUES
(1, 'Zaburi 23:1', 'Bwana ndiye mchungaji wangu, sitapungukiwa na kitu.', 'SWAHILI', 1, 1, NULL, NULL, '2026-04-10 14:39:11', '2026-04-10 14:39:11'),
(2, 'Yeremia 29:11', 'Mimi najua mipango niliyo nayo juu yenu, mipango ya amani na si ya mabaya.', 'SWAHILI', 1, 1, NULL, NULL, '2026-04-10 14:39:11', '2026-04-10 14:39:11'),
(3, 'Wafilipi 4:13', 'Naweza kufanya mambo yote kupitia yeye anitiaye nguvu.', 'SWAHILI', 1, 1, NULL, NULL, '2026-04-10 14:39:11', '2026-04-10 14:39:11'),
(4, 'Isaya 41:10', 'Usiogope, kwa maana mimi niko pamoja nawe.', 'SWAHILI', 1, 1, NULL, NULL, '2026-04-10 14:39:11', '2026-04-10 14:39:11'),
(5, 'Warumi 8:28', 'Mambo yote hufanya kazi pamoja kwa wema kwa wale wampendao Mungu.', 'SWAHILI', 1, 1, NULL, NULL, '2026-04-10 14:39:11', '2026-04-10 14:39:11'),
(6, 'Zaburi 46:1', 'Mungu ni kimbilio na nguvu, msaada wa karibu wakati wa shida.', 'SWAHILI', 1, 1, NULL, NULL, '2026-04-10 14:39:11', '2026-04-10 14:39:11'),
(7, 'Mathayo 6:33', 'Tafuteni kwanza ufalme wa Mungu na haki yake.', 'SWAHILI', 1, 1, NULL, NULL, '2026-04-10 14:39:11', '2026-04-10 14:39:11'),
(8, 'Zaburi 34:17', 'Wenye haki wanapolia, Bwana husikia na kuwaokoa.', 'SWAHILI', 1, 1, NULL, NULL, '2026-04-10 14:39:11', '2026-04-10 14:39:11'),
(9, 'Yohana 14:27', 'Amani nawaachieni, amani yangu nawapa.', 'SWAHILI', 1, 1, NULL, NULL, '2026-04-10 14:39:11', '2026-04-10 14:39:11'),
(10, 'Mithali 3:5', 'Mtumaini Bwana kwa moyo wako wote.', 'SWAHILI', 1, 1, NULL, NULL, '2026-04-10 14:39:11', '2026-04-10 14:39:11'),
(11, 'Zaburi 121:1-2', 'Msaada wangu hutoka kwa Bwana aliyeziumba mbingu na nchi.', 'SWAHILI', 1, 1, NULL, NULL, '2026-04-10 14:39:11', '2026-04-10 14:39:11'),
(12, 'Yakobo 1:5', 'Mtu akipungukiwa hekima, na amwombe Mungu.', 'SWAHILI', 1, 1, NULL, NULL, '2026-04-10 14:39:11', '2026-04-10 14:39:11'),
(13, 'Waebrania 11:1', 'Imani ni hakika ya mambo yatarajiwayo.', 'SWAHILI', 1, 1, NULL, NULL, '2026-04-10 14:39:11', '2026-04-10 14:39:11'),
(14, '2 Timotheo 1:7', 'Mungu hakutupa roho ya woga bali ya nguvu na upendo.', 'SWAHILI', 1, 1, NULL, NULL, '2026-04-10 14:39:11', '2026-04-10 14:39:11'),
(15, 'Zaburi 37:4', 'Jifurahishe katika Bwana naye atakupa haja za moyo wako.', 'SWAHILI', 1, 1, NULL, NULL, '2026-04-10 14:39:11', '2026-04-10 14:39:11'),
(16, 'Mathayo 11:28', 'Njoni kwangu ninyi wote mnaochoka, nami nitawapumzisha.', 'SWAHILI', 1, 1, NULL, NULL, '2026-04-10 14:39:11', '2026-04-10 14:39:11'),
(17, 'Zaburi 119:105', 'Neno lako ni taa ya miguu yangu.', 'SWAHILI', 1, 1, NULL, NULL, '2026-04-10 14:39:11', '2026-04-10 14:39:11'),
(18, 'Isaya 40:31', 'Wamngojeao Bwana watapata nguvu mpya.', 'SWAHILI', 1, 1, NULL, NULL, '2026-04-10 14:39:11', '2026-04-10 14:39:11'),
(19, 'Warumi 12:2', 'Msifananishwe na dunia hii bali mgeuzwe kwa kufanywa upya akili zenu.', 'SWAHILI', 1, 1, NULL, NULL, '2026-04-10 14:39:11', '2026-04-10 14:39:11'),
(20, 'Yoshua 1:9', 'Uwe hodari na moyo mkuu, Bwana yuko pamoja nawe.', 'SWAHILI', 1, 1, NULL, NULL, '2026-04-10 14:39:11', '2026-04-10 14:39:11'),
(21, 'Zaburi 91:1', 'Akaaye mahali pa siri pa Aliye Juu atakaa chini ya uvuli wa Mwenyezi.', 'SWAHILI', 1, 1, NULL, NULL, '2026-04-10 14:39:11', '2026-04-10 14:39:11'),
(22, 'Mathayo 7:7', 'Ombeni nanyi mtapewa, tafuteni nanyi mtaona.', 'SWAHILI', 1, 1, NULL, NULL, '2026-04-10 14:39:11', '2026-04-10 14:39:11'),
(23, '1 Wakorintho 10:13', 'Mungu ni mwaminifu, hatakuacha ujaribiwe kupita uwezo wako.', 'SWAHILI', 1, 1, NULL, NULL, '2026-04-10 14:39:11', '2026-04-10 14:39:11'),
(24, 'Zaburi 55:22', 'Mtwikie Bwana mzigo wako naye atakutegemeza.', 'SWAHILI', 1, 1, NULL, NULL, '2026-04-10 14:39:11', '2026-04-10 14:39:11'),
(25, 'Mika 6:8', 'Tenda haki, penda rehema, tembea kwa unyenyekevu.', 'SWAHILI', 1, 1, NULL, NULL, '2026-04-10 14:39:11', '2026-04-10 14:39:11'),
(26, 'Wafilipi 4:6', 'Msihangaike kwa neno lolote, bali kwa maombi.', 'SWAHILI', 1, 1, NULL, NULL, '2026-04-10 14:39:11', '2026-04-10 14:39:11'),
(27, 'Zaburi 27:1', 'Bwana ni nuru yangu na wokovu wangu, nitaogopa nani?', 'SWAHILI', 1, 1, NULL, NULL, '2026-04-10 14:39:11', '2026-04-10 14:39:11'),
(28, 'Waebrania 13:5', 'Sitakuacha wala sitakupungukia.', 'SWAHILI', 1, 1, NULL, NULL, '2026-04-10 14:39:11', '2026-04-10 14:39:11'),
(29, 'Zaburi 118:24', 'Hii ndiyo siku aliyoifanya Bwana, tutafurahi.', 'SWAHILI', 1, 1, NULL, NULL, '2026-04-10 14:39:11', '2026-04-10 14:39:11'),
(30, 'Isaya 26:3', 'Utamlinda yeye ambaye moyo wake unakutegemea.', 'SWAHILI', 1, 1, NULL, NULL, '2026-04-10 14:39:11', '2026-04-10 14:39:11'),
(31, 'Zaburi 62:1', 'Roho yangu inamtumaini Mungu peke yake.', 'SWAHILI', 1, 1, NULL, NULL, '2026-04-10 14:39:11', '2026-04-10 14:39:11'),
(32, 'Mathayo 5:14', 'Ninyi ni nuru ya ulimwengu.', 'SWAHILI', 1, 1, NULL, NULL, '2026-04-10 14:39:11', '2026-04-10 14:39:11'),
(33, 'Warumi 15:13', 'Mungu wa tumaini awajaze furaha na amani.', 'SWAHILI', 1, 1, NULL, NULL, '2026-04-10 14:39:11', '2026-04-10 14:39:11'),
(34, 'Zaburi 100:5', 'Bwana ni mwema, rehema zake ni za milele.', 'SWAHILI', 1, 1, NULL, NULL, '2026-04-10 14:39:11', '2026-04-10 14:39:11'),
(35, 'Yohana 16:33', 'Ulimwenguni mtapata dhiki, lakini jipeni moyo.', 'SWAHILI', 1, 1, NULL, NULL, '2026-04-10 14:39:11', '2026-04-10 14:39:11'),
(36, '2 Wakorintho 5:7', 'Tunaenenda kwa imani, si kwa kuona.', 'SWAHILI', 1, 1, NULL, NULL, '2026-04-10 14:39:11', '2026-04-10 14:39:11'),
(37, 'Zaburi 16:8', 'Nimemweka Bwana mbele yangu siku zote.', 'SWAHILI', 1, 1, NULL, NULL, '2026-04-10 14:39:11', '2026-04-10 14:39:11'),
(38, 'Isaya 43:2', 'Upitapo katika maji, nitakuwa pamoja nawe.', 'SWAHILI', 1, 1, NULL, NULL, '2026-04-10 14:39:11', '2026-04-10 14:39:11'),
(39, 'Yakobo 4:8', 'Mkaribieni Mungu naye atawakaribia.', 'SWAHILI', 1, 1, NULL, NULL, '2026-04-10 14:39:11', '2026-04-10 14:39:11'),
(40, 'Zaburi 145:18', 'Bwana yuko karibu na wote wamwitao.', 'SWAHILI', 1, 1, NULL, NULL, '2026-04-10 14:39:11', '2026-04-10 14:39:11'),
(41, 'Wagalatia 6:9', 'Tusichoke kutenda mema.', 'SWAHILI', 1, 1, NULL, NULL, '2026-04-10 14:39:11', '2026-04-10 14:39:11'),
(42, 'Zaburi 30:5', 'Furaha huja asubuhi.', 'SWAHILI', 1, 1, NULL, NULL, '2026-04-10 14:39:11', '2026-04-10 14:39:11'),
(43, 'Mathayo 28:20', 'Niko pamoja nanyi siku zote.', 'SWAHILI', 1, 1, NULL, NULL, '2026-04-10 14:39:11', '2026-04-10 14:39:11'),
(44, 'Zaburi 19:14', 'Maneno ya kinywa changu yakubalike mbele zako.', 'SWAHILI', 1, 1, NULL, NULL, '2026-04-10 14:39:11', '2026-04-10 14:39:11'),
(45, 'Isaya 55:8', 'Mawazo yangu si mawazo yenu.', 'SWAHILI', 1, 1, NULL, NULL, '2026-04-10 14:39:11', '2026-04-10 14:39:11'),
(46, 'Waefeso 6:10', 'Imarisheni katika Bwana.', 'SWAHILI', 1, 1, NULL, NULL, '2026-04-10 14:39:11', '2026-04-10 14:39:11'),
(47, 'Zaburi 84:11', 'Bwana huwapa neema na utukufu.', 'SWAHILI', 1, 1, NULL, NULL, '2026-04-10 14:39:11', '2026-04-10 14:39:11'),
(48, 'Mathayo 22:37', 'Mpende Bwana Mungu wako kwa moyo wako wote.', 'SWAHILI', 1, 1, NULL, NULL, '2026-04-10 14:39:11', '2026-04-10 14:39:11'),
(49, '1 Petro 5:7', 'Mtupieni yeye fadhaa zenu zote.', 'SWAHILI', 1, 1, NULL, NULL, '2026-04-10 14:39:11', '2026-04-10 14:39:11'),
(50, 'Zaburi 9:9', 'Bwana ni kimbilio wakati wa dhiki.', 'SWAHILI', 1, 1, NULL, NULL, '2026-04-10 14:39:11', '2026-04-10 14:39:11'),
(51, 'Yohana 8:12', 'Mimi ndimi nuru ya ulimwengu.', 'SWAHILI', 1, 1, NULL, NULL, '2026-04-10 14:39:11', '2026-04-10 14:39:11'),
(52, 'Zaburi 73:26', 'Mungu ndiye nguvu ya moyo wangu.', 'SWAHILI', 1, 1, NULL, NULL, '2026-04-10 14:39:11', '2026-04-10 14:39:11'),
(53, 'Mathayo 6:34', 'Msijisumbue kwa ajili ya kesho.', 'SWAHILI', 1, 1, NULL, NULL, '2026-04-10 14:39:11', '2026-04-10 14:39:11'),
(54, 'Isaya 30:21', 'Hii ndiyo njia, iendeni.', 'SWAHILI', 1, 1, NULL, NULL, '2026-04-10 14:39:11', '2026-04-10 14:39:11'),
(55, 'Zaburi 18:2', 'Bwana ni mwamba wangu na ngome yangu.', 'SWAHILI', 1, 1, NULL, NULL, '2026-04-10 14:39:11', '2026-04-10 14:39:11'),
(56, 'Waebrania 4:16', 'Karibieni kiti cha neema kwa ujasiri.', 'SWAHILI', 1, 1, NULL, NULL, '2026-04-10 14:39:11', '2026-04-10 14:39:11'),
(57, 'Zaburi 63:3', 'Fadhili zako ni bora kuliko uhai.', 'SWAHILI', 1, 1, NULL, NULL, '2026-04-10 14:39:11', '2026-04-10 14:39:11'),
(58, 'Mathayo 19:26', 'Kwa Mungu mambo yote yanawezekana.', 'SWAHILI', 1, 1, NULL, NULL, '2026-04-10 14:39:11', '2026-04-10 14:39:11'),
(59, 'Warumi 5:5', 'Upendo wa Mungu umemiminwa mioyoni mwetu.', 'SWAHILI', 1, 1, NULL, NULL, '2026-04-10 14:39:11', '2026-04-10 14:39:11'),
(60, 'Zaburi 86:7', 'Katika siku ya taabu nitakuita.', 'SWAHILI', 1, 1, NULL, NULL, '2026-04-10 14:39:11', '2026-04-10 14:39:11'),
(61, 'Yohana 10:10', 'Nimekuja ili wawe na uzima tele.', 'SWAHILI', 1, 1, NULL, NULL, '2026-04-10 14:39:11', '2026-04-10 14:39:11'),
(62, 'Zaburi 32:8', 'Nitakufundisha njia utakayokwenda.', 'SWAHILI', 1, 1, NULL, NULL, '2026-04-10 14:39:11', '2026-04-10 14:39:11'),
(63, 'Mathayo 5:16', 'Nuru yenu iangaze mbele ya watu.', 'SWAHILI', 1, 1, NULL, NULL, '2026-04-10 14:39:11', '2026-04-10 14:39:11'),
(64, 'Isaya 58:11', 'Bwana atakuongoza siku zote.', 'SWAHILI', 1, 1, NULL, NULL, '2026-04-10 14:39:11', '2026-04-10 14:39:11'),
(65, 'Zaburi 40:1', 'Nilimngojea Bwana naye akanisikia.', 'SWAHILI', 1, 1, NULL, NULL, '2026-04-10 14:39:11', '2026-04-10 14:39:11'),
(66, 'Wakolosai 3:23', 'Fanyeni yote kwa moyo kama kwa Bwana.', 'SWAHILI', 1, 1, NULL, NULL, '2026-04-10 14:39:11', '2026-04-10 14:39:11'),
(67, 'Zaburi 143:8', 'Nionyeshe njia ninayopaswa kwenda.', 'SWAHILI', 1, 1, NULL, NULL, '2026-04-10 14:39:11', '2026-04-10 14:39:11'),
(68, 'Mathayo 11:29', 'Jifunzeni kwangu, kwa kuwa mimi ni mpole.', 'SWAHILI', 1, 1, NULL, NULL, '2026-04-10 14:39:11', '2026-04-10 14:39:11'),
(69, 'Isaya 12:2', 'Mungu ni wokovu wangu, sitaogopa.', 'SWAHILI', 1, 1, NULL, NULL, '2026-04-10 14:39:11', '2026-04-10 14:39:11'),
(70, 'Zaburi 56:3', 'Nikiogopa, nitamtumaini wewe.', 'SWAHILI', 1, 1, NULL, NULL, '2026-04-10 14:39:11', '2026-04-10 14:39:11'),
(71, 'Yohana 15:5', 'Bila mimi hamwezi kufanya lolote.', 'SWAHILI', 1, 1, NULL, NULL, '2026-04-10 14:39:11', '2026-04-10 14:39:11'),
(72, 'Zaburi 119:11', 'Nimelificha neno lako moyoni mwangu.', 'SWAHILI', 1, 1, NULL, NULL, '2026-04-10 14:39:11', '2026-04-10 14:39:11'),
(73, 'Mathayo 21:22', 'Mtakachoomba kwa imani mtapokea.', 'SWAHILI', 1, 1, NULL, NULL, '2026-04-10 14:39:11', '2026-04-10 14:39:11'),
(74, 'Isaya 54:17', 'Hakuna silaha itakayofanikiwa dhidi yako.', 'SWAHILI', 1, 1, NULL, NULL, '2026-04-10 14:39:11', '2026-04-10 14:39:11'),
(75, 'Zaburi 31:24', 'Jipe moyo na uwe hodari.', 'SWAHILI', 1, 1, NULL, NULL, '2026-04-10 14:39:11', '2026-04-10 14:39:11'),
(76, 'Waefeso 3:20', 'Mungu anaweza kufanya zaidi ya tunavyofikiri.', 'SWAHILI', 1, 1, NULL, NULL, '2026-04-10 14:39:11', '2026-04-10 14:39:11'),
(77, 'Zaburi 34:8', 'Onjeni mkaone ya kuwa Bwana ni mwema.', 'SWAHILI', 1, 1, NULL, NULL, '2026-04-10 14:39:11', '2026-04-10 14:39:11'),
(78, 'Mathayo 18:20', 'Walipo wawili au watatu, mimi nipo.', 'SWAHILI', 1, 1, NULL, NULL, '2026-04-10 14:39:11', '2026-04-10 14:39:11'),
(79, 'Isaya 40:29', 'Huwapa nguvu wanyonge.', 'SWAHILI', 1, 1, NULL, NULL, '2026-04-10 14:39:11', '2026-04-10 14:39:11'),
(80, 'Zaburi 27:14', 'Mngojee Bwana, uwe hodari.', 'SWAHILI', 1, 1, NULL, NULL, '2026-04-10 14:39:11', '2026-04-10 14:39:11'),
(81, 'Yohana 6:35', 'Mimi ndimi mkate wa uzima.', 'SWAHILI', 1, 1, NULL, NULL, '2026-04-10 14:39:11', '2026-04-10 14:39:11'),
(82, 'Zaburi 125:1', 'Wamtegemeao Bwana ni kama mlima usiotikisika.', 'SWAHILI', 1, 1, NULL, NULL, '2026-04-10 14:39:11', '2026-04-10 14:39:11'),
(83, 'Mathayo 10:31', 'Msihofu, ninyi ni wa thamani zaidi.', 'SWAHILI', 1, 1, NULL, NULL, '2026-04-10 14:39:11', '2026-04-10 14:39:11'),
(84, 'Isaya 41:13', 'Nitakushika mkono wako wa kuume.', 'SWAHILI', 1, 1, NULL, NULL, '2026-04-10 14:39:11', '2026-04-10 14:39:11'),
(85, 'Zaburi 138:3', 'Ulinipa nguvu rohoni mwangu.', 'SWAHILI', 1, 1, NULL, NULL, '2026-04-10 14:39:11', '2026-04-10 14:39:11'),
(86, '2 Wathesalonike 3:3', 'Bwana ni mwaminifu atakulinda.', 'SWAHILI', 1, 1, NULL, NULL, '2026-04-10 14:39:11', '2026-04-10 14:39:11'),
(87, 'Zaburi 91:2', 'Yeye ni kimbilio langu na ngome yangu.', 'SWAHILI', 1, 1, NULL, NULL, '2026-04-10 14:39:11', '2026-04-10 14:39:11'),
(88, 'Mathayo 9:29', 'Na iwe kwenu kama imani yenu.', 'SWAHILI', 1, 1, NULL, NULL, '2026-04-10 14:39:11', '2026-04-10 14:39:11'),
(89, 'Isaya 33:2', 'Uwe nguvu yetu kila asubuhi.', 'SWAHILI', 1, 1, NULL, NULL, '2026-04-10 14:39:11', '2026-04-10 14:39:11'),
(90, 'Zaburi 20:7', 'Sisi tutalitaja jina la Bwana.', 'SWAHILI', 1, 1, NULL, NULL, '2026-04-10 14:39:11', '2026-04-10 14:39:11'),
(91, 'Yohana 14:6', 'Mimi ndimi njia na kweli na uzima.', 'SWAHILI', 1, 1, NULL, NULL, '2026-04-10 14:39:11', '2026-04-10 14:39:11'),
(92, 'Zaburi 28:7', 'Bwana ni nguvu yangu na ngao yangu.', 'SWAHILI', 1, 1, NULL, NULL, '2026-04-10 14:39:11', '2026-04-10 14:39:11'),
(93, 'Mathayo 6:21', 'Palipo hazina yako, ndipo ulipo moyo wako.', 'SWAHILI', 1, 1, NULL, NULL, '2026-04-10 14:39:11', '2026-04-10 14:39:11'),
(94, 'Isaya 26:4', 'Mtumainini Bwana milele.', 'SWAHILI', 1, 1, NULL, NULL, '2026-04-10 14:39:11', '2026-04-10 14:39:11'),
(95, 'Zaburi 147:3', 'Huwaponya waliovunjika moyo.', 'SWAHILI', 1, 1, NULL, NULL, '2026-04-10 14:39:11', '2026-04-10 14:39:11'),
(96, 'Yakobo 1:12', 'Heri mtu astahimiliye majaribu.', 'SWAHILI', 1, 1, NULL, NULL, '2026-04-10 14:39:11', '2026-04-10 14:39:11'),
(97, 'Zaburi 33:4', 'Neno la Bwana ni la haki.', 'SWAHILI', 1, 1, NULL, NULL, '2026-04-10 14:39:11', '2026-04-10 14:39:11'),
(98, 'Mathayo 5:9', 'Heri wapatanishi.', 'SWAHILI', 1, 1, NULL, NULL, '2026-04-10 14:39:11', '2026-04-10 14:39:11'),
(99, 'Isaya 9:6', 'Ataitwa Mfalme wa amani.', 'SWAHILI', 1, 1, NULL, NULL, '2026-04-10 14:39:11', '2026-04-10 14:39:11'),
(100, 'Zaburi 150:6', 'Kila mwenye pumzi na amsifu Bwana.', 'SWAHILI', 1, 1, NULL, NULL, '2026-04-10 14:39:11', '2026-04-10 14:39:11');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `role_id` bigint UNSIGNED NOT NULL,
  `full_name` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `phone` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_hash` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `last_login_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `phone` (`phone`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_users_role_id` (`role_id`),
  KEY `idx_users_active` (`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `role_id`, `full_name`, `email`, `phone`, `password_hash`, `is_active`, `last_login_at`, `created_at`, `updated_at`) VALUES
(1, 1, 'System Admin', 'admin@kanisa.local', '+255700000001', '$2y$12$su4OYImjnrhnw3MfRedfFud6D63Ucg03Zn8GW9Y6lCFc.3cE.TQky', 1, '2026-04-15 22:51:12', '2026-04-10 10:43:44', '2026-04-15 19:51:12'),
(2, 2, 'Finance Officer', 'finance@kanisa.local', '+255700000002', '$2y$12$9AaEziY1BLh96OOmcM0vPe8Bbf4yoliAdPkWaN0eTav1XMCaoWoKy', 1, '2026-04-15 22:49:02', '2026-04-10 10:43:44', '2026-04-15 19:49:02'),
(3, 3, 'Church Secretary', 'secretary@kanisa.local', '+255700000003', '$2y$12$XKak66rFipAdrgJrQ57VM..zKyp3RI9inxNnkMkHFmer51xNoCo8O', 1, '2026-04-15 22:44:48', '2026-04-10 10:43:44', '2026-04-15 19:44:48'),
(4, 4, 'Standard User', 'user@kanisa.local', '+255700000004', '$2a$12$Xp2a0FZMub7mTE8pZbZpLekolpXEJzSbCfbHjYANgydGq6Nx4H3Au', 1, NULL, '2026-04-10 10:43:44', '2026-04-14 12:43:05');

--
-- Constraints for dumped tables
--

--
-- Constraints for table `approval_workflows`
--
ALTER TABLE `approval_workflows`
  ADD CONSTRAINT `fk_aw_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `assets`
--
ALTER TABLE `assets`
  ADD CONSTRAINT `fk_assets_event` FOREIGN KEY (`assigned_event_id`) REFERENCES `events` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_assets_user` FOREIGN KEY (`assigned_to_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `asset_assignments`
--
ALTER TABLE `asset_assignments`
  ADD CONSTRAINT `fk_asset_assignment_asset` FOREIGN KEY (`asset_id`) REFERENCES `assets` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_asset_assignment_assigned_by` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_asset_assignment_event` FOREIGN KEY (`assigned_event_id`) REFERENCES `events` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_asset_assignment_user` FOREIGN KEY (`assigned_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `attendance_snapshots`
--
ALTER TABLE `attendance_snapshots`
  ADD CONSTRAINT `fk_attendance_snapshots_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `fk_audit_logs_actor` FOREIGN KEY (`actor_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `budget_expenses`
--
ALTER TABLE `budget_expenses`
  ADD CONSTRAINT `fk_be_budget` FOREIGN KEY (`budget_id`) REFERENCES `department_budgets` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `departments`
--
ALTER TABLE `departments`
  ADD CONSTRAINT `fk_departments_head` FOREIGN KEY (`head_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `department_contributions`
--
ALTER TABLE `department_contributions`
  ADD CONSTRAINT `fk_dept_contrib_dept` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_dept_contrib_member` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `department_finance_records`
--
ALTER TABLE `department_finance_records`
  ADD CONSTRAINT `fk_dept_fin_rec_dept` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `department_leaders`
--
ALTER TABLE `department_leaders`
  ADD CONSTRAINT `fk_dept_leaders_dept` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_dept_leaders_member` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `department_members`
--
ALTER TABLE `department_members`
  ADD CONSTRAINT `fk_dept_members_dept` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_dept_members_member` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `department_reports`
--
ALTER TABLE `department_reports`
  ADD CONSTRAINT `fk_dept_reports_dept` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_dept_reports_reviewed_by` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `events`
--
ALTER TABLE `events`
  ADD CONSTRAINT `fk_events_budget_approved_by` FOREIGN KEY (`budget_approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_events_organizer` FOREIGN KEY (`organizer_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_events_target_group` FOREIGN KEY (`target_group_id`) REFERENCES `groups` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `event_attendance`
--
ALTER TABLE `event_attendance`
  ADD CONSTRAINT `fk_event_att_event` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_event_att_member` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `event_budget_items`
--
ALTER TABLE `event_budget_items`
  ADD CONSTRAINT `fk_event_budget_event` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `event_finance_links`
--
ALTER TABLE `event_finance_links`
  ADD CONSTRAINT `fk_event_finance_links_entry` FOREIGN KEY (`finance_entry_id`) REFERENCES `finance_entries` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_event_finance_links_event` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `event_tasks`
--
ALTER TABLE `event_tasks`
  ADD CONSTRAINT `fk_event_tasks_event` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_event_tasks_user` FOREIGN KEY (`assigned_to_user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Constraints for table `finance_entries`
--
ALTER TABLE `finance_entries`
  ADD CONSTRAINT `fk_finance_entries_approved_by` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_finance_entries_category` FOREIGN KEY (`category_id`) REFERENCES `finance_categories` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_finance_entries_dept` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_finance_entries_event` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_finance_entries_member` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_finance_entries_po` FOREIGN KEY (`purchase_order_id`) REFERENCES `purchase_orders` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_finance_entries_recorded_by` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_finance_entries_supplier` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `groups`
--
ALTER TABLE `groups`
  ADD CONSTRAINT `fk_groups_leader_member` FOREIGN KEY (`leader_member_id`) REFERENCES `members` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `guests`
--
ALTER TABLE `guests`
  ADD CONSTRAINT `fk_guests_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_guests_invited_by` FOREIGN KEY (`invited_by_member_id`) REFERENCES `members` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `maintenance_logs`
--
ALTER TABLE `maintenance_logs`
  ADD CONSTRAINT `fk_maintenance_asset` FOREIGN KEY (`asset_id`) REFERENCES `assets` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_maintenance_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Constraints for table `members`
--
ALTER TABLE `members`
  ADD CONSTRAINT `fk_members_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_members_updated_by` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `pledges`
--
ALTER TABLE `pledges`
  ADD CONSTRAINT `fk_pledges_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pledges_member` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Constraints for table `pledge_payments`
--
ALTER TABLE `pledge_payments`
  ADD CONSTRAINT `fk_pledge_payments_entry` FOREIGN KEY (`finance_entry_id`) REFERENCES `finance_entries` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pledge_payments_pledge` FOREIGN KEY (`pledge_id`) REFERENCES `pledges` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `procurement_approvals`
--
ALTER TABLE `procurement_approvals`
  ADD CONSTRAINT `fk_procurement_approval_request` FOREIGN KEY (`purchase_request_id`) REFERENCES `purchase_requests` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_procurement_approval_user` FOREIGN KEY (`approver_user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Constraints for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  ADD CONSTRAINT `fk_purchase_orders_issued_by` FOREIGN KEY (`issued_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_purchase_orders_request` FOREIGN KEY (`purchase_request_id`) REFERENCES `purchase_requests` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_purchase_orders_supplier` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Constraints for table `purchase_order_items`
--
ALTER TABLE `purchase_order_items`
  ADD CONSTRAINT `fk_po_items_po` FOREIGN KEY (`purchase_order_id`) REFERENCES `purchase_orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `purchase_requests`
--
ALTER TABLE `purchase_requests`
  ADD CONSTRAINT `fk_purchase_requests_event` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_purchase_requests_user` FOREIGN KEY (`requested_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Constraints for table `purchase_request_items`
--
ALTER TABLE `purchase_request_items`
  ADD CONSTRAINT `fk_pri_pr` FOREIGN KEY (`purchase_request_id`) REFERENCES `purchase_requests` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD CONSTRAINT `fk_rp_perm` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rp_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `sms_logs`
--
ALTER TABLE `sms_logs`
  ADD CONSTRAINT `fk_sms_event` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_sms_member` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_sms_sent_by` FOREIGN KEY (`sent_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_users_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
