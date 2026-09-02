-- Migration: Create department_leaders and department_reports tables
-- Also adds UNIQUE constraint to prevent duplicate leader assignments
-- Date: 2026-04-14

SET NAMES utf8mb4;

-- ─── 1. department_leaders ───
CREATE TABLE IF NOT EXISTS department_leaders (
  id            BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  department_id BIGINT UNSIGNED NOT NULL,
  member_id     BIGINT UNSIGNED NULL COMMENT 'FK to members table',
  leader_type   VARCHAR(50) NOT NULL,
  leader_name   VARCHAR(100) NOT NULL,
  email         VARCHAR(100) NULL,
  phone         VARCHAR(20) NULL,
  bio           TEXT NULL,
  is_active     TINYINT(1) NOT NULL DEFAULT 1,
  created_at    TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at    TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  -- Prevent same member holding the same position twice in a department
  UNIQUE KEY uq_dept_leader_member_type (department_id, member_id, leader_type),
  CONSTRAINT fk_dept_leaders_dept   FOREIGN KEY (department_id) REFERENCES departments(id) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_dept_leaders_member FOREIGN KEY (member_id)     REFERENCES members(id)     ON UPDATE CASCADE ON DELETE SET NULL,
  INDEX idx_dept_leaders_department (department_id),
  INDEX idx_dept_leaders_active     (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── 2. department_reports ───
CREATE TABLE IF NOT EXISTS department_reports (
  id            BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  department_id BIGINT UNSIGNED NOT NULL,
  title         VARCHAR(255) NOT NULL,
  description   LONGTEXT NULL,
  report_date   DATE NOT NULL,
  category      VARCHAR(50) NOT NULL COMMENT 'e.g., Weekly, Monthly, Activity, Finance, Quarterly, Annual, Event, Other',
  status        ENUM('draft','submitted','approved','rejected') NOT NULL DEFAULT 'draft',
  submitted_at  DATETIME NULL,
  reviewed_by   BIGINT UNSIGNED NULL,
  reviewed_at   DATETIME NULL,
  review_notes  TEXT NULL,
  created_at    TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at    TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_dept_reports_dept        FOREIGN KEY (department_id) REFERENCES departments(id) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_dept_reports_reviewed_by FOREIGN KEY (reviewed_by)   REFERENCES users(id)       ON UPDATE CASCADE ON DELETE SET NULL,
  INDEX idx_dept_reports_department (department_id),
  INDEX idx_dept_reports_status     (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
