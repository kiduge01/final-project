-- Add request_title column to department_budgets for linking to procurement
ALTER TABLE department_budgets ADD COLUMN IF NOT EXISTS request_title VARCHAR(255) NULL AFTER description;

-- Optional: Update existing budgets to have a request_title based on description
UPDATE department_budgets SET request_title = CONCAT(department, ' - ', description) WHERE request_title IS NULL AND description IS NOT NULL;
