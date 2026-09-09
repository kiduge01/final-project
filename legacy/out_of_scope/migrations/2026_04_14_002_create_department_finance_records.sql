-- Migration: Create department_finance_records table
-- Purpose: Simple income/expense ledger for individual departments
--          (separate from the main church finance_entries which requires
--           complex FK chains: category_id, recorded_by user FK, etc.)
-- Date: 2026-04-14

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS department_finance_records (
  id            BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  department_id BIGINT UNSIGNED NOT NULL,
  type          ENUM('income','expense') NOT NULL,
  category      VARCHAR(100) NOT NULL,
  amount        DECIMAL(14,2) NOT NULL,
  description   TEXT NULL,
  entry_date    DATE NOT NULL,
  recorded_by   VARCHAR(100) NULL COMMENT 'Name of department head at time of entry',
  deleted_at    DATETIME NULL,
  created_at    TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at    TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_dept_fin_rec_dept FOREIGN KEY (department_id) REFERENCES departments(id)
    ON UPDATE CASCADE ON DELETE CASCADE,
  INDEX idx_dept_fin_rec_dept (department_id),
  INDEX idx_dept_fin_rec_type (type),
  INDEX idx_dept_fin_rec_date (entry_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
