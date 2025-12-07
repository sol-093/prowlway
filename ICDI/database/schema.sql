-- PROWLWAY ICDISG Archive Website Database Schema
-- Database: icdi_db

CREATE DATABASE IF NOT EXISTS `icdi_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `icdi_db`;

-- ============================================
-- USERS & AUTHENTICATION
-- ============================================

CREATE TABLE IF NOT EXISTS `admins` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `email` VARCHAR(255) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `name` VARCHAR(255) DEFAULT NULL,
  `role` ENUM('super_admin', 'admin', 'editor') DEFAULT 'admin',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Default admin account (password: admin123)
INSERT INTO `admins` (`email`, `password`, `name`, `role`) VALUES
('admin@icdisg.ph', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin User', 'super_admin');

-- ============================================
-- ANNOUNCEMENTS
-- ============================================

CREATE TABLE IF NOT EXISTS `announcements` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL,
  `description` TEXT,
  `content` TEXT,
  `category` ENUM('general', 'academic', 'event', 'maintenance', 'urgent') DEFAULT 'general',
  `image` VARCHAR(500) DEFAULT NULL COMMENT 'Relative path to uploaded image file',
  `pinned` TINYINT(1) DEFAULT 0,
  `status` ENUM('draft', 'published', 'archived') DEFAULT 'published',
  `created_by` INT(11) UNSIGNED DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_category` (`category`),
  KEY `idx_pinned` (`pinned`),
  KEY `idx_status` (`status`),
  KEY `fk_announcements_admin` (`created_by`),
  CONSTRAINT `fk_announcements_admin` FOREIGN KEY (`created_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- EVENTS
-- ============================================

CREATE TABLE IF NOT EXISTS `events` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL,
  `caption` VARCHAR(500) DEFAULT NULL,
  `description` TEXT,
  `summary` TEXT,
  `category` ENUM('workshop', 'seminar', 'service', 'celebration', 'other') DEFAULT 'other',
  `image` VARCHAR(500) NOT NULL COMMENT 'Relative path to uploaded main image',
  `gallery` TEXT DEFAULT NULL COMMENT 'JSON array of relative paths to uploaded gallery images',
  `date` DATE NOT NULL,
  `end_date` DATE DEFAULT NULL,
  `location` VARCHAR(255) DEFAULT NULL,
  `display_order` INT(11) DEFAULT 0,
  `status` ENUM('draft', 'published', 'archived') DEFAULT 'published',
  `created_by` INT(11) UNSIGNED DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_category` (`category`),
  KEY `idx_date` (`date`),
  KEY `idx_status` (`status`),
  KEY `idx_display_order` (`display_order`),
  KEY `fk_events_admin` (`created_by`),
  CONSTRAINT `fk_events_admin` FOREIGN KEY (`created_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- DOCUMENTS
-- ============================================

CREATE TABLE IF NOT EXISTS `documents` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL,
  `description` TEXT,
  `category` ENUM('01', '02', '03', '04', '05') NOT NULL COMMENT '01=OFFICES REPORT, 02=EXECUTIVE ORD, 03=ORDINANCE, 04=RESOLUTION, 05=OTHER',
  `file_path` VARCHAR(500) DEFAULT NULL COMMENT 'Relative path to uploaded PDF file',
  `file_size` BIGINT(20) DEFAULT NULL COMMENT 'File size in bytes',
  `file_type` VARCHAR(50) DEFAULT NULL,
  `status` ENUM('draft', 'published', 'archived') DEFAULT 'published',
  `created_by` INT(11) UNSIGNED DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_category` (`category`),
  KEY `idx_status` (`status`),
  KEY `fk_documents_admin` (`created_by`),
  CONSTRAINT `fk_documents_admin` FOREIGN KEY (`created_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- BATCHES (Academic Years)
-- ============================================

CREATE TABLE IF NOT EXISTS `batches` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `academic_year` VARCHAR(50) NOT NULL COMMENT 'e.g., A.Y. 2025-2026',
  `start_year` YEAR(4) NOT NULL,
  `end_year` YEAR(4) NOT NULL,
  `image` VARCHAR(500) DEFAULT NULL COMMENT 'Relative path to uploaded image file',
  `description` TEXT,
  `status` ENUM('draft', 'active', 'archived') DEFAULT 'active',
  `display_order` INT(11) DEFAULT 0,
  `created_by` INT(11) UNSIGNED DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_academic_year` (`academic_year`),
  KEY `idx_status` (`status`),
  KEY `idx_display_order` (`display_order`),
  KEY `fk_batches_admin` (`created_by`),
  CONSTRAINT `fk_batches_admin` FOREIGN KEY (`created_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- INSTITUTE INFORMATION
-- ============================================

CREATE TABLE IF NOT EXISTS `institute_info` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `section` VARCHAR(100) NOT NULL COMMENT 'about, mission, vision, logo',
  `title` VARCHAR(255) DEFAULT NULL,
  `content` TEXT,
  `image` VARCHAR(500) DEFAULT NULL COMMENT 'Relative path to uploaded image file',
  `display_order` INT(11) DEFAULT 0,
  `status` ENUM('draft', 'published') DEFAULT 'published',
  `updated_by` INT(11) UNSIGNED DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_section` (`section`),
  KEY `idx_status` (`status`),
  KEY `fk_institute_admin` (`updated_by`),
  CONSTRAINT `fk_institute_admin` FOREIGN KEY (`updated_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default institute sections
INSERT INTO `institute_info` (`section`, `title`, `content`, `display_order`) VALUES
('about', 'About', 'The Institute Of Computing And Digital Innovation (ICDI) was established in 2020, with roots in Kolehiyo Ng Lungsod Ng Dasmariñas (KLD). It has evolved through various iterations including the Institute Of Information And Computing Sciences (IICS) and the Institute Of Mathematical Application And Computing Sciences (IMACS), culminating in its current role as a Dynamic Academic Hub.', 1),
('mission', 'Mission', 'Empower Student Leadership Through Forward-Thinking Initiatives That Inspire Creativity And Progress. Solidify The Institute\'s Reputation As A Leader In Both Academic And Extracurricular Excellence.', 2),
('vision', 'Vision', 'To Effectively Bridge The Nodes Of Communication And Collaboration Among The Student Body, Organizations, Committees, And Administration, Both Within And Outside The Institution By Leveraging The Power Of Knowledge, Leadership, And Dedication.', 3),
('logo', 'Logo', 'Lorem Ipsum Dolor Sit Amet, Consectetur Adipiscing Elit, Sed Do Eiusmod Tempor Incididunt Ut Labore Et Dolore Magna Aliqua.', 4);

-- ============================================
-- INSTITUTE SECTIONS (Faculty, Admin, Programs)
-- ============================================

CREATE TABLE IF NOT EXISTS `institute_sections` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `type` ENUM('faculty_unit', 'admin_representative', 'program') NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `description` TEXT,
  `content` TEXT,
  `image` VARCHAR(500) DEFAULT NULL COMMENT 'Relative path to uploaded image file',
  `display_order` INT(11) DEFAULT 0,
  `status` ENUM('draft', 'published') DEFAULT 'published',
  `created_by` INT(11) UNSIGNED DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_type` (`type`),
  KEY `idx_status` (`status`),
  KEY `fk_institute_sections_admin` (`created_by`),
  CONSTRAINT `fk_institute_sections_admin` FOREIGN KEY (`created_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- STUDENT ORGANIZATIONS
-- ============================================

CREATE TABLE IF NOT EXISTS `student_organizations` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `acronym` VARCHAR(50) DEFAULT NULL,
  `description` TEXT,
  `logo` VARCHAR(500) DEFAULT NULL COMMENT 'Relative path to uploaded logo image',
  `website` VARCHAR(500) DEFAULT NULL,
  `social_media` TEXT DEFAULT NULL COMMENT 'JSON object with social media links',
  `display_order` INT(11) DEFAULT 0,
  `status` ENUM('draft', 'active', 'archived') DEFAULT 'active',
  `created_by` INT(11) UNSIGNED DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_status` (`status`),
  KEY `idx_display_order` (`display_order`),
  KEY `fk_organizations_admin` (`created_by`),
  CONSTRAINT `fk_organizations_admin` FOREIGN KEY (`created_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default student organizations
INSERT INTO `student_organizations` (`name`, `acronym`, `description`, `display_order`) VALUES
('ICDI Student Government', 'ICDISG', 'The official student government of the Institute of Computing and Digital Innovation', 1),
('CS Society', 'CSS', 'Computer Science Society', 2),
('IS Society', 'ISS', 'Information Systems Society', 3),
('GITCUB', 'GITCUB', 'Google IT Community University Branch', 4),
('JPCS', 'JPCS', 'Junior Philippine Computer Society', 5);

-- ============================================
-- SITE SETTINGS
-- ============================================

CREATE TABLE IF NOT EXISTS `site_settings` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `setting_key` VARCHAR(100) NOT NULL UNIQUE,
  `setting_value` TEXT,
  `setting_type` ENUM('text', 'number', 'boolean', 'json') DEFAULT 'text',
  `description` VARCHAR(500) DEFAULT NULL,
  `updated_by` INT(11) UNSIGNED DEFAULT NULL,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_settings_admin` (`updated_by`),
  CONSTRAINT `fk_settings_admin` FOREIGN KEY (`updated_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default settings
INSERT INTO `site_settings` (`setting_key`, `setting_value`, `setting_type`, `description`) VALUES
('site_name', 'PROWLWAY', 'text', 'Website name'),
('site_description', 'ICDISG Archive Website', 'text', 'Website description'),
('contact_email', 'imacsac@kidduph', 'text', 'Contact email address'),
('maintenance_mode', '0', 'boolean', 'Enable maintenance mode');

-- ============================================
-- INDEXES FOR PERFORMANCE
-- ============================================

-- Additional composite indexes for common queries
CREATE INDEX `idx_events_published_date` ON `events` (`status`, `date`, `display_order`);
CREATE INDEX `idx_announcements_published_pinned` ON `announcements` (`status`, `pinned`, `created_at`);
CREATE INDEX `idx_documents_published_category` ON `documents` (`status`, `category`, `created_at`);

