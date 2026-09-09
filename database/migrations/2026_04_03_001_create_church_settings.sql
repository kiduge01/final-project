-- Create church_settings table for editable church profile
CREATE TABLE IF NOT EXISTS church_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed default values
INSERT IGNORE INTO church_settings (setting_key, setting_value) VALUES
('church_name', 'TAG MSASANI'),
('location', 'Dar es Salaam, Tanzania'),
('phone', ''),
('email', ''),
('address', ''),
('pastor_name', ''),
('founded_year', '');
