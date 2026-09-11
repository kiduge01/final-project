-- Sadaka Module: Offerings/Donations Management System
-- Tables for different types of Sadaka: Love, Development, Tithes, and Contributions

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- Sadaka Categories (types of offerings)
CREATE TABLE IF NOT EXISTS sadaka_categories (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  category_name VARCHAR(100) NOT NULL UNIQUE,
  category_description TEXT NULL,
  category_slug VARCHAR(100) NOT NULL UNIQUE,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_category_slug (category_slug),
  INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sadaka Entries (individual contributions)
CREATE TABLE IF NOT EXISTS sadaka_entries (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  member_id BIGINT UNSIGNED NOT NULL,
  category_id BIGINT UNSIGNED NOT NULL,
  entry_month INT NOT NULL,
  entry_year INT NOT NULL,
  entry_week INT NULL,
  amount DECIMAL(10,2) NOT NULL,
  entry_date DATE NOT NULL,
  notes TEXT NULL,
  entered_by BIGINT UNSIGNED NULL,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_sadaka_member FOREIGN KEY (member_id) REFERENCES members(id)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_sadaka_category FOREIGN KEY (category_id) REFERENCES sadaka_categories(id)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_sadaka_entered_by FOREIGN KEY (entered_by) REFERENCES users(id)
    ON UPDATE CASCADE ON DELETE SET NULL,
  INDEX idx_member_category (member_id, category_id),
  INDEX idx_entry_date (entry_date),
  INDEX idx_year_month (entry_year, entry_month),
  INDEX idx_week (entry_week)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sadaka Upload History (for tracking Excel imports)
CREATE TABLE IF NOT EXISTS sadaka_uploads (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  category_id BIGINT UNSIGNED NOT NULL,
  upload_filename VARCHAR(255) NOT NULL,
  total_rows INT NOT NULL,
  successful_rows INT NOT NULL,
  failed_rows INT NOT NULL,
  upload_date DATE NOT NULL,
  uploaded_by BIGINT UNSIGNED NULL,
  error_log TEXT NULL,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_upload_category FOREIGN KEY (category_id) REFERENCES sadaka_categories(id)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_upload_user FOREIGN KEY (uploaded_by) REFERENCES users(id)
    ON UPDATE CASCADE ON DELETE SET NULL,
  INDEX idx_category_id (category_id),
  INDEX idx_upload_date (upload_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default Sadaka categories
INSERT INTO sadaka_categories (category_name, category_description, category_slug, is_active) VALUES
('Sadaka za Upendo', 'Offerings from love and willing heart', 'sadaka-za-upendo', 1),
('Sadaka za Maendeleo', 'Development and building fund contributions', 'sadaka-za-maendeleo', 1),
('Mafungu ya Kumi', 'Tithes - 10% income offerings', 'mafungu-ya-kumi', 1),
('Machangizo', 'Pledges and special contributions', 'machangizo', 1)
ON DUPLICATE KEY UPDATE updated_at = NOW();

SET FOREIGN_KEY_CHECKS = 1;
