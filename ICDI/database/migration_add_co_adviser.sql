-- Migration: Add co_adviser option to batches and batch_members tables
-- Date: 2026-01-30
-- Description: Adds co_adviser as an option for target_group in batches and group_type in batch_members

-- Add co_adviser to batches.target_group enum
ALTER TABLE `batches` 
MODIFY COLUMN `target_group` enum('all','adviser','co_adviser','executive_officer','executive_associate') DEFAULT 'all';

-- Add co_adviser to batch_members.group_type enum
ALTER TABLE `batch_members` 
MODIFY COLUMN `group_type` enum('adviser','co_adviser','executive_officer','executive_associate') NOT NULL;
