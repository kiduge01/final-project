-- Church Management System (Tanzania) - MySQL 8+
-- Full relational schema with module integration, auditability, and reporting support.

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS audit_logs;
DROP TABLE IF EXISTS login_attempts;
DROP TABLE IF EXISTS sms_logs;
DROP TABLE IF EXISTS event_attendance;
DROP TABLE IF EXISTS attendance_records;
DROP TABLE IF EXISTS services;
DROP TABLE IF EXISTS event_budget_items;
DROP TABLE IF EXISTS event_tasks;
DROP TABLE IF EXISTS event_finance_links;
DROP TABLE IF EXISTS events;
DROP TABLE IF EXISTS member_group_assignments;
DROP TABLE IF EXISTS groups;
DROP TABLE IF EXISTS department_reports;
DROP TABLE IF EXISTS department_leaders;
DROP TABLE IF EXISTS department_members;
DROP TABLE IF EXISTS departments;
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

CREATE TABLE login_attempts (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  identifier VARCHAR(255) NOT NULL COMMENT 'email or phone used for login',
  attempted_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_login_attempts_identifier (identifier),
  INDEX idx_login_attempts_time (attempted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── Department Subsystem Tables ───

CREATE TABLE departments (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL UNIQUE,
  description VARCHAR(255) NULL,
  head_user_id BIGINT UNSIGNED NULL COMMENT 'Optional department head (user)',
  head_name VARCHAR(100) NULL COMMENT 'Department head full name for separate login',
  head_email VARCHAR(100) NULL COMMENT 'Department head email for separate login',
  head_phone VARCHAR(30) NULL COMMENT 'Department head phone number',
  head_password_hash VARCHAR(255) NULL COMMENT 'Department head password (separate from users table)',
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_departments_head FOREIGN KEY (head_user_id) REFERENCES users(id)
    ON UPDATE CASCADE ON DELETE SET NULL,
  INDEX idx_departments_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE department_members (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  department_id BIGINT UNSIGNED NOT NULL,
  member_id BIGINT UNSIGNED NOT NULL,
  assigned_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  notes VARCHAR(255) NULL COMMENT 'Role or additional info (e.g., Worship Leader, Youth Coordinator)',
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_dept_member (department_id, member_id),
  CONSTRAINT fk_dept_members_dept FOREIGN KEY (department_id) REFERENCES departments(id)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_dept_members_member FOREIGN KEY (member_id) REFERENCES members(id)
    ON UPDATE CASCADE ON DELETE CASCADE,
  INDEX idx_dept_members_department (department_id),
  INDEX idx_dept_members_member (member_id),
  INDEX idx_dept_members_assigned_date (assigned_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE department_leaders (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  department_id BIGINT UNSIGNED NOT NULL,
  member_id BIGINT UNSIGNED NULL COMMENT 'FK to members table if leader is a member',
  leader_type VARCHAR(50) NOT NULL COMMENT 'e.g., Chairman, Treasurer, Secretary, Coordinator',
  leader_name VARCHAR(100) NOT NULL COMMENT 'Full name of leader (denormalized for easy display)',
  email VARCHAR(100) NULL,
  phone VARCHAR(20) NULL,
  bio TEXT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_dept_leaders_dept FOREIGN KEY (department_id) REFERENCES departments(id)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_dept_leaders_member FOREIGN KEY (member_id) REFERENCES members(id)
    ON UPDATE CASCADE ON DELETE SET NULL,
  INDEX idx_dept_leaders_department (department_id),
  INDEX idx_dept_leaders_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE department_reports (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  department_id BIGINT UNSIGNED NOT NULL,
  title VARCHAR(255) NOT NULL,
  description LONGTEXT NOT NULL,
  report_date DATE NOT NULL,
  category VARCHAR(50) NOT NULL COMMENT 'e.g., Weekly, Monthly, Activity, Finance, Quarterly, Annual, Event, Other',
  status ENUM('draft','submitted','approved','rejected') NOT NULL DEFAULT 'draft',
  submitted_at DATETIME NULL,
  reviewed_by BIGINT UNSIGNED NULL COMMENT 'Admin user who reviewed',
  reviewed_at DATETIME NULL,
  review_notes TEXT NULL,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_dept_reports_dept FOREIGN KEY (department_id) REFERENCES departments(id)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_dept_reports_reviewed_by FOREIGN KEY (reviewed_by) REFERENCES users(id)
    ON UPDATE CASCADE ON DELETE SET NULL,
  INDEX idx_dept_reports_department (department_id),
  INDEX idx_dept_reports_status (status)
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

-- Default departments
INSERT INTO departments (name, description, is_active) VALUES
('Ibada', 'Idara ya ibada na muziki', 1),
('Vijana', 'Idara ya vijana', 1),
('Ujenzi', 'Mradi wa ujenzi wa kanisa', 1),
('Huduma', 'Huduma za jamii na misaada', 1),
('Elimu', 'Elimu ya Biblia na mafunzo', 1),
('Utawala', 'Utawala na usimamizi wa kanisa', 1);
