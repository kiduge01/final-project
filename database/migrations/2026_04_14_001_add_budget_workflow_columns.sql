-- Add missing columns to department_budgets that the listBudgets API query expects
-- These columns support the full budget workflow (spend tracking, closing, finance entry linking)

ALTER TABLE department_budgets ADD COLUMN IF NOT EXISTS spent_amount DECIMAL(14,2) NOT NULL DEFAULT 0.00 AFTER reserved_amount;
ALTER TABLE department_budgets ADD COLUMN IF NOT EXISTS actual_notes TEXT NULL AFTER actual_amount;
ALTER TABLE department_budgets ADD COLUMN IF NOT EXISTS closed_at DATETIME NULL AFTER approved_at;
ALTER TABLE department_budgets ADD COLUMN IF NOT EXISTS closed_by BIGINT UNSIGNED NULL AFTER closed_at;
ALTER TABLE department_budgets ADD COLUMN IF NOT EXISTS finance_entry_id BIGINT UNSIGNED NULL AFTER closed_by;
