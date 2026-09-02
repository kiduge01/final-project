-- Create guests registration table
-- This table stores individual guest information for church attendance

CREATE TABLE IF NOT EXISTS guests (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  guest_code VARCHAR(50) NOT NULL UNIQUE COMMENT 'Auto-generated guest code',
  first_name VARCHAR(100) NOT NULL,
  last_name VARCHAR(100) NOT NULL,
  phone VARCHAR(30) NOT NULL,
  location VARCHAR(255) NOT NULL COMMENT 'Guest home location/area',
  invited_by_member_id BIGINT UNSIGNED NULL COMMENT 'FK to members table - who invited them',
  invited_by_name VARCHAR(100) NULL COMMENT 'Name of person who invited (if not a member)',
  service_date DATE NOT NULL COMMENT 'Date of service attended',
  visit_type ENUM('first_time', 'returning', 'referred') NOT NULL DEFAULT 'first_time',
  email VARCHAR(150) NULL,
  age_group ENUM('child', 'teen', 'youth', 'adult', 'senior') NULL,
  notes TEXT NULL,
  status ENUM('registered', 'visited', 'converted', 'inactive') NOT NULL DEFAULT 'registered',
  follow_up_date DATE NULL COMMENT 'Planned follow-up date',
  created_by BIGINT UNSIGNED NULL COMMENT 'Admin user who registered',
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  CONSTRAINT fk_guests_invited_by FOREIGN KEY (invited_by_member_id) REFERENCES members(id)
    ON UPDATE CASCADE ON DELETE SET NULL,
  CONSTRAINT fk_guests_created_by FOREIGN KEY (created_by) REFERENCES users(id)
    ON UPDATE CASCADE ON DELETE SET NULL,
  
  INDEX idx_guests_phone (phone),
  INDEX idx_guests_service_date (service_date),
  INDEX idx_guests_status (status),
  INDEX idx_guests_location (location)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
