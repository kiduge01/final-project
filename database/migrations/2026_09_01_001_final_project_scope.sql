CREATE TABLE IF NOT EXISTS guests (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  guest_code VARCHAR(50) NOT NULL UNIQUE,
  first_name VARCHAR(100) NOT NULL,
  last_name VARCHAR(100) NOT NULL,
  phone VARCHAR(30) NOT NULL,
  location VARCHAR(255) NOT NULL,
  email VARCHAR(150) NULL,
  age_group VARCHAR(30) NULL,
  visit_type VARCHAR(30) NOT NULL DEFAULT 'first_time',
  invited_by_name VARCHAR(100) NULL,
  service_date DATE NOT NULL,
  follow_up_date DATE NULL,
  notes TEXT NULL,
  status VARCHAR(30) NOT NULL DEFAULT 'registered',
  created_by BIGINT UNSIGNED NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_guests_phone (phone),
  INDEX idx_guests_service_date (service_date),
  INDEX idx_guests_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS ai_logs (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NULL,
  question TEXT NOT NULL,
  answer TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_ai_logs_user (user_id),
  INDEX idx_ai_logs_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
