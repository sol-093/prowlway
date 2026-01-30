-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 30, 2026 at 08:05 PM
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
-- Database: `icdi_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) UNSIGNED NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `role` enum('super_admin','admin','editor') DEFAULT 'admin',
  `status` enum('active','archived') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `email`, `password`, `name`, `role`, `status`, `created_at`, `updated_at`) VALUES
(2, 'icdi.admin@kld.edu.ph', '$2y$10$vbqHIgyptACyeBHXz/ohb.BE8dvGeZ78SUiG14zllOT7cQlYonIP.', 'ICDI Admin', 'admin', 'active', '2026-01-30 01:35:57', '2026-01-30 01:35:57'),
(3, 'gitcub.editor@kld.edu.ph', '$2y$10$vbqHIgyptACyeBHXz/ohb.BE8dvGeZ78SUiG14zllOT7cQlYonIP.', 'GITCUB Editor', 'editor', 'active', '2026-01-30 01:35:57', '2026-01-30 01:35:57'),
(4, 'css.editor@kld.edu.ph', '$2y$10$vbqHIgyptACyeBHXz/ohb.BE8dvGeZ78SUiG14zllOT7cQlYonIP.', 'CSS Editor', 'editor', 'active', '2026-01-30 01:35:57', '2026-01-30 01:35:57'),
(5, 'iss.admin@kld.edu.ph', '$2y$10$vbqHIgyptACyeBHXz/ohb.BE8dvGeZ78SUiG14zllOT7cQlYonIP.', 'ISS Editor', 'editor', 'active', '2026-01-30 01:35:57', '2026-01-30 01:36:46'),
(6, 'jpcs.editor@kld.edu.ph', '$2y$10$vbqHIgyptACyeBHXz/ohb.BE8dvGeZ78SUiG14zllOT7cQlYonIP.', 'JPCS Editor', 'editor', 'active', '2026-01-30 01:35:57', '2026-01-30 01:35:57'),
(7, 'superadmin@icdisg.ph', '$2y$10$tSBPpP1kWdBoUnAGpt4g7e0TLmEbmbHc7nL/caQEY.p4a0BFzlDjm', 'ICDI Super Admin', 'super_admin', 'active', '2026-01-30 01:37:26', '2026-01-30 01:37:26'),
(8, 'iss.editor@kld.edu.ph', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'ISS Editor', 'editor', 'active', '2026-01-30 01:37:48', '2026-01-30 01:37:48');

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `id` int(11) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `content` text DEFAULT NULL,
  `category` enum('general','academic','event','maintenance','urgent') DEFAULT 'general',
  `image` varchar(500) DEFAULT NULL COMMENT 'Relative path to uploaded image file',
  `pinned` tinyint(1) DEFAULT 0,
  `is_meeting` tinyint(1) DEFAULT 0,
  `meeting_date` datetime DEFAULT NULL,
  `meeting_end_date` datetime DEFAULT NULL,
  `meeting_location` varchar(255) DEFAULT NULL,
  `status` enum('draft','pending_review','approved','published','archived') DEFAULT 'draft',
  `academic_year` varchar(20) DEFAULT NULL COMMENT 'e.g., 2024-2025',
  `created_by` int(11) UNSIGNED DEFAULT NULL,
  `reviewed_by` int(11) UNSIGNED DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `review_notes` text DEFAULT NULL,
  `approved_by` int(11) UNSIGNED DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `approval_notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `announcements`
--

INSERT INTO `announcements` (`id`, `title`, `description`, `content`, `category`, `image`, `pinned`, `is_meeting`, `meeting_date`, `meeting_end_date`, `meeting_location`, `status`, `academic_year`, `created_by`, `reviewed_by`, `reviewed_at`, `review_notes`, `approved_by`, `approved_at`, `approval_notes`, `created_at`, `updated_at`) VALUES
(1, 'Meeting', '123', '123', 'academic', 'images/img_697b5a022cfad0.14140578_1769691650.png', 0, 1, '2026-01-30 17:41:00', NULL, 'cb1 309', 'published', NULL, 7, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-29 13:00:50', '2026-01-30 04:48:02');

-- --------------------------------------------------------

--
-- Table structure for table `audit_log`
--

CREATE TABLE `audit_log` (
  `id` int(11) UNSIGNED NOT NULL,
  `admin_id` int(11) UNSIGNED DEFAULT NULL,
  `action` varchar(100) NOT NULL COMMENT 'login, logout, publish, archive, delete, settings_update, role_assign, etc.',
  `entity_type` varchar(50) DEFAULT NULL COMMENT 'announcement, document, event, organization, batch, inquiry, admin, settings',
  `entity_id` int(11) UNSIGNED DEFAULT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `audit_log`
--

INSERT INTO `audit_log` (`id`, `admin_id`, `action`, `entity_type`, `entity_id`, `details`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, NULL, 'logout', 'admin', NULL, 'Logout (admin deleted)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-30 01:37:54'),
(2, 7, 'login', 'admin', 7, 'Login successful', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-30 01:38:14'),
(3, 7, 'user_delete', 'admin', 1, 'Deleted admin #1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-30 01:39:27'),
(4, 7, 'logout', 'admin', 7, 'Logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-30 01:39:30'),
(5, 2, 'login', 'admin', 2, 'Login successful', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-30 01:40:09'),
(6, 2, 'logout', 'admin', 2, 'Logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-30 01:40:47'),
(7, 3, 'login', 'admin', 3, 'Login successful', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-30 01:41:12'),
(8, 3, 'logout', 'admin', 3, 'Logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-30 01:42:13'),
(9, 2, 'login', 'admin', 2, 'Login successful', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-30 01:42:17'),
(10, 2, 'publish', 'batch', 2, 'Batch published', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-30 01:42:32'),
(11, 2, 'logout', 'admin', 2, 'Logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-30 03:23:06'),
(12, 7, 'login', 'admin', 7, 'Login successful', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-30 03:23:11'),
(13, 7, 'login', 'admin', 7, 'Login successful', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-30 03:58:22'),
(14, 7, 'logout', 'admin', 7, 'Logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-30 04:14:55'),
(17, 2, 'login', 'admin', 2, 'Login successful', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-30 04:15:12'),
(18, 2, 'logout', 'admin', 2, 'Logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-30 04:15:18'),
(19, 7, 'login', 'admin', 7, 'Login successful', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-30 04:15:23'),
(20, 7, 'logout', 'admin', 7, 'Logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-30 04:15:41'),
(21, 2, 'login', 'admin', 2, 'Login successful', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-30 04:15:46'),
(22, 2, 'logout', 'admin', 2, 'Logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-30 04:16:03'),
(23, 3, 'login', 'admin', 3, 'Login successful', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-30 04:16:10'),
(24, 3, 'logout', 'admin', 3, 'Logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-30 04:17:55'),
(25, 2, 'login', 'admin', 2, 'Login successful', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-30 04:18:01'),
(26, 2, 'logout', 'admin', 2, 'Logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-30 04:18:59'),
(27, 7, 'login', 'admin', 7, 'Login successful', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-30 04:19:04'),
(28, 7, 'delete', 'announcement', 2, 'Announcement deleted', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-30 04:20:51'),
(29, 7, 'archive', 'announcement', 1, 'Announcement archived (bulk)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-30 04:35:38'),
(30, 7, 'restore', 'announcement', 1, 'Announcement restored from archive', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-30 04:37:41'),
(31, 7, 'publish', 'announcement', 1, 'Announcement published', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-30 04:41:31'),
(34, 3, 'login', 'admin', 3, 'Login successful', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-30 05:17:50'),
(35, 3, 'logout', 'admin', 3, 'Logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-30 05:17:55'),
(36, 7, 'login', 'admin', 7, 'Login successful', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-30 05:18:02'),
(37, 7, 'logout', 'admin', 7, 'Logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-30 05:20:13'),
(38, 2, 'login', 'admin', 2, 'Login successful', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-30 05:20:26'),
(39, 2, 'logout', 'admin', 2, 'Logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-30 05:20:39'),
(40, 7, 'login', 'admin', 7, 'Login successful', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-30 05:20:42'),
(41, 7, 'login', 'admin', 7, 'Login successful', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-30 07:45:36'),
(42, 7, 'publish', 'event', 2, 'Event published', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-30 07:47:00'),
(43, 7, 'archive', 'batch', 1, 'Batch archived', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-30 07:55:12'),
(44, 7, 'archive', 'batch', 1, 'Batch archived', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-30 07:55:24'),
(45, 7, 'archive', 'batch', 1, 'Batch archived', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-30 07:55:27'),
(46, 7, 'archive', 'batch', 1, 'Batch archived', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-30 07:55:37'),
(47, 7, 'archive', 'batch', 1, 'Batch archived', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-30 07:57:17'),
(48, 7, 'delete', 'batch', 1, 'Batch permanently deleted', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-30 07:59:46'),
(49, 7, 'publish', 'event', 3, 'Event created and published', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-30 08:49:03'),
(50, 7, 'publish', 'event', 4, 'Event created and published', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-30 08:50:09'),
(51, 7, 'login', 'admin', 7, 'Login successful', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-30 17:25:51'),
(52, 7, 'publish', 'institute_section', 1, 'Institute section created and published', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-30 19:04:15');

-- --------------------------------------------------------

--
-- Table structure for table `batches`
--

CREATE TABLE `batches` (
  `id` int(11) UNSIGNED NOT NULL,
  `organization_id` int(11) UNSIGNED DEFAULT NULL COMMENT 'student_organizations.id',
  `academic_year` varchar(50) NOT NULL COMMENT 'e.g., A.Y. 2025-2026',
  `start_year` year(4) NOT NULL,
  `end_year` year(4) NOT NULL,
  `image` varchar(500) DEFAULT NULL COMMENT 'Relative path to uploaded image file',
  `description` text DEFAULT NULL,
  `target_group` enum('all','adviser','co_adviser','executive_officer','executive_associate') DEFAULT 'all',
  `status` enum('draft','active','archived') DEFAULT 'active',
  `display_order` int(11) DEFAULT 0,
  `created_by` int(11) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `batches`
--

INSERT INTO `batches` (`id`, `organization_id`, `academic_year`, `start_year`, `end_year`, `image`, `description`, `target_group`, `status`, `display_order`, `created_by`, `created_at`, `updated_at`) VALUES
(2, 4, 'A.Y 2025-2026', '2026', '2027', 'images/img_697c0c55aa70f3.35873857_1769737301.png', '1312231', 'all', 'active', 0, 7, '2026-01-30 01:41:41', '2026-01-30 07:57:15');

-- --------------------------------------------------------

--
-- Table structure for table `batch_members`
--

CREATE TABLE `batch_members` (
  `id` int(11) UNSIGNED NOT NULL,
  `batch_id` int(11) UNSIGNED NOT NULL,
  `group_type` enum('adviser','co_adviser','executive_officer','executive_associate') NOT NULL,
  `name` varchar(255) NOT NULL,
  `position_title` varchar(255) NOT NULL,
  `image` varchar(500) DEFAULT NULL COMMENT 'Relative path to uploaded profile image',
  `display_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `batch_members`
--

INSERT INTO `batch_members` (`id`, `batch_id`, `group_type`, `name`, `position_title`, `image`, `display_order`, `created_at`, `updated_at`) VALUES
(1, 2, 'co_adviser', 'Batch member updated', 'co adviser', 'images/img_697c665c821305.59893703_1769760348.png', 2, '2026-01-30 08:03:20', '2026-01-30 08:13:00'),
(2, 2, 'adviser', 'CS Society', 'adviser', 'images/img_697c67775ccc17.66588339_1769760631.png', 0, '2026-01-30 08:10:31', '2026-01-30 08:10:31'),
(3, 2, 'executive_officer', 'Zendrick Delacruz Gango', 'Auditor', 'images/img_697c68872d8362.92654087_1769760903.png', 0, '2026-01-30 08:15:03', '2026-01-30 08:15:03'),
(4, 2, 'executive_officer', 'Zendrick Delacruz Gango', 'Governor', 'images/img_697c68a5bb2014.47796861_1769760933.png', 0, '2026-01-30 08:15:33', '2026-01-30 08:15:33'),
(5, 2, 'executive_officer', 'Zendrick Delacruz Gango', 'vice governor', 'images/img_697c68be686509.27066442_1769760958.png', 0, '2026-01-30 08:15:58', '2026-01-30 08:15:58'),
(6, 2, 'executive_officer', 'Zendrick Delacruz Gango', 'business manager', 'images/img_697c68cf751ca0.59160396_1769760975.png', 0, '2026-01-30 08:16:15', '2026-01-30 08:16:15'),
(7, 2, 'executive_officer', 'Zendrick Delacruz Gango', 'business manager', 'images/img_697c68e00e7eb5.10228068_1769760992.png', 0, '2026-01-30 08:16:32', '2026-01-30 08:16:32'),
(8, 2, 'executive_officer', 'Zendrick Delacruz Gango', 'business manager', 'images/img_697c68ec036c66.79039275_1769761004.png', 0, '2026-01-30 08:16:44', '2026-01-30 08:16:44'),
(9, 2, 'executive_associate', 'Zendrick Delacruz Gango', 'business manager', 'images/img_697c68fe1a92d1.30124698_1769761022.png', 0, '2026-01-30 08:17:02', '2026-01-30 08:17:02'),
(10, 2, 'executive_associate', 'Zendrick Delacruz Gango', 'business manager', 'images/img_697c690b3f6019.09269427_1769761035.png', 0, '2026-01-30 08:17:15', '2026-01-30 08:17:15'),
(11, 2, 'executive_associate', 'Zendrick Delacruz Gango', 'co adviser', 'images/img_697c6919c56011.07361063_1769761049.png', 0, '2026-01-30 08:17:29', '2026-01-30 08:29:04');

-- --------------------------------------------------------

--
-- Table structure for table `contact_inquiries`
--

CREATE TABLE `contact_inquiries` (
  `id` int(11) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `message` text NOT NULL,
  `status` enum('open','closed','archived') DEFAULT 'open',
  `response_text` text DEFAULT NULL,
  `responded_at` timestamp NULL DEFAULT NULL,
  `responded_by` int(11) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `contact_inquiries`
--

INSERT INTO `contact_inquiries` (`id`, `name`, `email`, `subject`, `message`, `status`, `response_text`, `responded_at`, `responded_by`, `created_at`, `updated_at`) VALUES
(1, 'CS Society', 'zendrick03gango@kld.edu.ph', '124', '123', 'open', NULL, NULL, NULL, '2026-01-30 01:32:47', '2026-01-30 01:32:47');

-- --------------------------------------------------------

--
-- Table structure for table `documents`
--

CREATE TABLE `documents` (
  `id` int(11) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `category` enum('01','02','03','04','05') NOT NULL COMMENT '01=OFFICES REPORT, 02=EXECUTIVE ORDER, 03=ORDINANCE, 04=RESOLUTION, 05=OTHER',
  `subcategory` varchar(100) DEFAULT NULL COMMENT 'OTP, OVIA, OVPEA, OS, OTA, OBPR, Media Publication, Arts Craft, Documentation, Business, etc.',
  `series_year` varchar(20) DEFAULT NULL COMMENT 'e.g., 2025',
  `document_type` enum('executive_order','administrative_order','memorandum') DEFAULT NULL COMMENT 'For Orders category (02)',
  `academic_year` varchar(20) DEFAULT NULL COMMENT 'e.g., 2024-2025',
  `file_path` varchar(500) DEFAULT NULL COMMENT 'Relative path to uploaded PDF file',
  `file_size` bigint(20) DEFAULT NULL COMMENT 'File size in bytes',
  `file_type` varchar(50) DEFAULT NULL,
  `status` enum('draft','pending_review','approved','published','archived') DEFAULT 'draft',
  `created_by` int(11) UNSIGNED DEFAULT NULL,
  `reviewed_by` int(11) UNSIGNED DEFAULT NULL COMMENT 'Admin ID who reviewed the document',
  `reviewed_at` timestamp NULL DEFAULT NULL COMMENT 'When document was reviewed',
  `review_notes` text DEFAULT NULL COMMENT 'Review comments/notes',
  `approved_by` int(11) UNSIGNED DEFAULT NULL COMMENT 'Admin ID who approved the document',
  `approved_at` timestamp NULL DEFAULT NULL COMMENT 'When document was approved',
  `approval_notes` text DEFAULT NULL COMMENT 'Approval comments/notes',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `documents`
--

INSERT INTO `documents` (`id`, `title`, `description`, `category`, `subcategory`, `series_year`, `document_type`, `academic_year`, `file_path`, `file_size`, `file_type`, `status`, `created_by`, `reviewed_by`, `reviewed_at`, `review_notes`, `approved_by`, `approved_at`, `approval_notes`, `created_at`, `updated_at`) VALUES
(1, '123123123', '345', '01', NULL, NULL, NULL, NULL, 'documents/doc_697b5cf47aca74.10396727_1769692404.pdf', 809982, 'application/pdf', 'published', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-29 13:13:24', '2026-01-29 13:47:17');

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `id` int(11) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `caption` varchar(500) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `summary` text DEFAULT NULL,
  `category` enum('workshop','seminar','service','celebration','other') DEFAULT 'other',
  `schedule_type` enum('event','enrollment','school_break','school_end','start_of_classes','exam_period') NOT NULL DEFAULT 'event' COMMENT 'What happens this day: Enrollment, School Break, School End, etc.',
  `image` varchar(500) NOT NULL COMMENT 'Relative path to uploaded main image',
  `gallery` text DEFAULT NULL COMMENT 'JSON array of relative paths to uploaded gallery images',
  `date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `display_order` int(11) DEFAULT 0,
  `status` enum('draft','pending_review','approved','published','archived') DEFAULT 'draft',
  `academic_year` varchar(20) DEFAULT NULL COMMENT 'e.g., 2024-2025',
  `created_by` int(11) UNSIGNED DEFAULT NULL,
  `reviewed_by` int(11) UNSIGNED DEFAULT NULL COMMENT 'Admin ID who reviewed the event',
  `reviewed_at` timestamp NULL DEFAULT NULL COMMENT 'When event was reviewed',
  `review_notes` text DEFAULT NULL COMMENT 'Review comments/notes',
  `approved_by` int(11) UNSIGNED DEFAULT NULL COMMENT 'Admin ID who approved the event',
  `approved_at` timestamp NULL DEFAULT NULL COMMENT 'When event was approved',
  `approval_notes` text DEFAULT NULL COMMENT 'Approval comments/notes',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`id`, `title`, `caption`, `description`, `summary`, `category`, `schedule_type`, `image`, `gallery`, `date`, `end_date`, `location`, `display_order`, `status`, `academic_year`, `created_by`, `reviewed_by`, `reviewed_at`, `review_notes`, `approved_by`, `approved_at`, `approval_notes`, `created_at`, `updated_at`) VALUES
(2, '123', '123', '<p>Lorem ipsum dolor sit amet. Nam ipsam libero ut reprehenderit magnam ut consequuntur animi qui optio commodi qui voluptatem nulla et doloremque quisquam nam odio maiores. Et mollitia sint ut error error eum autem rerum ab sequi vero. Non placeat modi sit magni debitis est praesentium dolorum nam perferendis aspernatur quo placeat quas eum voluptate quisquam. </p><p>Et enim cumque quo quisquam quae est molestiae exercitationem qui nemo laboriosam. Sed consequatur fugit ea Quis alias id quis vitae et doloremque laborum. </p><p>Ut odio ipsam qui consequatur quasi a sunt cupiditate est voluptas corporis At enim dolorem et placeat porro? Nam officiis officia ut galisum doloremque et deleniti nostrum aut voluptates illo! </p>\r\n', '<p>Lorem ipsum dolor sit amet. Nam ipsam libero ut reprehenderit magnam ut consequuntur animi qui optio commodi qui voluptatem nulla et doloremque quisquam nam odio maiores. Et mollitia sint ut error error eum autem rerum ab sequi vero. Non placeat modi sit magni debitis est praesentium dolorum nam perferendis aspernatur quo placeat quas eum voluptate quisquam. </p><p>Et enim cumque quo quisquam quae est molestiae exercitationem qui nemo laboriosam. Sed consequatur fugit ea Quis alias id quis vitae et doloremque laborum. </p><p>Ut odio ipsam qui consequatur quasi a sunt cupiditate est voluptas corporis At enim dolorem et placeat porro? Nam officiis officia ut galisum doloremque et deleniti nostrum aut voluptates illo! </p>\r\n', 'service', 'event', 'images/img_697bed9f7a1b01.33693340_1769729439.png', '[\"images\\/img_697bed9f7a66c4.42230535_1769729439.png\",\"images\\/img_697bed9f7abdc3.58164822_1769729439.png\",\"images\\/img_697bed9f7aeb16.47373279_1769729439.png\",\"images\\/img_697bed9f7b1253.26797268_1769729439.png\",\"images\\/img_697bed9f7f7529.36375870_1769729439.png\",\"images\\/img_697bed9f7fac94.99555281_1769729439.png\",\"images\\/img_697bed9f7fd8e1.20610900_1769729439.png\"]', '2026-01-06', NULL, '', 0, 'published', NULL, 7, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-29 23:30:39', '2026-01-30 07:47:00'),
(3, 'ito ang title ', 'ito ang title ', '<p>Lorem ipsum dolor sit amet. Ab tenetur velit non maiores esse sit reprehenderit iste ut consequatur architecto et nostrum obcaecati! Aut iure nostrum ut repudiandae amet quo impedit eveniet. Cum quidem optio hic natus excepturi qui itaque quia et minus autem et minima necessitatibus. Eos libero voluptate rem corporis dolor qui tenetur rerum. </p><p>Et autem magnam et iste Quis et doloribus magnam ex accusamus voluptatem est quibusdam explicabo. Cum alias rerum rem natus nihil ea distinctio rerum ad quam quis. Ut laborum culpa ea obcaecati aspernatur qui quibusdam quisquam sed nobis aspernatur aut voluptatem quidem id repudiandae delectus. </p><p>Sit distinctio magnam eum quam magni sit dignissimos mollitia non dolorum magni qui eveniet nihil quo mollitia voluptates. Ad officiis minima qui velit voluptas aut quia quisquam sit voluptate enim qui quia quia 33 unde galisum. Est neque eligendi eos debitis voluptatem sit voluptatem consequatur aut porro deserunt! </p>\r\n', '<p>Lorem ipsum dolor sit amet. Ab tenetur velit non maiores esse sit reprehenderit iste ut consequatur architecto et nostrum obcaecati! Aut iure nostrum ut repudiandae amet quo impedit eveniet. Cum quidem optio hic natus excepturi qui itaque quia et minus autem et minima necessitatibus. Eos libero voluptate rem corporis dolor qui tenetur rerum. </p><p>Et autem magnam et iste Quis et doloribus magnam ex accusamus voluptatem est quibusdam explicabo. Cum alias rerum rem natus nihil ea distinctio rerum ad quam quis. Ut laborum culpa ea obcaecati aspernatur qui quibusdam quisquam sed nobis aspernatur aut voluptatem quidem id repudiandae delectus. </p><p>Sit distinctio magnam eum quam magni sit dignissimos mollitia non dolorum magni qui eveniet nihil quo mollitia voluptates. Ad officiis minima qui velit voluptas aut quia quisquam sit voluptate enim qui quia quia 33 unde galisum. Est neque eligendi eos debitis voluptatem sit voluptatem consequatur aut porro deserunt! </p>\r\n', 'workshop', 'event', 'images/img_697c707fa70dd4.11447913_1769762943.jpg', NULL, '2026-01-31', NULL, 'Aurora St., Makati City', 0, 'published', NULL, 7, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-30 08:49:03', '2026-01-30 08:49:03'),
(4, 'asdasdasda', 'ito ang title ', '<p>Lorem ipsum dolor sit amet. Ab tenetur velit non maiores esse sit reprehenderit iste ut consequatur architecto et nostrum obcaecati! Aut iure nostrum ut repudiandae amet quo impedit eveniet. Cum quidem optio hic natus excepturi qui itaque quia et minus autem et minima necessitatibus. Eos libero voluptate rem corporis dolor qui tenetur rerum. </p><p>Et autem magnam et iste Quis et doloribus magnam ex accusamus voluptatem est quibusdam explicabo. Cum alias rerum rem natus nihil ea distinctio rerum ad quam quis. Ut laborum culpa ea obcaecati aspernatur qui quibusdam quisquam sed nobis aspernatur aut voluptatem quidem id repudiandae delectus. </p><p>Sit distinctio magnam eum quam magni sit dignissimos mollitia non dolorum magni qui eveniet nihil quo mollitia voluptates. Ad officiis minima qui velit voluptas aut quia quisquam sit voluptate enim qui quia quia 33 unde galisum. Est neque eligendi eos debitis voluptatem sit voluptatem consequatur aut porro deserunt! </p>\r\n', '<p>Lorem ipsum dolor sit amet. Ab tenetur velit non maiores esse sit reprehenderit iste ut consequatur architecto et nostrum obcaecati! Aut iure nostrum ut repudiandae amet quo impedit eveniet. Cum quidem optio hic natus excepturi qui itaque quia et minus autem et minima necessitatibus. Eos libero voluptate rem corporis dolor qui tenetur rerum. </p><p>Et autem magnam et iste Quis et doloribus magnam ex accusamus voluptatem est quibusdam explicabo. Cum alias rerum rem natus nihil ea distinctio rerum ad quam quis. Ut laborum culpa ea obcaecati aspernatur qui quibusdam quisquam sed nobis aspernatur aut voluptatem quidem id repudiandae delectus. </p><p>Sit distinctio magnam eum quam magni sit dignissimos mollitia non dolorum magni qui eveniet nihil quo mollitia voluptates. Ad officiis minima qui velit voluptas aut quia quisquam sit voluptate enim qui quia quia 33 unde galisum. Est neque eligendi eos debitis voluptatem sit voluptatem consequatur aut porro deserunt! </p>\r\n', 'workshop', 'event', 'images/img_697c70c1401780.50778454_1769763009.png', NULL, '2026-02-07', NULL, 'Guadalupe, Makaci City', 0, 'published', NULL, 7, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-30 08:50:09', '2026-01-30 08:50:09');

-- --------------------------------------------------------

--
-- Table structure for table `holidays`
--

CREATE TABLE `holidays` (
  `id` int(11) UNSIGNED NOT NULL,
  `date` date NOT NULL,
  `end_date` date DEFAULT NULL COMMENT 'End date for multi-day (e.g. Christmas break from-to)',
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL COMMENT 'Description (e.g. Enrollment day details)',
  `type` enum('regular','special_non_working','special_working','dasma','enrollment','wellness_break','christmas_break','year_end','school_end','start_of_school','school') NOT NULL DEFAULT 'regular',
  `type_label` varchar(255) DEFAULT NULL COMMENT 'Custom type label (e.g. when type=school, or override for school types)',
  `region` varchar(20) NOT NULL DEFAULT 'PH' COMMENT 'PH = Philippines, Dasma = Dasmariñas City',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `holidays`
--

INSERT INTO `holidays` (`id`, `date`, `end_date`, `name`, `description`, `type`, `type_label`, `region`, `created_at`) VALUES
(1, '2024-01-01', NULL, 'New Year\'s Day', NULL, 'regular', NULL, 'PH', '2026-01-30 00:23:56'),
(2, '2024-02-25', NULL, 'EDSA Revolution Anniversary', NULL, 'special_working', NULL, 'PH', '2026-01-30 00:23:56'),
(3, '2024-04-09', NULL, 'Araw ng Kagitingan', NULL, 'regular', NULL, 'PH', '2026-01-30 00:23:56'),
(4, '2024-05-01', NULL, 'Labor Day', NULL, 'regular', NULL, 'PH', '2026-01-30 00:23:56'),
(5, '2024-06-12', NULL, 'Independence Day', NULL, 'regular', NULL, 'PH', '2026-01-30 00:23:56'),
(6, '2024-08-21', NULL, 'Ninoy Aquino Day', NULL, 'special_non_working', NULL, 'PH', '2026-01-30 00:23:56'),
(7, '2024-08-26', NULL, 'National Heroes Day', NULL, 'regular', NULL, 'PH', '2026-01-30 00:23:56'),
(8, '2024-11-26', NULL, 'Foundation Day of Dasmariñas City', NULL, 'special_non_working', NULL, 'Dasma', '2026-01-30 00:23:56'),
(9, '2024-11-30', NULL, 'Bonifacio Day', NULL, 'regular', NULL, 'PH', '2026-01-30 00:23:56'),
(10, '2024-12-25', NULL, 'Christmas Day', NULL, 'regular', NULL, 'PH', '2026-01-30 00:23:56'),
(11, '2024-12-30', NULL, 'Rizal Day', NULL, 'regular', NULL, 'PH', '2026-01-30 00:23:56'),
(12, '2024-12-31', NULL, 'Last Day of the Year', NULL, 'special_non_working', NULL, 'PH', '2026-01-30 00:23:56'),
(13, '2025-01-01', NULL, 'New Year\'s Day', NULL, 'regular', NULL, 'PH', '2026-01-30 00:23:56'),
(14, '2025-02-25', NULL, 'EDSA Revolution Anniversary', NULL, 'special_working', NULL, 'PH', '2026-01-30 00:23:56'),
(15, '2025-04-18', NULL, 'Good Friday', NULL, 'regular', NULL, 'PH', '2026-01-30 00:23:56'),
(16, '2025-04-09', NULL, 'Araw ng Kagitingan', NULL, 'regular', NULL, 'PH', '2026-01-30 00:23:56'),
(17, '2025-05-01', NULL, 'Labor Day', NULL, 'regular', NULL, 'PH', '2026-01-30 00:23:56'),
(18, '2025-06-12', NULL, 'Independence Day', NULL, 'regular', NULL, 'PH', '2026-01-30 00:23:56'),
(19, '2025-08-21', NULL, 'Ninoy Aquino Day', NULL, 'special_non_working', NULL, 'PH', '2026-01-30 00:23:56'),
(20, '2025-08-25', NULL, 'National Heroes Day', NULL, 'regular', NULL, 'PH', '2026-01-30 00:23:56'),
(21, '2025-11-26', NULL, 'Foundation Day of Dasmariñas City', NULL, 'special_non_working', NULL, 'Dasma', '2026-01-30 00:23:56'),
(22, '2025-11-30', NULL, 'Bonifacio Day', NULL, 'regular', NULL, 'PH', '2026-01-30 00:23:56'),
(23, '2025-12-25', NULL, 'Christmas Day', NULL, 'regular', NULL, 'PH', '2026-01-30 00:23:56'),
(24, '2025-12-30', NULL, 'Rizal Day', NULL, 'regular', NULL, 'PH', '2026-01-30 00:23:56'),
(25, '2025-12-31', NULL, 'Last Day of the Year', NULL, 'special_non_working', NULL, 'PH', '2026-01-30 00:23:56'),
(26, '2026-01-01', NULL, 'New Year\'s Day', NULL, 'regular', NULL, 'PH', '2026-01-30 00:23:56'),
(27, '2026-02-25', NULL, 'EDSA Revolution Anniversary', NULL, 'special_working', NULL, 'PH', '2026-01-30 00:23:56'),
(28, '2026-04-02', NULL, 'Maundy Thursday', NULL, 'regular', NULL, 'PH', '2026-01-30 00:23:56'),
(29, '2026-04-03', NULL, 'Good Friday', NULL, 'regular', NULL, 'PH', '2026-01-30 00:23:56'),
(30, '2026-04-04', NULL, 'Black Saturday', NULL, 'special_non_working', NULL, 'PH', '2026-01-30 00:23:56'),
(31, '2026-04-09', NULL, 'Araw ng Kagitingan', NULL, 'regular', NULL, 'PH', '2026-01-30 00:23:56'),
(32, '2026-05-01', NULL, 'Labor Day', NULL, 'regular', NULL, 'PH', '2026-01-30 00:23:56'),
(33, '2026-06-12', NULL, 'Independence Day', NULL, 'regular', NULL, 'PH', '2026-01-30 00:23:56'),
(34, '2026-08-21', NULL, 'Ninoy Aquino Day', NULL, 'special_non_working', NULL, 'PH', '2026-01-30 00:23:56'),
(35, '2026-08-31', NULL, 'National Heroes Day', NULL, 'regular', NULL, 'PH', '2026-01-30 00:23:56'),
(36, '2026-11-26', NULL, 'Foundation Day of Dasmariñas City', NULL, 'special_non_working', NULL, 'Dasma', '2026-01-30 00:23:56'),
(37, '2026-11-30', NULL, 'Bonifacio Day', NULL, 'regular', NULL, 'PH', '2026-01-30 00:23:56'),
(38, '2026-12-25', NULL, 'Christmas Day', NULL, 'regular', NULL, 'PH', '2026-01-30 00:23:56'),
(39, '2026-12-30', NULL, 'Rizal Day', NULL, 'regular', NULL, 'PH', '2026-01-30 00:23:56'),
(40, '2026-12-31', NULL, 'Last Day of the Year', NULL, 'special_non_working', NULL, 'PH', '2026-01-30 00:23:56'),
(42, '2026-01-14', '2026-01-21', '1st year enrollment', '1231231231231231When \"School (enter type below)\" is selected, enter the type here. For other school types this overrides the default label.', 'school', '1st year enrollment', 'PH', '2026-01-30 00:41:35');

-- --------------------------------------------------------

--
-- Table structure for table `institute_info`
--

CREATE TABLE `institute_info` (
  `id` int(11) UNSIGNED NOT NULL,
  `section` varchar(100) NOT NULL COMMENT 'about, mission, vision, logo',
  `title` varchar(255) DEFAULT NULL,
  `content` text DEFAULT NULL,
  `image` varchar(500) DEFAULT NULL COMMENT 'Relative path to uploaded image file',
  `display_order` int(11) DEFAULT 0,
  `status` enum('draft','published') DEFAULT 'published',
  `updated_by` int(11) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `institute_info`
--

INSERT INTO `institute_info` (`id`, `section`, `title`, `content`, `image`, `display_order`, `status`, `updated_by`, `created_at`, `updated_at`) VALUES
(1, 'about', 'About', 'The Institute Of Computing And Digital Innovation (ICDI) was established in 2020, with roots in Kolehiyo Ng Lungsod Ng Dasmariñas (KLD). It has evolved through various iterations including the Institute Of Information And Computing Sciences (IICS) and the Institute Of Mathematical Application And Computing Sciences (IMACS), culminating in its current role as a Dynamic Academic Hub.', NULL, 1, 'published', NULL, '2026-01-29 12:02:14', '2026-01-29 12:02:14'),
(2, 'mission', 'Mission', 'Empower Student Leadership Through Forward-Thinking Initiatives That Inspire Creativity And Progress. Solidify The Institute\'s Reputation As A Leader In Both Academic And Extracurricular Excellence.', NULL, 2, 'published', NULL, '2026-01-29 12:02:14', '2026-01-29 12:02:14'),
(3, 'vision', 'Vision', 'To Effectively Bridge The Nodes Of Communication And Collaboration Among The Student Body, Organizations, Committees, And Administration, Both Within And Outside The Institution By Leveraging The Power Of Knowledge, Leadership, And Dedication.', NULL, 3, 'published', NULL, '2026-01-29 12:02:14', '2026-01-29 12:02:14'),
(4, 'logo', 'Logo', 'Lorem Ipsum Dolor Sit Amet, Consectetur Adipiscing Elit, Sed Do Eiusmod Tempor Incididunt Ut Labore Et Dolore Magna Aliqua.', 'images/img_697bf389255db0.47581274_1769730953.jpg', 4, 'published', NULL, '2026-01-29 12:02:14', '2026-01-29 23:55:53'),
(5, 'banner', 'Banner', '', 'images/img_697bf3969f3ff5.33128309_1769730966.png', 0, 'published', NULL, '2026-01-29 23:56:06', '2026-01-29 23:56:06');

-- --------------------------------------------------------

--
-- Table structure for table `institute_sections`
--

CREATE TABLE `institute_sections` (
  `id` int(11) UNSIGNED NOT NULL,
  `type` enum('faculty_unit','admin_representative','program') NOT NULL,
  `title` varchar(255) NOT NULL,
  `position_title` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `content` text DEFAULT NULL,
  `image` varchar(500) DEFAULT NULL COMMENT 'Relative path to uploaded image file',
  `display_order` int(11) DEFAULT 0,
  `status` enum('draft','published') DEFAULT 'published',
  `created_by` int(11) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `institute_sections`
--

INSERT INTO `institute_sections` (`id`, `type`, `title`, `position_title`, `description`, `content`, `image`, `display_order`, `status`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'faculty_unit', '123', '123', '123', '123', NULL, 0, 'published', 7, '2026-01-30 19:04:15', '2026-01-30 19:04:15');

-- --------------------------------------------------------

--
-- Table structure for table `organization_core_values`
--

CREATE TABLE `organization_core_values` (
  `id` int(11) UNSIGNED NOT NULL,
  `organization_id` int(11) UNSIGNED NOT NULL,
  `icon` varchar(500) DEFAULT NULL COMMENT 'Relative path to uploaded icon image',
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `display_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `organization_core_values`
--

INSERT INTO `organization_core_values` (`id`, `organization_id`, `icon`, `title`, `description`, `display_order`, `created_at`, `updated_at`) VALUES
(6, 2, 'images/img_697bf1f21320c7.57036209_1769730546.jpg', 'IYO ANG TITLE', 'To Effectively Bridge The Nodes Of Communication And Collaboration Among The Student Body, Organizations, Committees, And Administration, Both Within And Outside The Institution By Leveraging The Power Of Knowledge, Leadership, And Dedication.', 0, '2026-01-30 09:07:20', '2026-01-30 09:07:20'),
(7, 2, 'images/img_697bf1f2140fa3.18138641_1769730546.jpg', 'IYO ANG TITLE', 'To Effectively Bridge The Nodes Of Communication And Collaboration Among The Student Body, Organizations, Committees, And Administration, Both Within And Outside The Institution By Leveraging The Power Of Knowledge, Leadership, And Dedication.', 1, '2026-01-30 09:07:20', '2026-01-30 09:07:20'),
(8, 2, 'images/img_697bf1f214c706.03656923_1769730546.jpg', 'IYO ANG TITLE', 'To Effectively Bridge The Nodes Of Communication And Collaboration Among The Student Body, Organizations, Committees, And Administration, Both Within And Outside The Institution By Leveraging The Power Of Knowledge, Leadership, And Dedication.', 2, '2026-01-30 09:07:20', '2026-01-30 09:07:20'),
(9, 2, 'images/img_697bf1f2156a72.51226571_1769730546.png', 'MAMA MO TITLE', 'To Effectively Bridge The Nodes Of Communication And Collaboration Among The Student Body, Organizations, Committees, And Administration, Both Within And Outside The Institution By Leveraging The Power Of Knowledge, Leadership, And Dedication.', 3, '2026-01-30 09:07:20', '2026-01-30 09:07:20');

-- --------------------------------------------------------

--
-- Table structure for table `site_settings`
--

CREATE TABLE `site_settings` (
  `id` int(11) UNSIGNED NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `setting_type` enum('text','number','boolean','json') DEFAULT 'text',
  `description` varchar(500) DEFAULT NULL,
  `updated_by` int(11) UNSIGNED DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `site_settings`
--

INSERT INTO `site_settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `description`, `updated_by`, `updated_at`) VALUES
(1, 'site_name', 'PROWLWAY', 'text', 'Website name', NULL, '2026-01-29 12:02:15'),
(2, 'site_description', 'ICDISG Archive Website', 'text', 'Website description', NULL, '2026-01-29 12:02:15'),
(3, 'contact_email', 'imacsac@kidduph', 'text', 'Contact email address', NULL, '2026-01-29 12:02:15'),
(4, 'maintenance_mode', '0', 'boolean', 'Enable maintenance mode', NULL, '2026-01-29 12:02:15');

-- --------------------------------------------------------

--
-- Table structure for table `student_organizations`
--

CREATE TABLE `student_organizations` (
  `id` int(11) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `acronym` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `mission` text DEFAULT NULL,
  `vision` text DEFAULT NULL,
  `content` text DEFAULT NULL COMMENT 'Core values (one per line)',
  `logo` varchar(500) DEFAULT NULL COMMENT 'Relative path to uploaded logo image',
  `banner_image` varchar(500) DEFAULT NULL COMMENT 'Landscape hero image (shown above About on org page)',
  `website` varchar(500) DEFAULT NULL,
  `social_media` text DEFAULT NULL COMMENT 'JSON object with social media links',
  `display_order` int(11) DEFAULT 0,
  `status` enum('draft','active','archived') DEFAULT 'active',
  `created_by` int(11) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `student_organizations`
--

INSERT INTO `student_organizations` (`id`, `name`, `acronym`, `description`, `mission`, `vision`, `content`, `logo`, `banner_image`, `website`, `social_media`, `display_order`, `status`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'ICDI Student Government', 'ICDISG', 'The official student government of the Institute of Computing and Digital Innovation', NULL, NULL, '', 'images/img_697be78e6255c8.35536392_1769727886.png', 'images/img_697bee6a997750.76772622_1769729642.png', '', NULL, 1, 'active', NULL, '2026-01-29 12:02:15', '2026-01-29 23:34:02'),
(2, 'CS Society', 'CSS', '<p>Lorem ipsum dolor sit amet. Et quidem cupiditate ut galisum dignissimos et velit eligendi et minima tenetur et nemo dignissimos. Ea sunt numquam in eveniet sunt est dignissimos incidunt ut dolores nobis est numquam ipsum! Sit repellat necessitatibus qui odit adipisci cum alias incidunt sit nulla optio. Non quod alias hic obcaecati enim aut itaque internos est quia quibusdam qui molestiae fugit aut dolor facere et sint iste. </p><p>Ut enim vero quo cupiditate sint est perferendis minus eos repellat voluptas hic doloremque nihil et quis iusto aut quibusdam dolore. At dolorum atque qui quasi molestiae aut voluptatem perferendis aut quas quia? Ea nobis dolorum eos dolor deleniti rem nobis quisquam qui officia nisi rem suscipit quam aut voluptas blanditiis. </p><p>Qui praesentium dolorum ut omnis eligendi cum provident saepe? Sit voluptatem quisquam qui exercitationem sint et veniam voluptatem. Eos voluptas iure in libero aliquam ut dolore officia ut aperiam dolores. </p>\r\n', 'Empower Student Leadership Through Forward-Thinking Initiatives That Inspire Creativity And Progress. Solidify The Institute\'s Reputation As A Leader In Both Academic And Extracurricular Excellence.', 'To Effectively Bridge The Nodes Of Communication And Collaboration Among The Student Body, Organizations, Committees, And Administration, Both Within And Outside The Institution By Leveraging The Power Of Knowledge, Leadership, And Dedication.\r\n', '', 'images/img_697be785d35d70.38210289_1769727877.png', 'images/img_697bf028ca1272.91783756_1769730088.png', '', NULL, 2, 'active', 7, '2026-01-29 12:02:15', '2026-01-30 09:07:20'),
(3, 'IS Society', 'ISS', 'Information Systems Society', NULL, NULL, '', 'images/img_697be79bc9bfb5.10416983_1769727899.png', NULL, '', NULL, 3, 'active', NULL, '2026-01-29 12:02:15', '2026-01-29 23:04:59'),
(4, 'GITCUB', 'GITCUB', 'Google IT Community University Branch', NULL, NULL, '', 'images/img_697be7a638b0c1.09519661_1769727910.png', NULL, '', NULL, 4, 'active', NULL, '2026-01-29 12:02:15', '2026-01-29 23:05:10'),
(5, 'JPCS', 'JPCS', 'Junior Philippine Computer Society', NULL, NULL, '', 'images/img_697be7acf324b0.58611005_1769727916.png', NULL, '', NULL, 5, 'active', NULL, '2026-01-29 12:02:15', '2026-01-29 23:05:17');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_category` (`category`),
  ADD KEY `idx_pinned` (`pinned`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `fk_announcements_admin` (`created_by`),
  ADD KEY `idx_announcements_published_pinned` (`status`,`pinned`,`created_at`),
  ADD KEY `idx_announcements_status_created` (`status`,`created_at`),
  ADD KEY `idx_announcements_academic_year` (`academic_year`),
  ADD KEY `fk_announcements_reviewed_by` (`reviewed_by`),
  ADD KEY `fk_announcements_approved_by` (`approved_by`);
ALTER TABLE `announcements` ADD FULLTEXT KEY `ft_search` (`title`,`description`,`content`);

--
-- Indexes for table `audit_log`
--
ALTER TABLE `audit_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_audit_admin` (`admin_id`),
  ADD KEY `idx_audit_action` (`action`),
  ADD KEY `idx_audit_entity` (`entity_type`,`entity_id`),
  ADD KEY `idx_audit_created` (`created_at`);

--
-- Indexes for table `batches`
--
ALTER TABLE `batches`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_academic_year` (`academic_year`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_display_order` (`display_order`),
  ADD KEY `fk_batches_admin` (`created_by`),
  ADD KEY `idx_batches_org` (`organization_id`);

--
-- Indexes for table `batch_members`
--
ALTER TABLE `batch_members`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_batch_group` (`batch_id`,`group_type`,`display_order`);

--
-- Indexes for table `contact_inquiries`
--
ALTER TABLE `contact_inquiries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_inquiries_status` (`status`),
  ADD KEY `idx_inquiries_created` (`created_at`),
  ADD KEY `fk_inquiries_responded_by` (`responded_by`),
  ADD KEY `idx_inquiries_status_created` (`status`,`created_at`);

--
-- Indexes for table `documents`
--
ALTER TABLE `documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_category` (`category`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `fk_documents_admin` (`created_by`),
  ADD KEY `idx_documents_published_category` (`status`,`category`,`created_at`),
  ADD KEY `idx_documents_academic_year` (`academic_year`),
  ADD KEY `idx_documents_subcategory` (`subcategory`),
  ADD KEY `fk_documents_reviewed_by` (`reviewed_by`),
  ADD KEY `fk_documents_approved_by` (`approved_by`);
ALTER TABLE `documents` ADD FULLTEXT KEY `ft_search` (`title`,`description`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_category` (`category`),
  ADD KEY `idx_schedule_type` (`schedule_type`),
  ADD KEY `idx_date` (`date`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_display_order` (`display_order`),
  ADD KEY `fk_events_admin` (`created_by`),
  ADD KEY `idx_events_published_date` (`status`,`date`,`display_order`),
  ADD KEY `idx_events_status_date` (`status`,`date`),
  ADD KEY `idx_events_academic_year` (`academic_year`),
  ADD KEY `fk_events_reviewed_by` (`reviewed_by`),
  ADD KEY `fk_events_approved_by` (`approved_by`);
ALTER TABLE `events` ADD FULLTEXT KEY `ft_search` (`title`,`caption`,`description`);

--
-- Indexes for table `holidays`
--
ALTER TABLE `holidays`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_holidays_date` (`date`),
  ADD KEY `idx_holidays_region` (`region`);

--
-- Indexes for table `institute_info`
--
ALTER TABLE `institute_info`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_section` (`section`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `fk_institute_admin` (`updated_by`);

--
-- Indexes for table `institute_sections`
--
ALTER TABLE `institute_sections`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_type` (`type`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `fk_institute_sections_admin` (`created_by`),
  ADD KEY `idx_institute_sections_type_status_order` (`type`,`status`,`display_order`);

--
-- Indexes for table `organization_core_values`
--
ALTER TABLE `organization_core_values`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_org_core_values_org` (`organization_id`);

--
-- Indexes for table `site_settings`
--
ALTER TABLE `site_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`),
  ADD KEY `fk_settings_admin` (`updated_by`);

--
-- Indexes for table `student_organizations`
--
ALTER TABLE `student_organizations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_display_order` (`display_order`),
  ADD KEY `fk_organizations_admin` (`created_by`),
  ADD KEY `idx_orgs_status_order` (`status`,`display_order`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `audit_log`
--
ALTER TABLE `audit_log`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT for table `batches`
--
ALTER TABLE `batches`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `batch_members`
--
ALTER TABLE `batch_members`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `contact_inquiries`
--
ALTER TABLE `contact_inquiries`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `documents`
--
ALTER TABLE `documents`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `holidays`
--
ALTER TABLE `holidays`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `institute_info`
--
ALTER TABLE `institute_info`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `institute_sections`
--
ALTER TABLE `institute_sections`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `organization_core_values`
--
ALTER TABLE `organization_core_values`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `site_settings`
--
ALTER TABLE `site_settings`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `student_organizations`
--
ALTER TABLE `student_organizations`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `announcements`
--
ALTER TABLE `announcements`
  ADD CONSTRAINT `fk_announcements_admin` FOREIGN KEY (`created_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_announcements_approved_by` FOREIGN KEY (`approved_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_announcements_reviewed_by` FOREIGN KEY (`reviewed_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `audit_log`
--
ALTER TABLE `audit_log`
  ADD CONSTRAINT `fk_audit_log_admin` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `batches`
--
ALTER TABLE `batches`
  ADD CONSTRAINT `fk_batches_admin` FOREIGN KEY (`created_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_batches_org` FOREIGN KEY (`organization_id`) REFERENCES `student_organizations` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `batch_members`
--
ALTER TABLE `batch_members`
  ADD CONSTRAINT `fk_batch_members_batch` FOREIGN KEY (`batch_id`) REFERENCES `batches` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `contact_inquiries`
--
ALTER TABLE `contact_inquiries`
  ADD CONSTRAINT `fk_inquiries_responded_by` FOREIGN KEY (`responded_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `documents`
--
ALTER TABLE `documents`
  ADD CONSTRAINT `fk_documents_admin` FOREIGN KEY (`created_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_documents_approved_by` FOREIGN KEY (`approved_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_documents_reviewed_by` FOREIGN KEY (`reviewed_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `events`
--
ALTER TABLE `events`
  ADD CONSTRAINT `fk_events_admin` FOREIGN KEY (`created_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_events_approved_by` FOREIGN KEY (`approved_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_events_reviewed_by` FOREIGN KEY (`reviewed_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `institute_info`
--
ALTER TABLE `institute_info`
  ADD CONSTRAINT `fk_institute_admin` FOREIGN KEY (`updated_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `institute_sections`
--
ALTER TABLE `institute_sections`
  ADD CONSTRAINT `fk_institute_sections_admin` FOREIGN KEY (`created_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `organization_core_values`
--
ALTER TABLE `organization_core_values`
  ADD CONSTRAINT `fk_org_core_values_org` FOREIGN KEY (`organization_id`) REFERENCES `student_organizations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `site_settings`
--
ALTER TABLE `site_settings`
  ADD CONSTRAINT `fk_settings_admin` FOREIGN KEY (`updated_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `student_organizations`
--
ALTER TABLE `student_organizations`
  ADD CONSTRAINT `fk_organizations_admin` FOREIGN KEY (`created_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
