-- Church Account / Bank Account tracking
-- Tracks different accounts (cash box, bank accounts, mobile money wallets)
-- Links to finance_entries via payment_method to auto-calculate balances

CREATE TABLE IF NOT EXISTS church_accounts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    account_name VARCHAR(150) NOT NULL,
    account_type ENUM('cash','bank','mobile_money') NOT NULL DEFAULT 'bank',
    bank_name VARCHAR(150) NULL,
    account_number VARCHAR(60) NULL,
    opening_balance DECIMAL(14,2) NOT NULL DEFAULT 0.00,
    description VARCHAR(255) NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_church_accounts_type (account_type),
    INDEX idx_church_accounts_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Link finance entries to specific accounts
-- Note: MySQL 9.x does not support ADD COLUMN IF NOT EXISTS
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'finance_entries' AND COLUMN_NAME = 'church_account_id');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE finance_entries ADD COLUMN church_account_id BIGINT UNSIGNED NULL AFTER department_id, ADD INDEX idx_fe_church_account (church_account_id)', 'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Seed default accounts
INSERT INTO church_accounts (account_name, account_type, bank_name, opening_balance, description) VALUES
('Pesa Taslimu (Cash Box)', 'cash', NULL, 0.00, 'Main church cash box'),
('M-Pesa', 'mobile_money', 'Vodacom', 0.00, 'Church M-Pesa account'),
('CRDB Bank', 'bank', 'CRDB Bank', 0.00, 'Church main bank account')
ON DUPLICATE KEY UPDATE account_name = VALUES(account_name);
