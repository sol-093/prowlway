-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 29, 2026 at 03:11 PM
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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `email`, `password`, `name`, `role`, `created_at`, `updated_at`) VALUES
(1, 'admin@icdisg.ph', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin User', 'super_admin', '2026-01-29 12:02:14', '2026-01-29 12:02:14');

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
  `status` enum('draft','published','archived') DEFAULT 'published',
  `created_by` int(11) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `announcements`
--

INSERT INTO `announcements` (`id`, `title`, `description`, `content`, `category`, `image`, `pinned`, `status`, `created_by`, `created_at`, `updated_at`) VALUES
(1, '123', '123', '123', 'academic', 'images/img_697b5a022cfad0.14140578_1769691650.png', 0, 'published', 1, '2026-01-29 13:00:50', '2026-01-29 13:00:50');

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
  `target_group` enum('all','adviser','executive_officer','executive_associate') DEFAULT 'all' COMMENT 'Which group this batch page focuses on',
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
(1, NULL, '2', '2026', '2027', 'images/img_697b5b3bbe5e17.79450240_1769691963.png', '123', 'all', 'active', 0, 1, '2026-01-29 13:06:03', '2026-01-29 13:06:03');

-- --------------------------------------------------------

--
-- Table structure for table `batch_members`
--

CREATE TABLE `batch_members` (
  `id` int(11) UNSIGNED NOT NULL,
  `batch_id` int(11) UNSIGNED NOT NULL,
  `group_type` enum('adviser','executive_officer','executive_associate') NOT NULL,
  `name` varchar(255) NOT NULL,
  `position_title` varchar(255) NOT NULL,
  `image` varchar(500) DEFAULT NULL COMMENT 'Relative path to uploaded profile image',
  `display_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `documents`
--

CREATE TABLE `documents` (
  `id` int(11) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `category` enum('01','02','03','04','05') NOT NULL COMMENT '01=OFFICES REPORT, 02=EXECUTIVE ORDER, 03=ORDINANCE, 04=RESOLUTION, 05=OTHER',
  `file_path` varchar(500) DEFAULT NULL COMMENT 'Relative path to uploaded PDF file',
  `file_size` bigint(20) DEFAULT NULL COMMENT 'File size in bytes',
  `file_type` varchar(50) DEFAULT NULL,
  `status` enum('draft','published','archived') DEFAULT 'published',
  `created_by` int(11) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `documents`
--

INSERT INTO `documents` (`id`, `title`, `description`, `category`, `file_path`, `file_size`, `file_type`, `status`, `created_by`, `created_at`, `updated_at`) VALUES
(1, '123123123', '345', '01', 'documents/doc_697b5cf47aca74.10396727_1769692404.pdf', 809982, 'application/pdf', 'published', 1, '2026-01-29 13:13:24', '2026-01-29 13:47:17');

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
  `image` varchar(500) NOT NULL COMMENT 'Relative path to uploaded main image',
  `gallery` text DEFAULT NULL COMMENT 'JSON array of relative paths to uploaded gallery images',
  `date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `display_order` int(11) DEFAULT 0,
  `status` enum('draft','published','archived') DEFAULT 'published',
  `created_by` int(11) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`id`, `title`, `caption`, `description`, `summary`, `category`, `image`, `gallery`, `date`, `end_date`, `location`, `display_order`, `status`, `created_by`, `created_at`, `updated_at`) VALUES
(1, '123', '123', '123', '123', 'seminar', 'images/img_697b64bd06d429.91982403_1769694397.png', '[\"images\\/img_697b64bd07e6d4.95441931_1769694397.png\"]', '2026-01-29', NULL, '123', 0, 'published', 1, '2026-01-29 13:46:37', '2026-01-29 13:46:37');

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
(4, 'logo', 'Logo', 'Lorem Ipsum Dolor Sit Amet, Consectetur Adipiscing Elit, Sed Do Eiusmod Tempor Incididunt Ut Labore Et Dolore Magna Aliqua.', NULL, 4, 'published', NULL, '2026-01-29 12:02:14', '2026-01-29 12:02:14');

-- --------------------------------------------------------

--
-- Table structure for table `institute_sections`
--

CREATE TABLE `institute_sections` (
  `id` int(11) UNSIGNED NOT NULL,
  `type` enum('faculty_unit','admin_representative','program') NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `content` text DEFAULT NULL,
  `image` varchar(500) DEFAULT NULL COMMENT 'Relative path to uploaded image file',
  `display_order` int(11) DEFAULT 0,
  `status` enum('draft','published') DEFAULT 'published',
  `created_by` int(11) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
  `content` text DEFAULT NULL COMMENT 'Core values (one per line)',
  `logo` varchar(500) DEFAULT NULL COMMENT 'Relative path to uploaded logo image',
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

INSERT INTO `student_organizations` (`id`, `name`, `acronym`, `description`, `content`, `logo`, `website`, `social_media`, `display_order`, `status`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'ICDI Student Government', 'ICDISG', 'The official student government of the Institute of Computing and Digital Innovation', NULL, NULL, NULL, NULL, 1, 'active', NULL, '2026-01-29 12:02:15', '2026-01-29 12:02:15'),
(2, 'CS Society', 'CSS', 'Computer Science Society', NULL, NULL, NULL, NULL, 2, 'active', NULL, '2026-01-29 12:02:15', '2026-01-29 12:02:15'),
(3, 'IS Society', 'ISS', 'Information Systems Society', NULL, NULL, NULL, NULL, 3, 'active', NULL, '2026-01-29 12:02:15', '2026-01-29 12:02:15'),
(4, 'GITCUB', 'GITCUB', 'Google IT Community University Branch', NULL, NULL, NULL, NULL, 4, 'active', NULL, '2026-01-29 12:02:15', '2026-01-29 12:02:15'),
(5, 'JPCS', 'JPCS', 'Junior Philippine Computer Society', NULL, NULL, NULL, NULL, 5, 'active', NULL, '2026-01-29 12:02:15', '2026-01-29 12:02:15');

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
  ADD KEY `idx_announcements_published_pinned` (`status`,`pinned`,`created_at`);

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
-- Indexes for table `documents`
--
ALTER TABLE `documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_category` (`category`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `fk_documents_admin` (`created_by`),
  ADD KEY `idx_documents_published_category` (`status`,`category`,`created_at`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_category` (`category`),
  ADD KEY `idx_date` (`date`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_display_order` (`display_order`),
  ADD KEY `fk_events_admin` (`created_by`),
  ADD KEY `idx_events_published_date` (`status`,`date`,`display_order`);

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
  ADD KEY `fk_institute_sections_admin` (`created_by`);

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
  ADD KEY `fk_organizations_admin` (`created_by`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `batches`
--
ALTER TABLE `batches`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `batch_members`
--
ALTER TABLE `batch_members`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `documents`
--
ALTER TABLE `documents`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `institute_info`
--
ALTER TABLE `institute_info`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `institute_sections`
--
ALTER TABLE `institute_sections`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

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
  ADD CONSTRAINT `fk_announcements_admin` FOREIGN KEY (`created_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `batches`
--
ALTER TABLE `batches`
  ADD CONSTRAINT `fk_batches_admin` FOREIGN KEY (`created_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_batches_org` FOREIGN KEY (`organization_id`) REFERENCES `student_organizations` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `batch_members`
--
ALTER TABLE `batch_members`
  ADD CONSTRAINT `fk_batch_members_batch` FOREIGN KEY (`batch_id`) REFERENCES `batches` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `documents`
--
ALTER TABLE `documents`
  ADD CONSTRAINT `fk_documents_admin` FOREIGN KEY (`created_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `events`
--
ALTER TABLE `events`
  ADD CONSTRAINT `fk_events_admin` FOREIGN KEY (`created_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `institute_info`
--
ALTER TABLE `institute_info`
  ADD CONSTRAINT `fk_institute_admin` FOREIGN KEY (`updated_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `institute_sections`
--
ALTER TABLE `institute_sections`
  ADD CONSTRAINT `fk_institute_sections_admin` FOREIGN KEY (`created_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `site_settings`
--
ALTER TABLE `site_settings`
  ADD CONSTRAINT `fk_settings_admin` FOREIGN KEY (`updated_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `student_organizations`
--
ALTER TABLE `student_organizations`
  ADD CONSTRAINT `fk_organizations_admin` FOREIGN KEY (`created_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
