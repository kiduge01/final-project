-- Migration: Full procurement schema (department_budgets, purchase_request_items, approval_logs, budget_expenses)
-- Alters purchase_requests to add missing columns
-- Date: 2026-04-14

SET NAMES utf8mb4;
SET foreign_key_checks = 0;

-- ─── 1. department_budgets ───
CREATE TABLE IF NOT EXISTS department_budgets (
  id             BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  department     VARCHAR(120) NOT NULL,
  category_id    BIGINT UNSIGNED NULL,
  fiscal_month   VARCHAR(7)   NOT NULL COMMENT 'YYYY-MM',
  planned_amount DECIMAL(14,2) NOT NULL DEFAULT 0.00,
  actual_amount  DECIMAL(14,2) NOT NULL DEFAULT 0.00,
  reserved_amount DECIMAL(14,2) NOT NULL DEFAULT 0.00,
  status         ENUM('draft','submitted','approved','rejected','expenses_added','closed') NOT NULL DEFAULT 'draft',
  submitted_by   BIGINT UNSIGNED NULL,
  approved_by    BIGINT UNSIGNED NULL,
  approved_at    DATETIME NULL,
  event_id       BIGINT UNSIGNED NULL,
  description    TEXT NULL,
  notes          TEXT NULL,
  created_at     TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at     TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_db_dept (department),
  INDEX idx_db_month (fiscal_month),
  INDEX idx_db_status (status),
  INDEX idx_db_event (event_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── 2. budget_expenses ───
CREATE TABLE IF NOT EXISTS budget_expenses (
  id           BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  budget_id    BIGINT UNSIGNED NOT NULL,
  item_name    VARCHAR(220) NOT NULL,
  amount       DECIMAL(14,2) NOT NULL DEFAULT 0.00,
  expense_date DATE NOT NULL,
  notes        TEXT NULL,
  source_type  VARCHAR(40) NULL COMMENT 'manual, procurement',
  source_id    BIGINT UNSIGNED NULL,
  recorded_by  BIGINT UNSIGNED NULL,
  created_at   TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at   TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_be_budget (budget_id),
  INDEX idx_be_date (expense_date),
  CONSTRAINT fk_be_budget FOREIGN KEY (budget_id) REFERENCES department_budgets(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── 3. purchase_request_items ───
CREATE TABLE IF NOT EXISTS purchase_request_items (
  id                    BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  purchase_request_id   BIGINT UNSIGNED NOT NULL,
  item_name             VARCHAR(220) NOT NULL,
  quantity              DECIMAL(12,2) NOT NULL DEFAULT 1.00,
  estimated_unit_cost   DECIMAL(14,2) NOT NULL DEFAULT 0.00,
  line_total            DECIMAL(14,2) GENERATED ALWAYS AS (quantity * estimated_unit_cost) STORED,
  notes                 VARCHAR(255) NULL,
  created_at            TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_pri_pr (purchase_request_id),
  CONSTRAINT fk_pri_pr FOREIGN KEY (purchase_request_id) REFERENCES purchase_requests(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── 4. approval_logs ───
CREATE TABLE IF NOT EXISTS approval_logs (
  id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  entity_type VARCHAR(60)  NOT NULL COMMENT 'e.g. procurement, budget',
  entity_id   BIGINT UNSIGNED NOT NULL,
  level_no    TINYINT UNSIGNED NOT NULL DEFAULT 1,
  action      VARCHAR(40)  NOT NULL COMMENT 'submitted, approved, rejected',
  actor_id    BIGINT UNSIGNED NULL,
  notes       VARCHAR(500) NULL,
  acted_at    TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_al_entity (entity_type, entity_id),
  INDEX idx_al_actor (actor_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── 5. Alter purchase_requests — add missing columns ───
ALTER TABLE purchase_requests
  ADD COLUMN budget_id         BIGINT UNSIGNED NULL AFTER event_id,
  ADD COLUMN vendor_name       VARCHAR(180) NULL AFTER budget_id,
  ADD COLUMN approved_by       BIGINT UNSIGNED NULL AFTER vendor_name,
  ADD COLUMN approved_at       DATETIME NULL AFTER approved_by,
  ADD COLUMN rejection_reason  TEXT NULL AFTER approved_at,
  ADD COLUMN completed_at      DATETIME NULL AFTER rejection_reason;

-- Update status enum to include: purchased, completed, cancelled
ALTER TABLE purchase_requests
  MODIFY COLUMN status ENUM(
    'draft','submitted','approved','rejected',
    'purchased','completed','cancelled',
    'ordered','closed'
  ) NOT NULL DEFAULT 'draft';

SET foreign_key_checks = 1;
