-- ============================================================
-- Migration: Department Contributions & Asset Assignments
-- Date: 2026-04-14
-- ============================================================

-- 1. Department Contributions (michango)
CREATE TABLE IF NOT EXISTS department_contributions (
  id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  department_id       BIGINT UNSIGNED NOT NULL,
  member_id           BIGINT UNSIGNED NULL,                          -- NULL = non-member contributor
  contributor_name    VARCHAR(150) NULL,                             -- fallback if no member_id
  amount              DECIMAL(14,2) NOT NULL,
  payment_method      ENUM('cash','mpesa','cheque','bank_transfer','other') NOT NULL DEFAULT 'cash',
  purpose             VARCHAR(255) NULL,
  contribution_date   DATE NOT NULL,
  recorded_by         VARCHAR(150) NULL,                             -- head name who recorded
  created_at          TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_dept_contrib_dept   FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE CASCADE  ON UPDATE CASCADE,
  CONSTRAINT fk_dept_contrib_member FOREIGN KEY (member_id)     REFERENCES members(id)     ON DELETE SET NULL ON UPDATE CASCADE,
  INDEX idx_dept_contrib_dept  (department_id),
  INDEX idx_dept_contrib_date  (contribution_date),
  INDEX idx_dept_contrib_member(member_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Extend asset_assignments to support department assignment
ALTER TABLE asset_assignments
  MODIFY COLUMN assigned_type ENUM('user','event','location','department') NOT NULL;

ALTER TABLE asset_assignments
  ADD COLUMN IF NOT EXISTS assigned_department_id BIGINT UNSIGNED NULL
    AFTER assigned_location;

ALTER TABLE asset_assignments
  ADD CONSTRAINT fk_asset_assign_dept
    FOREIGN KEY (assigned_department_id) REFERENCES departments(id)
    ON UPDATE CASCADE ON DELETE SET NULL;

ALTER TABLE asset_assignments
  ADD INDEX IF NOT EXISTS idx_asset_assign_dept (assigned_department_id);
