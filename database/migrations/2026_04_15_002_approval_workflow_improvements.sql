-- Approval workflow improvements
-- Adds rejection tracking columns to finance_entries
-- Creates approval_workflows configuration table

-- 1. Add rejection_count column
SET @col1 = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'finance_entries' AND COLUMN_NAME = 'rejection_count');
SET @sql1 = IF(@col1 = 0, 'ALTER TABLE finance_entries ADD COLUMN rejection_count TINYINT UNSIGNED NOT NULL DEFAULT 0 AFTER approval_status', 'SELECT 1');
PREPARE s1 FROM @sql1; EXECUTE s1; DEALLOCATE PREPARE s1;

-- 2. Add rejection_reason column
SET @col2 = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'finance_entries' AND COLUMN_NAME = 'rejection_reason');
SET @sql2 = IF(@col2 = 0, 'ALTER TABLE finance_entries ADD COLUMN rejection_reason VARCHAR(500) NULL AFTER rejection_count', 'SELECT 1');
PREPARE s2 FROM @sql2; EXECUTE s2; DEALLOCATE PREPARE s2;

-- 3. Change default of approval_status from 'approved' to 'pending'
ALTER TABLE finance_entries MODIFY COLUMN approval_status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending';

-- 4. Create approval_workflows table
CREATE TABLE IF NOT EXISTS approval_workflows (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    workflow_type VARCHAR(50) NOT NULL,
    level_no TINYINT UNSIGNED NOT NULL DEFAULT 1,
    role_id BIGINT UNSIGNED NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_workflow_type_level (workflow_type, level_no),
    CONSTRAINT fk_aw_role FOREIGN KEY (role_id) REFERENCES roles(id) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Seed default workflows (Finance Officer = role 2, Approver = role 7)
INSERT IGNORE INTO approval_workflows (workflow_type, level_no, role_id) VALUES
('finance_entry', 1, 2),
('budget', 1, 2),
('budget', 2, 7),
('procurement', 1, 7);
