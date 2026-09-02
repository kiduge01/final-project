-- Migration: Add Budget Approval Tracking
-- Adds status tracking and approval metadata to budget workflow

-- Add budget approval status and metadata to event_budget_items
ALTER TABLE event_budget_items 
ADD COLUMN `budget_status` ENUM('draft', 'pending_approval', 'approved', 'rejected', 'in_progress', 'completed') NOT NULL DEFAULT 'draft' AFTER `item_type`,
ADD COLUMN `approved_by` BIGINT UNSIGNED NULL AFTER `notes`,
ADD COLUMN `approved_at` DATETIME NULL AFTER `approved_by`,
ADD COLUMN `rejection_reason` TEXT NULL AFTER `approved_at`,
ADD INDEX idx_event_budget_status (budget_status),
ADD INDEX idx_event_budget_approved_by (approved_by),
ADD CONSTRAINT fk_event_budget_approved_by FOREIGN KEY (approved_by) REFERENCES users(id) ON UPDATE CASCADE ON DELETE SET NULL;

-- Add budget tracking to events table
ALTER TABLE `events`
ADD COLUMN `budget_status` ENUM('draft', 'pending_approval', 'approved', 'rejected', 'in_progress', 'completed') NOT NULL DEFAULT 'draft' AFTER `budget_total`,
ADD COLUMN `budget_approved_by` BIGINT UNSIGNED NULL AFTER `budget_status`,
ADD COLUMN `budget_approved_at` DATETIME NULL AFTER `budget_approved_by`,
ADD COLUMN `budget_locked_at` DATETIME NULL AFTER `budget_approved_at`,
ADD INDEX idx_events_budget_status (budget_status),
ADD CONSTRAINT fk_events_budget_approved_by FOREIGN KEY (budget_approved_by) REFERENCES users(id) ON UPDATE CASCADE ON DELETE SET NULL;

-- Create event_finance_links table if not exists (for linking events to finance entries)
CREATE TABLE IF NOT EXISTS event_finance_links (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  event_id BIGINT UNSIGNED NOT NULL,
  finance_entry_id BIGINT UNSIGNED NOT NULL,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_event_finance_links_event FOREIGN KEY (event_id) REFERENCES `events`(id) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_event_finance_links_entry FOREIGN KEY (finance_entry_id) REFERENCES finance_entries(id) ON UPDATE CASCADE ON DELETE CASCADE,
  UNIQUE KEY uq_event_finance (event_id, finance_entry_id),
  INDEX idx_event_finance_links_event_id (event_id),
  INDEX idx_event_finance_links_entry_id (finance_entry_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
