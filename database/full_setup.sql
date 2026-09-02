-- Full one-shot setup for Church CMS
DROP DATABASE IF EXISTS DPP;
CREATE DATABASE DPP CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE church_cms;

-- Church Management System (Tanzania) - MySQL 8+
-- Full relational schema with module integration, auditability, and reporting support.

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS audit_logs;
DROP TABLE IF EXISTS sms_logs;
DROP TABLE IF EXISTS event_attendance;
DROP TABLE IF EXISTS attendance_records;
DROP TABLE IF EXISTS services;
DROP TABLE IF EXISTS event_budget_items;
DROP TABLE IF EXISTS event_tasks;
DROP TABLE IF EXISTS event_finance_links;
DROP TABLE IF EXISTS `events`;
DROP TABLE IF EXISTS member_group_assignments;
DROP TABLE IF EXISTS `groups`;
DROP TABLE IF EXISTS finance_entries;
DROP TABLE IF EXISTS finance_categories;
DROP TABLE IF EXISTS purchase_order_items;
DROP TABLE IF EXISTS purchase_orders;
DROP TABLE IF EXISTS procurement_approvals;
DROP TABLE IF EXISTS purchase_requests;
DROP TABLE IF EXISTS suppliers;
DROP TABLE IF EXISTS maintenance_logs;
DROP TABLE IF EXISTS asset_assignments;
DROP TABLE IF EXISTS assets;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS roles;
DROP TABLE IF EXISTS members;

SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE roles (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL UNIQUE,
  description VARCHAR(255) NULL,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE users (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  role_id BIGINT UNSIGNED NOT NULL,
  full_name VARCHAR(150) NOT NULL,
  email VARCHAR(150) NULL UNIQUE,
  phone VARCHAR(30) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  last_login_at DATETIME NULL,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_users_role FOREIGN KEY (role_id) REFERENCES roles(id)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  INDEX idx_users_role_id (role_id),
  INDEX idx_users_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE members (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  member_code VARCHAR(50) NOT NULL UNIQUE,
  first_name VARCHAR(100) NOT NULL,
  last_name VARCHAR(100) NOT NULL,
  gender ENUM('male', 'female', 'other') NOT NULL,
  date_of_birth DATE NULL,
  marital_status ENUM('single', 'married', 'widowed', 'divorced') NULL,
  phone VARCHAR(30) NOT NULL,
  alt_phone VARCHAR(30) NULL,
  email VARCHAR(150) NULL,
  physical_address VARCHAR(255) NULL,
  ward VARCHAR(100) NULL,
  district VARCHAR(100) NULL,
  region VARCHAR(100) NULL,
  emergency_contact_name VARCHAR(150) NULL,
  emergency_contact_phone VARCHAR(30) NULL,
  baptism_date DATE NULL,
  join_date DATE NOT NULL,
  member_status ENUM('active', 'inactive', 'transferred', 'deceased') NOT NULL DEFAULT 'active',
  notes TEXT NULL,
  created_by BIGINT UNSIGNED NULL,
  updated_by BIGINT UNSIGNED NULL,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_members_created_by FOREIGN KEY (created_by) REFERENCES users(id)
    ON UPDATE CASCADE ON DELETE SET NULL,
  CONSTRAINT fk_members_updated_by FOREIGN KEY (updated_by) REFERENCES users(id)
    ON UPDATE CASCADE ON DELETE SET NULL,
  INDEX idx_members_name (last_name, first_name),
  INDEX idx_members_status (member_status),
  INDEX idx_members_phone (phone),
  INDEX idx_members_region (region)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `groups` (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL UNIQUE,
  description VARCHAR(255) NULL,
  leader_member_id BIGINT UNSIGNED NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_groups_leader_member FOREIGN KEY (leader_member_id) REFERENCES members(id)
    ON UPDATE CASCADE ON DELETE SET NULL,
  INDEX idx_groups_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE member_group_assignments (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  member_id BIGINT UNSIGNED NOT NULL,
  group_id BIGINT UNSIGNED NOT NULL,
  assigned_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  assigned_by BIGINT UNSIGNED NULL,
  is_primary_group TINYINT(1) NOT NULL DEFAULT 0,
  ended_at DATETIME NULL,
  CONSTRAINT fk_member_groups_member FOREIGN KEY (member_id) REFERENCES members(id)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_member_groups_group FOREIGN KEY (group_id) REFERENCES `groups`(id)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_member_groups_assigned_by FOREIGN KEY (assigned_by) REFERENCES users(id)
    ON UPDATE CASCADE ON DELETE SET NULL,
  UNIQUE KEY uq_member_group_active (member_id, group_id, ended_at),
  INDEX idx_member_groups_group_id (group_id),
  INDEX idx_member_groups_member_id (member_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE services (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  service_date DATE NOT NULL,
  service_type ENUM('sunday', 'midweek', 'prayer', 'special') NOT NULL,
  title VARCHAR(150) NOT NULL,
  start_time TIME NULL,
  end_time TIME NULL,
  venue VARCHAR(150) NULL,
  led_by_user_id BIGINT UNSIGNED NULL,
  notes TEXT NULL,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_services_led_by FOREIGN KEY (led_by_user_id) REFERENCES users(id)
    ON UPDATE CASCADE ON DELETE SET NULL,
  INDEX idx_services_date (service_date),
  INDEX idx_services_type (service_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE attendance_records (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  service_id BIGINT UNSIGNED NOT NULL,
  member_id BIGINT UNSIGNED NOT NULL,
  attendance_status ENUM('present', 'absent', 'late', 'excused') NOT NULL DEFAULT 'present',
  check_in_time DATETIME NULL,
  recorded_by BIGINT UNSIGNED NULL,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_attendance_service FOREIGN KEY (service_id) REFERENCES services(id)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_attendance_member FOREIGN KEY (member_id) REFERENCES members(id)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_attendance_recorded_by FOREIGN KEY (recorded_by) REFERENCES users(id)
    ON UPDATE CASCADE ON DELETE SET NULL,
  UNIQUE KEY uq_attendance_service_member (service_id, member_id),
  INDEX idx_attendance_member_id (member_id),
  INDEX idx_attendance_status (attendance_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `events` (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  event_code VARCHAR(50) NOT NULL UNIQUE,
  title VARCHAR(180) NOT NULL,
  description TEXT NULL,
  category ENUM('conference', 'seminar', 'outreach', 'fundraiser', 'youth', 'choir', 'other') NOT NULL DEFAULT 'other',
  start_datetime DATETIME NOT NULL,
  end_datetime DATETIME NOT NULL,
  venue VARCHAR(180) NULL,
  organizer_user_id BIGINT UNSIGNED NULL,
  target_group_id BIGINT UNSIGNED NULL,
  expected_attendance INT UNSIGNED NULL,
  status ENUM('draft', 'planned', 'ongoing', 'completed', 'cancelled') NOT NULL DEFAULT 'draft',
  budget_total DECIMAL(14,2) NOT NULL DEFAULT 0,
  notes TEXT NULL,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_events_organizer FOREIGN KEY (organizer_user_id) REFERENCES users(id)
    ON UPDATE CASCADE ON DELETE SET NULL,
  CONSTRAINT fk_events_target_group FOREIGN KEY (target_group_id) REFERENCES `groups`(id)
    ON UPDATE CASCADE ON DELETE SET NULL,
  INDEX idx_events_start (start_datetime),
  INDEX idx_events_status (status),
  INDEX idx_events_category (category)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE event_tasks (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  event_id BIGINT UNSIGNED NOT NULL,
  title VARCHAR(180) NOT NULL,
  details TEXT NULL,
  assigned_to_user_id BIGINT UNSIGNED NOT NULL,
  due_datetime DATETIME NULL,
  task_status ENUM('todo', 'in_progress', 'done', 'cancelled') NOT NULL DEFAULT 'todo',
  priority ENUM('low', 'medium', 'high') NOT NULL DEFAULT 'medium',
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_event_tasks_event FOREIGN KEY (event_id) REFERENCES `events`(id)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_event_tasks_user FOREIGN KEY (assigned_to_user_id) REFERENCES users(id)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  INDEX idx_event_tasks_event_id (event_id),
  INDEX idx_event_tasks_user_id (assigned_to_user_id),
  INDEX idx_event_tasks_status (task_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE event_budget_items (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  event_id BIGINT UNSIGNED NOT NULL,
  item_type ENUM('income', 'expense') NOT NULL,
  item_name VARCHAR(180) NOT NULL,
  planned_amount DECIMAL(14,2) NOT NULL,
  actual_amount DECIMAL(14,2) NOT NULL DEFAULT 0,
  notes VARCHAR(255) NULL,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_event_budget_event FOREIGN KEY (event_id) REFERENCES `events`(id)
    ON UPDATE CASCADE ON DELETE CASCADE,
  INDEX idx_event_budget_event_id (event_id),
  INDEX idx_event_budget_type (item_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE event_attendance (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  event_id BIGINT UNSIGNED NOT NULL,
  member_id BIGINT UNSIGNED NOT NULL,
  status ENUM('registered', 'present', 'absent') NOT NULL DEFAULT 'registered',
  check_in_datetime DATETIME NULL,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_event_attendance_event FOREIGN KEY (event_id) REFERENCES `events`(id)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_event_attendance_member FOREIGN KEY (member_id) REFERENCES members(id)
    ON UPDATE CASCADE ON DELETE CASCADE,
  UNIQUE KEY uq_event_member (event_id, member_id),
  INDEX idx_event_attendance_member_id (member_id),
  INDEX idx_event_attendance_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE finance_categories (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  category_type ENUM('income', 'expense') NOT NULL,
  code VARCHAR(50) NOT NULL UNIQUE,
  name VARCHAR(120) NOT NULL,
  description VARCHAR(255) NULL,
  is_system TINYINT(1) NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_finance_categories_type (category_type),
  INDEX idx_finance_categories_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE suppliers (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  supplier_code VARCHAR(50) NOT NULL UNIQUE,
  name VARCHAR(150) NOT NULL,
  contact_person VARCHAR(150) NULL,
  phone VARCHAR(30) NOT NULL,
  email VARCHAR(150) NULL,
  tin_number VARCHAR(60) NULL,
  address VARCHAR(255) NULL,
  rating TINYINT UNSIGNED NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_suppliers_name (name),
  INDEX idx_suppliers_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE purchase_requests (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  request_no VARCHAR(60) NOT NULL UNIQUE,
  requested_by BIGINT UNSIGNED NOT NULL,
  department VARCHAR(120) NULL,
  purpose VARCHAR(255) NOT NULL,
  estimated_cost DECIMAL(14,2) NOT NULL,
  event_id BIGINT UNSIGNED NULL,
  requested_date DATE NOT NULL,
  required_by_date DATE NULL,
  status ENUM('draft', 'submitted', 'approved', 'rejected', 'ordered', 'closed') NOT NULL DEFAULT 'draft',
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_purchase_requests_user FOREIGN KEY (requested_by) REFERENCES users(id)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_purchase_requests_event FOREIGN KEY (event_id) REFERENCES `events`(id)
    ON UPDATE CASCADE ON DELETE SET NULL,
  INDEX idx_purchase_requests_status (status),
  INDEX idx_purchase_requests_date (requested_date),
  INDEX idx_purchase_requests_event_id (event_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE procurement_approvals (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  purchase_request_id BIGINT UNSIGNED NOT NULL,
  level_no TINYINT UNSIGNED NOT NULL,
  approver_user_id BIGINT UNSIGNED NOT NULL,
  decision ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
  decision_notes VARCHAR(255) NULL,
  decided_at DATETIME NULL,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_procurement_approval_request FOREIGN KEY (purchase_request_id) REFERENCES purchase_requests(id)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_procurement_approval_user FOREIGN KEY (approver_user_id) REFERENCES users(id)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  UNIQUE KEY uq_procurement_request_level (purchase_request_id, level_no),
  INDEX idx_procurement_approval_decision (decision)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE purchase_orders (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  po_no VARCHAR(60) NOT NULL UNIQUE,
  purchase_request_id BIGINT UNSIGNED NOT NULL,
  supplier_id BIGINT UNSIGNED NOT NULL,
  issued_by BIGINT UNSIGNED NOT NULL,
  issue_date DATE NOT NULL,
  expected_delivery_date DATE NULL,
  po_status ENUM('draft', 'issued', 'partially_received', 'received', 'cancelled') NOT NULL DEFAULT 'draft',
  subtotal DECIMAL(14,2) NOT NULL DEFAULT 0,
  tax_amount DECIMAL(14,2) NOT NULL DEFAULT 0,
  total_amount DECIMAL(14,2) NOT NULL DEFAULT 0,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_purchase_orders_request FOREIGN KEY (purchase_request_id) REFERENCES purchase_requests(id)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_purchase_orders_supplier FOREIGN KEY (supplier_id) REFERENCES suppliers(id)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_purchase_orders_issued_by FOREIGN KEY (issued_by) REFERENCES users(id)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  INDEX idx_purchase_orders_status (po_status),
  INDEX idx_purchase_orders_supplier_id (supplier_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE purchase_order_items (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  purchase_order_id BIGINT UNSIGNED NOT NULL,
  item_name VARCHAR(180) NOT NULL,
  quantity DECIMAL(12,2) NOT NULL,
  unit_price DECIMAL(14,2) NOT NULL,
  line_total DECIMAL(14,2) GENERATED ALWAYS AS (quantity * unit_price) STORED,
  received_quantity DECIMAL(12,2) NOT NULL DEFAULT 0,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_po_items_po FOREIGN KEY (purchase_order_id) REFERENCES purchase_orders(id)
    ON UPDATE CASCADE ON DELETE CASCADE,
  INDEX idx_po_items_po_id (purchase_order_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE finance_entries (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  entry_no VARCHAR(60) NOT NULL UNIQUE,
  entry_date DATE NOT NULL,
  category_id BIGINT UNSIGNED NOT NULL,
  amount DECIMAL(14,2) NOT NULL,
  payment_method ENUM('cash', 'mobile_money', 'bank_transfer', 'card', 'other') NOT NULL,
  reference_no VARCHAR(120) NULL,
  source_type ENUM('manual', 'event', 'procurement', 'system') NOT NULL DEFAULT 'manual',
  source_id BIGINT UNSIGNED NULL,
  event_id BIGINT UNSIGNED NULL,
  member_id BIGINT UNSIGNED NULL,
  supplier_id BIGINT UNSIGNED NULL,
  purchase_order_id BIGINT UNSIGNED NULL,
  description VARCHAR(255) NOT NULL,
  recorded_by BIGINT UNSIGNED NOT NULL,
  approved_by BIGINT UNSIGNED NULL,
  approved_at DATETIME NULL,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_finance_entries_category FOREIGN KEY (category_id) REFERENCES finance_categories(id)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_finance_entries_event FOREIGN KEY (event_id) REFERENCES `events`(id)
    ON UPDATE CASCADE ON DELETE SET NULL,
  CONSTRAINT fk_finance_entries_member FOREIGN KEY (member_id) REFERENCES members(id)
    ON UPDATE CASCADE ON DELETE SET NULL,
  CONSTRAINT fk_finance_entries_supplier FOREIGN KEY (supplier_id) REFERENCES suppliers(id)
    ON UPDATE CASCADE ON DELETE SET NULL,
  CONSTRAINT fk_finance_entries_po FOREIGN KEY (purchase_order_id) REFERENCES purchase_orders(id)
    ON UPDATE CASCADE ON DELETE SET NULL,
  CONSTRAINT fk_finance_entries_recorded_by FOREIGN KEY (recorded_by) REFERENCES users(id)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_finance_entries_approved_by FOREIGN KEY (approved_by) REFERENCES users(id)
    ON UPDATE CASCADE ON DELETE SET NULL,
  INDEX idx_finance_entries_date (entry_date),
  INDEX idx_finance_entries_category_id (category_id),
  INDEX idx_finance_entries_event_id (event_id),
  INDEX idx_finance_entries_source (source_type, source_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE event_finance_links (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  event_id BIGINT UNSIGNED NOT NULL,
  finance_entry_id BIGINT UNSIGNED NOT NULL,
  relation_type ENUM('income', 'expense') NOT NULL,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_event_finance_event FOREIGN KEY (event_id) REFERENCES `events`(id)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_event_finance_entry FOREIGN KEY (finance_entry_id) REFERENCES finance_entries(id)
    ON UPDATE CASCADE ON DELETE CASCADE,
  UNIQUE KEY uq_event_finance_unique (event_id, finance_entry_id),
  INDEX idx_event_finance_type (relation_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE assets (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  asset_tag VARCHAR(60) NOT NULL UNIQUE,
  name VARCHAR(180) NOT NULL,
  category VARCHAR(120) NOT NULL,
  purchase_date DATE NULL,
  purchase_value DECIMAL(14,2) NULL,
  condition_status ENUM('excellent', 'good', 'fair', 'poor', 'retired') NOT NULL DEFAULT 'good',
  current_location VARCHAR(180) NOT NULL,
  assigned_to_user_id BIGINT UNSIGNED NULL,
  assigned_event_id BIGINT UNSIGNED NULL,
  warranty_expiry DATE NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  notes TEXT NULL,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_assets_user FOREIGN KEY (assigned_to_user_id) REFERENCES users(id)
    ON UPDATE CASCADE ON DELETE SET NULL,
  CONSTRAINT fk_assets_event FOREIGN KEY (assigned_event_id) REFERENCES `events`(id)
    ON UPDATE CASCADE ON DELETE SET NULL,
  INDEX idx_assets_category (category),
  INDEX idx_assets_condition (condition_status),
  INDEX idx_assets_location (current_location)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE asset_assignments (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  asset_id BIGINT UNSIGNED NOT NULL,
  assigned_type ENUM('user', 'event', 'location') NOT NULL,
  assigned_user_id BIGINT UNSIGNED NULL,
  assigned_event_id BIGINT UNSIGNED NULL,
  assigned_location VARCHAR(180) NULL,
  assigned_from DATETIME NOT NULL,
  assigned_to DATETIME NULL,
  assigned_by BIGINT UNSIGNED NOT NULL,
  notes VARCHAR(255) NULL,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_asset_assignment_asset FOREIGN KEY (asset_id) REFERENCES assets(id)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_asset_assignment_user FOREIGN KEY (assigned_user_id) REFERENCES users(id)
    ON UPDATE CASCADE ON DELETE SET NULL,
  CONSTRAINT fk_asset_assignment_event FOREIGN KEY (assigned_event_id) REFERENCES `events`(id)
    ON UPDATE CASCADE ON DELETE SET NULL,
  CONSTRAINT fk_asset_assignment_assigned_by FOREIGN KEY (assigned_by) REFERENCES users(id)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  INDEX idx_asset_assignments_asset_id (asset_id),
  INDEX idx_asset_assignments_type (assigned_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE maintenance_logs (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  asset_id BIGINT UNSIGNED NOT NULL,
  maintenance_type ENUM('routine', 'repair', 'inspection', 'replacement') NOT NULL,
  issue_description TEXT NULL,
  action_taken TEXT NOT NULL,
  service_provider VARCHAR(150) NULL,
  maintenance_cost DECIMAL(14,2) NOT NULL DEFAULT 0,
  maintenance_date DATE NOT NULL,
  next_due_date DATE NULL,
  created_by BIGINT UNSIGNED NOT NULL,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_maintenance_asset FOREIGN KEY (asset_id) REFERENCES assets(id)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_maintenance_created_by FOREIGN KEY (created_by) REFERENCES users(id)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  INDEX idx_maintenance_asset_id (asset_id),
  INDEX idx_maintenance_date (maintenance_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE theme_verses (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  verse_reference VARCHAR(120) NOT NULL,
  verse_text TEXT NOT NULL,
  translation VARCHAR(60) NOT NULL DEFAULT 'SWAHILI',
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  display_weight INT UNSIGNED NOT NULL DEFAULT 1,
  start_date DATE NULL,
  end_date DATE NULL,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_theme_verses_active (is_active),
  INDEX idx_theme_verses_dates (start_date, end_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE sms_logs (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  recipient_type ENUM('member', 'group', 'custom') NOT NULL,
  group_id BIGINT UNSIGNED NULL,
  member_id BIGINT UNSIGNED NULL,
  phone VARCHAR(30) NOT NULL,
  message_text VARCHAR(480) NOT NULL,
  message_type ENUM('broadcast', 'event_reminder', 'alert') NOT NULL DEFAULT 'broadcast',
  provider VARCHAR(100) NULL,
  provider_message_id VARCHAR(150) NULL,
  delivery_status ENUM('queued', 'sent', 'failed', 'delivered') NOT NULL DEFAULT 'queued',
  event_id BIGINT UNSIGNED NULL,
  sent_by BIGINT UNSIGNED NOT NULL,
  sent_at DATETIME NULL,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_sms_logs_group FOREIGN KEY (group_id) REFERENCES `groups`(id)
    ON UPDATE CASCADE ON DELETE SET NULL,
  CONSTRAINT fk_sms_logs_member FOREIGN KEY (member_id) REFERENCES members(id)
    ON UPDATE CASCADE ON DELETE SET NULL,
  CONSTRAINT fk_sms_logs_event FOREIGN KEY (event_id) REFERENCES `events`(id)
    ON UPDATE CASCADE ON DELETE SET NULL,
  CONSTRAINT fk_sms_logs_sent_by FOREIGN KEY (sent_by) REFERENCES users(id)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  INDEX idx_sms_logs_status (delivery_status),
  INDEX idx_sms_logs_event_id (event_id),
  INDEX idx_sms_logs_type (message_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE audit_logs (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  actor_user_id BIGINT UNSIGNED NULL,
  module_name VARCHAR(80) NOT NULL,
  action_name VARCHAR(80) NOT NULL,
  entity_type VARCHAR(80) NOT NULL,
  entity_id BIGINT UNSIGNED NULL,
  change_summary VARCHAR(255) NULL,
  old_values JSON NULL,
  new_values JSON NULL,
  ip_address VARCHAR(45) NULL,
  user_agent VARCHAR(255) NULL,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_audit_logs_actor FOREIGN KEY (actor_user_id) REFERENCES users(id)
    ON UPDATE CASCADE ON DELETE SET NULL,
  INDEX idx_audit_module_action (module_name, action_name),
  INDEX idx_audit_entity (entity_type, entity_id),
  INDEX idx_audit_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Baseline roles and finance categories.
INSERT INTO roles (name, description) VALUES
('Admin', 'Full access across all modules'),
('Finance Officer', 'Manages income, expenses, and reports'),
('Secretary', 'Manages members, attendance, communication, events'),
('Standard User', 'Limited operational access');

INSERT INTO finance_categories (category_type, code, name, description, is_system, is_active) VALUES
('income', 'TITHE', 'Tithe', 'Regular member tithe', 1, 1),
('income', 'OFFERING', 'Offering', 'Service and event offerings', 1, 1),
('income', 'DONATION', 'Donation', 'External/internal donations', 1, 1),
('expense', 'PROCUREMENT', 'Procurement Expense', 'Expenses from approved procurement', 1, 1),
('expense', 'MAINTENANCE', 'Maintenance', 'Asset maintenance and repair', 1, 1),
('expense', 'EVENT_EXPENSE', 'Event Expense', 'Event operational expenses', 1, 1);

-- Sample data for quick demo/testing after importing schema.sql

INSERT INTO `users` (role_id, full_name, email, phone, password_hash)
VALUES
(1, 'System Admin', 'admin@kanisa.local', '+255700000001', '$2y$10$t4lqssfnwtVuXkPU1EACiOqpmR7v.4eltsePDWixlICoHRTVqqyQ.'),
(2, 'Finance Officer', 'finance@kanisa.local', '+255700000002', '$2y$10$t4lqssfnwtVuXkPU1EACiOqpmR7v.4eltsePDWixlICoHRTVqqyQ.'),
(3, 'Church Secretary', 'secretary@kanisa.local', '+255700000003', '$2y$10$t4lqssfnwtVuXkPU1EACiOqpmR7v.4eltsePDWixlICoHRTVqqyQ.'),
(4, 'Standard User', 'user@kanisa.local', '+255700000004', '$2y$10$t4lqssfnwtVuXkPU1EACiOqpmR7v.4eltsePDWixlICoHRTVqqyQ.');

INSERT INTO `groups` (name, description, is_active) VALUES
('Youth', 'Youth ministry', 1),
('Choir', 'Worship choir team', 1),
('Women Fellowship', 'Women ministry', 1);

INSERT INTO members (
  member_code, first_name, last_name, gender, phone, join_date, member_status, region, district, ward, created_by
)
VALUES
('MBR-0001', 'Neema', 'Mushi', 'female', '+255712345001', '2025-02-10', 'active', 'Dar es Salaam', 'Kinondoni', 'Mikocheni', 3),
('MBR-0002', 'Daniel', 'Mhando', 'male', '+255712345002', '2024-11-20', 'active', 'Dar es Salaam', 'Ilala', 'Kariakoo', 3),
('MBR-0003', 'Asha', 'Kweka', 'female', '+255712345003', '2023-09-01', 'active', 'Arusha', 'Arusha Urban', 'Sakina', 3);

INSERT INTO `member_group_assignments` (member_id, group_id, assigned_by, is_primary_group)
VALUES
(1, 1, 3, 1),
(2, 2, 3, 1),
(3, 3, 3, 1);

INSERT INTO services (service_date, service_type, title, venue, led_by_user_id)
VALUES
('2026-03-22', 'sunday', 'Sunday Worship', 'Main Sanctuary', 3),
('2026-03-25', 'midweek', 'Midweek Prayer', 'Prayer Hall', 3);

INSERT INTO attendance_records (service_id, member_id, attendance_status, check_in_time, recorded_by)
VALUES
(1, 1, 'present', '2026-03-22 08:13:00', 3),
(1, 2, 'late', '2026-03-22 08:40:00', 3),
(2, 1, 'present', '2026-03-25 17:58:00', 3);

INSERT INTO `events` (
  event_code, title, category, start_datetime, end_datetime, venue, organizer_user_id, target_group_id, expected_attendance, status, budget_total
)
VALUES
('EVT-2026-001', 'Youth Outreach Night', 'outreach', '2026-03-30 18:00:00', '2026-03-30 22:00:00', 'Main Hall', 3, 1, 200, 'planned', 2200000.00);

INSERT INTO event_tasks (event_id, title, assigned_to_user_id, due_datetime, task_status, priority)
VALUES
(1, 'Sound Setup', 4, '2026-03-30 16:00:00', 'in_progress', 'high'),
(1, 'Usher Briefing', 3, '2026-03-30 17:30:00', 'todo', 'medium');

INSERT INTO event_budget_items (event_id, item_type, item_name, planned_amount, actual_amount)
VALUES
(1, 'expense', 'Publicity', 300000.00, 270000.00),
(1, 'expense', 'Refreshments', 900000.00, 0.00),
(1, 'income', 'Event Offering', 1500000.00, 0.00);

INSERT INTO suppliers (supplier_code, name, contact_person, phone, email, is_active)
VALUES
('SUP-001', 'Mwangaza Supplies Ltd', 'Elias John', '+255754000111', 'sales@mwangaza.co.tz', 1);

INSERT INTO purchase_requests (
  request_no, requested_by, department, purpose, estimated_cost, event_id, requested_date, required_by_date, status
)
VALUES
('PR-2026-031', 3, 'Youth Ministry', 'Sound and publicity materials', 780000.00, 1, '2026-03-24', '2026-03-29', 'approved');

INSERT INTO procurement_approvals (purchase_request_id, level_no, approver_user_id, decision, decision_notes, decided_at)
VALUES
(1, 1, 2, 'approved', 'Approved within event budget', '2026-03-25 10:15:00');

INSERT INTO purchase_orders (
  po_no, purchase_request_id, supplier_id, issued_by, issue_date, expected_delivery_date, po_status, subtotal, tax_amount, total_amount
)
VALUES
('PO-2026-017', 1, 1, 2, '2026-03-25', '2026-03-29', 'issued', 700000.00, 80000.00, 780000.00);

INSERT INTO purchase_order_items (purchase_order_id, item_name, quantity, unit_price, received_quantity)
VALUES
(1, 'Flyers Print', 1, 280000.00, 1),
(1, 'Audio Cables', 5, 100000.00, 3);

INSERT INTO finance_entries (
  entry_no, entry_date, category_id, amount, payment_method, source_type, source_id, event_id, supplier_id, purchase_order_id, description, recorded_by
)
VALUES
('FIN-2026-101', '2026-03-26', 4, 780000.00, 'bank_transfer', 'procurement', 1, 1, 1, 1, 'Procurement expense for Youth Outreach', 2),
('FIN-2026-102', '2026-03-30', 2, 1240000.00, 'cash', 'event', 1, 1, NULL, NULL, 'Youth Outreach event offering', 2);

INSERT INTO event_finance_links (event_id, finance_entry_id, relation_type)
VALUES
(1, 1, 'expense'),
(1, 2, 'income');

INSERT INTO assets (asset_tag, name, category, purchase_date, purchase_value, condition_status, current_location, is_active)
VALUES
('AST-001', 'Yamaha Mixer', 'Audio Equipment', '2024-02-10', 2400000.00, 'good', 'Media Room', 1),
('AST-002', 'Plastic Chairs (set)', 'Furniture', '2023-05-17', 1200000.00, 'fair', 'Store 1', 1);

INSERT INTO asset_assignments (asset_id, assigned_type, assigned_event_id, assigned_from, assigned_by, notes)
VALUES
(1, 'event', 1, '2026-03-30 15:30:00', 3, 'Assigned for Youth Outreach');

INSERT INTO maintenance_logs (
  asset_id, maintenance_type, issue_description, action_taken, service_provider, maintenance_cost, maintenance_date, created_by
)
VALUES
(1, 'repair', 'Channel noise on input 3', 'Internal board cleaned and connector replaced', 'AudioFix TZ', 180000.00, '2026-03-20', 2);

INSERT INTO sms_logs (
  recipient_type, group_id, phone, message_text, message_type, provider, delivery_status, event_id, sent_by, sent_at
)
VALUES
('group', 1, '+255712345001', 'Reminder: Youth Outreach starts 6 PM today. Karibu sana!', 'event_reminder', 'Beem', 'sent', 1, 3, '2026-03-30 10:00:00');

INSERT INTO audit_logs (
  actor_user_id, module_name, action_name, entity_type, entity_id, change_summary, old_values, new_values, ip_address, user_agent
)
VALUES
(3, 'events', 'create', 'events', 1, 'Created Youth Outreach event', NULL, JSON_OBJECT('title', 'Youth Outreach Night'), '127.0.0.1', 'Web Browser'),
(2, 'procurement', 'approve', 'purchase_requests', 1, 'Approved purchase request PR-2026-031', JSON_OBJECT('status', 'submitted'), JSON_OBJECT('status', 'approved'), '127.0.0.1', 'Web Browser');
