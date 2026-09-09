-- Migration: Departments table + Budget workflow columns
-- Date: 2026-04-01

SET NAMES utf8mb4;

-- ─── 1. Departments table ───
CREATE TABLE IF NOT EXISTS departments (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL UNIQUE,
  description VARCHAR(255) NULL,
  head_user_id BIGINT UNSIGNED NULL COMMENT 'Optional department head (user)',
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_departments_head FOREIGN KEY (head_user_id) REFERENCES users(id)
    ON UPDATE CASCADE ON DELETE SET NULL,
  INDEX idx_departments_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── 2. Seed default departments ───
INSERT IGNORE INTO departments (name, description) VALUES
('Ibada',        'Idara ya ibada na muziki'),
('Vijana',       'Idara ya vijana'),
('Ujenzi',       'Mradi wa ujenzi wa kanisa'),
('Huduma',       'Huduma za jamii na misaada'),
('Elimu',        'Elimu ya Biblia na mafunzo'),
('Utawala',      'Utawala na usimamizi wa kanisa');

-- ─── 3. Budget workflow columns on department_budgets ───
-- Add 'actual_amount' (kiasi kilichotumika kweli kweli)
-- Add 'status' values: draft | submitted | approved | expenses_added | closed
-- Add 'closed_at', 'closed_by', 'finance_entry_id' (link to the created expense)
-- Note: ALTER IGNORE is used so re-running is safe on already-migrated DBs

ALTER TABLE department_budgets
  MODIFY COLUMN status ENUM('draft','submitted','approved','rejected','expenses_added','closed') NOT NULL DEFAULT 'draft';

ALTER TABLE department_budgets
  ADD COLUMN IF NOT EXISTS actual_amount DECIMAL(14,2) NOT NULL DEFAULT 0 AFTER spent_amount,
  ADD COLUMN IF NOT EXISTS actual_notes VARCHAR(255) NULL AFTER actual_amount,
  ADD COLUMN IF NOT EXISTS closed_at DATETIME NULL AFTER actual_notes,
  ADD COLUMN IF NOT EXISTS closed_by BIGINT UNSIGNED NULL AFTER closed_at,
  ADD COLUMN IF NOT EXISTS finance_entry_id BIGINT UNSIGNED NULL COMMENT 'Finance expense entry created on close' AFTER closed_by;

-- Foreign keys (safe with IF NOT EXISTS workaround via checking)
-- Use ALTER TABLE to add constraints
ALTER TABLE department_budgets
  ADD CONSTRAINT fk_dept_budget_closed_by FOREIGN KEY (closed_by) REFERENCES users(id)
    ON UPDATE CASCADE ON DELETE SET NULL;

ALTER TABLE department_budgets
  ADD CONSTRAINT fk_dept_budget_finance_entry FOREIGN KEY (finance_entry_id) REFERENCES finance_entries(id)
    ON UPDATE CASCADE ON DELETE SET NULL;
