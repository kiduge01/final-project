-- Migration: Create Zone Management System
-- Date: 2026-05-14
-- Purpose: Add tables for zone management with members, ushers, events, and offerings

SET NAMES utf8mb4;

-- Create zones table
CREATE TABLE IF NOT EXISTS `zones` (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL UNIQUE,
  description VARCHAR(255) NULL,
  location VARCHAR(180) NOT NULL,
  coordinates VARCHAR(100) NULL COMMENT 'lat,lng format',
  zone_leader_id BIGINT UNSIGNED NULL COMMENT 'Primary leader/usher',
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_zones_leader FOREIGN KEY (zone_leader_id) REFERENCES members(id)
    ON UPDATE CASCADE ON DELETE SET NULL,
  INDEX idx_zones_active (is_active),
  INDEX idx_zones_location (location)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Zone members assignment
CREATE TABLE IF NOT EXISTS `zone_members` (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  zone_id BIGINT UNSIGNED NOT NULL,
  member_id BIGINT UNSIGNED NOT NULL,
  assigned_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  assigned_by BIGINT UNSIGNED NULL COMMENT 'User who assigned',
  notes VARCHAR(255) NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_zone_member (zone_id, member_id),
  CONSTRAINT fk_zone_members_zone FOREIGN KEY (zone_id) REFERENCES zones(id)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_zone_members_member FOREIGN KEY (member_id) REFERENCES members(id)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_zone_members_assigned_by FOREIGN KEY (assigned_by) REFERENCES users(id)
    ON UPDATE CASCADE ON DELETE SET NULL,
  INDEX idx_zone_members_zone (zone_id),
  INDEX idx_zone_members_member (member_id),
  INDEX idx_zone_members_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Zone ushers (leaders)
CREATE TABLE IF NOT EXISTS `zone_ushers` (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  zone_id BIGINT UNSIGNED NOT NULL,
  member_id BIGINT UNSIGNED NOT NULL,
  usher_role ENUM('head', 'assistant') NOT NULL COMMENT 'Head Usher or Assistant',
  assigned_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  assigned_by BIGINT UNSIGNED NULL COMMENT 'User who assigned',
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_zone_usher (zone_id, member_id, usher_role),
  CONSTRAINT fk_zone_ushers_zone FOREIGN KEY (zone_id) REFERENCES zones(id)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_zone_ushers_member FOREIGN KEY (member_id) REFERENCES members(id)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_zone_ushers_assigned_by FOREIGN KEY (assigned_by) REFERENCES users(id)
    ON UPDATE CASCADE ON DELETE SET NULL,
  INDEX idx_zone_ushers_zone (zone_id),
  INDEX idx_zone_ushers_member (member_id),
  INDEX idx_zone_ushers_role (usher_role),
  INDEX idx_zone_ushers_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Zone events
CREATE TABLE IF NOT EXISTS `zone_events` (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  zone_id BIGINT UNSIGNED NOT NULL,
  event_id BIGINT UNSIGNED NULL COMMENT 'Link to main events table if applicable',
  title VARCHAR(180) NOT NULL,
  description TEXT NULL,
  event_date DATETIME NOT NULL,
  venue VARCHAR(180) NULL,
  organizer_member_id BIGINT UNSIGNED NULL,
  expected_attendance INT UNSIGNED NULL,
  status ENUM('planned', 'ongoing', 'completed', 'cancelled') NOT NULL DEFAULT 'planned',
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_zone_events_zone FOREIGN KEY (zone_id) REFERENCES zones(id)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_zone_events_event FOREIGN KEY (event_id) REFERENCES events(id)
    ON UPDATE CASCADE ON DELETE SET NULL,
  CONSTRAINT fk_zone_events_organizer FOREIGN KEY (organizer_member_id) REFERENCES members(id)
    ON UPDATE CASCADE ON DELETE SET NULL,
  INDEX idx_zone_events_zone (zone_id),
  INDEX idx_zone_events_date (event_date),
  INDEX idx_zone_events_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Zone event offerings/collections
CREATE TABLE IF NOT EXISTS `zone_event_offerings` (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  zone_event_id BIGINT UNSIGNED NOT NULL,
  member_id BIGINT UNSIGNED NULL COMMENT 'Member who contributed (optional if anonymous)',
  offering_type ENUM('tithe', 'offering', 'donation', 'project_fund') NOT NULL,
  amount DECIMAL(14,2) NOT NULL,
  payment_method ENUM('cash', 'mobile_money', 'bank_transfer', 'check', 'other') NOT NULL DEFAULT 'cash',
  reference_no VARCHAR(120) NULL,
  recorded_by BIGINT UNSIGNED NOT NULL,
  notes VARCHAR(255) NULL,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_zone_offerings_event FOREIGN KEY (zone_event_id) REFERENCES zone_events(id)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_zone_offerings_member FOREIGN KEY (member_id) REFERENCES members(id)
    ON UPDATE CASCADE ON DELETE SET NULL,
  CONSTRAINT fk_zone_offerings_recorded_by FOREIGN KEY (recorded_by) REFERENCES users(id)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  INDEX idx_zone_offerings_event (zone_event_id),
  INDEX idx_zone_offerings_member (member_id),
  INDEX idx_zone_offerings_type (offering_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Zone attendance/participation tracking
CREATE TABLE IF NOT EXISTS `zone_event_attendance` (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  zone_event_id BIGINT UNSIGNED NOT NULL,
  member_id BIGINT UNSIGNED NOT NULL,
  status ENUM('registered', 'present', 'absent', 'excused') NOT NULL DEFAULT 'registered',
  check_in_time DATETIME NULL,
  check_out_time DATETIME NULL,
  recorded_by BIGINT UNSIGNED NULL,
  notes VARCHAR(255) NULL,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_zone_event_member (zone_event_id, member_id),
  CONSTRAINT fk_zone_attendance_event FOREIGN KEY (zone_event_id) REFERENCES zone_events(id)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_zone_attendance_member FOREIGN KEY (member_id) REFERENCES members(id)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_zone_attendance_recorded_by FOREIGN KEY (recorded_by) REFERENCES users(id)
    ON UPDATE CASCADE ON DELETE SET NULL,
  INDEX idx_zone_attendance_event (zone_event_id),
  INDEX idx_zone_attendance_member (member_id),
  INDEX idx_zone_attendance_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
