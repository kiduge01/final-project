-- Finance Module Upgrade: Pledges, Department Budgets, Extra Categories
-- Run after schema.sql + sample_data.sql

SET NAMES utf8mb4;

-- ─── 1. Pledges (Ahadi) ───
CREATE TABLE IF NOT EXISTS pledges (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  pledge_no VARCHAR(60) NOT NULL UNIQUE,
  member_id BIGINT UNSIGNED NOT NULL,
  campaign VARCHAR(180) NULL,
  description VARCHAR(255) NULL,
  total_amount DECIMAL(14,2) NOT NULL,
  paid_amount DECIMAL(14,2) NOT NULL DEFAULT 0,
  pledge_date DATE NOT NULL,
  due_date DATE NULL,
  status ENUM('active', 'completed', 'cancelled', 'overdue') NOT NULL DEFAULT 'active',
  created_by BIGINT UNSIGNED NOT NULL,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_pledges_member FOREIGN KEY (member_id) REFERENCES members(id)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_pledges_created_by FOREIGN KEY (created_by) REFERENCES users(id)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  INDEX idx_pledges_member_id (member_id),
  INDEX idx_pledges_status (status),
  INDEX idx_pledges_date (pledge_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Pledge payments link to finance_entries
CREATE TABLE IF NOT EXISTS pledge_payments (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  pledge_id BIGINT UNSIGNED NOT NULL,
  finance_entry_id BIGINT UNSIGNED NOT NULL,
  amount DECIMAL(14,2) NOT NULL,
  payment_date DATE NOT NULL,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_pledge_payments_pledge FOREIGN KEY (pledge_id) REFERENCES pledges(id)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_pledge_payments_entry FOREIGN KEY (finance_entry_id) REFERENCES finance_entries(id)
    ON UPDATE CASCADE ON DELETE CASCADE,
  INDEX idx_pledge_payments_pledge_id (pledge_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── 2. Department Budgets ───
CREATE TABLE IF NOT EXISTS department_budgets (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  department VARCHAR(120) NOT NULL,
  category_id BIGINT UNSIGNED NULL,
  fiscal_month VARCHAR(7) NOT NULL COMMENT 'YYYY-MM',
  planned_amount DECIMAL(14,2) NOT NULL,
  spent_amount DECIMAL(14,2) NOT NULL DEFAULT 0,
  status ENUM('draft', 'submitted', 'approved', 'rejected') NOT NULL DEFAULT 'draft',
  submitted_by BIGINT UNSIGNED NOT NULL,
  approved_by BIGINT UNSIGNED NULL,
  approved_at DATETIME NULL,
  notes VARCHAR(255) NULL,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_dept_budget_category FOREIGN KEY (category_id) REFERENCES finance_categories(id)
    ON UPDATE CASCADE ON DELETE SET NULL,
  CONSTRAINT fk_dept_budget_submitted_by FOREIGN KEY (submitted_by) REFERENCES users(id)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_dept_budget_approved_by FOREIGN KEY (approved_by) REFERENCES users(id)
    ON UPDATE CASCADE ON DELETE SET NULL,
  UNIQUE KEY uq_dept_budget_month (department, fiscal_month),
  INDEX idx_dept_budget_status (status),
  INDEX idx_dept_budget_month (fiscal_month)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── 3. Add approval_status to finance_entries ───
-- MySQL < 8.0.30 doesn't support ADD COLUMN IF NOT EXISTS
ALTER TABLE finance_entries
  ADD COLUMN approval_status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'approved' AFTER approved_at;

-- ─── 4. Extra finance categories ───
INSERT IGNORE INTO finance_categories (category_type, code, name, description, is_system, is_active) VALUES
('income', 'SADAKA', 'Sadaka', 'Sadaka za ibada na matukio', 1, 1),
('income', 'HARAMBEE', 'Harambee / Michango', 'Michango maalum na harambee', 1, 1),
('income', 'PLEDGE_INCOME', 'Ahadi Zilizolipwa', 'Mapato kutoka ahadi za washirika', 1, 1),
('expense', 'SALARY', 'Mishahara', 'Staff salaries and allowances', 1, 1),
('expense', 'UTILITIES', 'Huduma (Umeme/Maji)', 'Utility bills - electricity, water', 1, 1),
('expense', 'CONSTRUCTION', 'Ujenzi', 'Construction and building projects', 1, 1),
('expense', 'AID', 'Misaada', 'Charity and benevolence aid', 1, 1),
('expense', 'TRANSPORT', 'Usafiri', 'Transport and travel expenses', 1, 1);

-- ─── 5. Sample pledges ───
INSERT IGNORE INTO pledges (pledge_no, member_id, campaign, description, total_amount, paid_amount, pledge_date, due_date, status, created_by) VALUES
('PLG-2026-001', 1, 'Ujenzi wa Hekalu', 'Ahadi ya ujenzi wa kanisa jipya', 500000.00, 200000.00, '2026-01-15', '2026-12-31', 'active', 1),
('PLG-2026-002', 2, 'Ujenzi wa Hekalu', 'Ahadi ya ujenzi', 300000.00, 300000.00, '2026-01-20', '2026-06-30', 'completed', 1),
('PLG-2026-003', 3, 'Vifaa vya Ibada', 'Ahadi ya kununua vifaa', 150000.00, 50000.00, '2026-02-10', '2026-09-30', 'active', 1);

-- ─── 6. Sample department budgets ───
INSERT IGNORE INTO department_budgets (department, fiscal_month, planned_amount, spent_amount, status, submitted_by, approved_by, approved_at) VALUES
('Ibada', '2026-03', 2000000.00, 1200000.00, 'approved', 2, 1, '2026-03-01 10:00:00'),
('Vijana', '2026-03', 800000.00, 650000.00, 'approved', 3, 1, '2026-03-01 10:00:00'),
('Ujenzi', '2026-03', 5000000.00, 3500000.00, 'approved', 2, 1, '2026-03-02 09:00:00'),
('Huduma', '2026-03', 500000.00, 480000.00, 'approved', 2, 1, '2026-03-01 10:00:00'),
('Ibada', '2026-04', 2000000.00, 0, 'submitted', 2, NULL, NULL),
('Vijana', '2026-04', 900000.00, 0, 'draft', 3, NULL, NULL);
