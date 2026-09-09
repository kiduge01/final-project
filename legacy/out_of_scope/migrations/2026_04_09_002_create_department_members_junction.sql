-- Migration: Create department_members junction table
-- Date: 2026-04-09
-- Purpose: Enable many-to-many relationship between members and departments
-- A member can belong to multiple departments; each department has multiple members

SET NAMES utf8mb4;

-- ─── 1. Create department_members junction table ───
CREATE TABLE IF NOT EXISTS department_members (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  department_id BIGINT UNSIGNED NOT NULL,
  member_id BIGINT UNSIGNED NOT NULL,
  assigned_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  notes VARCHAR(255) NULL COMMENT 'Role or additional info (e.g., Worship Leader, Youth Coordinator)',
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  -- Unique constraint: a member can only be assigned once per department
  UNIQUE KEY uq_dept_member (department_id, member_id),
  
  -- Foreign keys
  CONSTRAINT fk_dept_members_dept FOREIGN KEY (department_id) REFERENCES departments(id)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_dept_members_member FOREIGN KEY (member_id) REFERENCES members(id)
    ON UPDATE CASCADE ON DELETE CASCADE,
  
  -- Indexes for performance
  INDEX idx_dept_members_department (department_id),
  INDEX idx_dept_members_member (member_id),
  INDEX idx_dept_members_assigned_date (assigned_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── 2. Add department_id to finance_entries if not exists ───
-- This allows finance records to belong to a specific department
ALTER TABLE finance_entries
  ADD COLUMN IF NOT EXISTS department_id BIGINT UNSIGNED NULL AFTER id,
  ADD CONSTRAINT fk_finance_entries_dept FOREIGN KEY (department_id) REFERENCES departments(id)
    ON UPDATE CASCADE ON DELETE SET NULL,
  ADD INDEX IF NOT EXISTS idx_finance_entries_dept (department_id);

-- ─── 3. Create department_leaders table for leadership roles ───
CREATE TABLE IF NOT EXISTS department_leaders (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  department_id BIGINT UNSIGNED NOT NULL,
  member_id BIGINT UNSIGNED NULL COMMENT 'FK to members table if leader is a member',
  leader_type VARCHAR(50) NOT NULL COMMENT 'e.g., Chairman, Treasurer, Secretary, Coordinator',
  leader_name VARCHAR(100) NOT NULL COMMENT 'Full name of leader (denormalized for easy display)',
  email VARCHAR(100) NULL,
  phone VARCHAR(20) NULL,
  bio TEXT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  -- Foreign keys
  CONSTRAINT fk_dept_leaders_dept FOREIGN KEY (department_id) REFERENCES departments(id)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_dept_leaders_member FOREIGN KEY (member_id) REFERENCES members(id)
    ON UPDATE CASCADE ON DELETE SET NULL,
  
  -- Indexes for performance
  INDEX idx_dept_leaders_department (department_id),
  INDEX idx_dept_leaders_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── 4. Create department_reports table ───
CREATE TABLE IF NOT EXISTS department_reports (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  department_id BIGINT UNSIGNED NOT NULL,
  title VARCHAR(255) NOT NULL,
  description LONGTEXT NOT NULL,
  report_date DATE NOT NULL,
  category VARCHAR(50) NOT NULL COMMENT 'e.g., Weekly, Monthly, Activity, Finance, Quarterly, Annual, Event, Other',
  status ENUM('draft','submitted','approved','rejected') NOT NULL DEFAULT 'draft',
  submitted_at DATETIME NULL,
  reviewed_by BIGINT UNSIGNED NULL COMMENT 'Admin user who reviewed',
  reviewed_at DATETIME NULL,
  review_notes TEXT NULL,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  -- Foreign keys
  CONSTRAINT fk_dept_reports_dept FOREIGN KEY (department_id) REFERENCES departments(id)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_dept_reports_reviewed_by FOREIGN KEY (reviewed_by) REFERENCES users(id)
    ON UPDATE CASCADE ON DELETE SET NULL,
  
  -- Indexes for performance
  INDEX idx_dept_reports_department (department_id),
  INDEX idx_dept_reports_status (status),
  INDEX idx_dept_reports_report_date (report_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
