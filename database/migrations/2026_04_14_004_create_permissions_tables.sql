-- Migration: Create permissions and role_permissions tables
-- Seeds all module permissions and assigns defaults per role
-- Date: 2026-04-14

SET NAMES utf8mb4;
SET foreign_key_checks = 0;

-- ─── 1. permissions ───
CREATE TABLE IF NOT EXISTS permissions (
  id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name        VARCHAR(80)  NOT NULL UNIQUE COMMENT 'e.g. members.create',
  module      VARCHAR(40)  NOT NULL        COMMENT 'e.g. members',
  description VARCHAR(150) NULL,
  created_at  TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_permissions_module (module)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── 2. role_permissions ───
CREATE TABLE IF NOT EXISTS role_permissions (
  id            BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  role_id       BIGINT UNSIGNED NOT NULL,
  permission_id BIGINT UNSIGNED NOT NULL,
  created_at    TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_role_perm (role_id, permission_id),
  CONSTRAINT fk_rp_role FOREIGN KEY (role_id)       REFERENCES roles(id)       ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_rp_perm FOREIGN KEY (permission_id) REFERENCES permissions(id) ON UPDATE CASCADE ON DELETE CASCADE,
  INDEX idx_rp_role_id (role_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── 3. Seed permissions ───
INSERT IGNORE INTO permissions (name, module, description) VALUES
-- Members
('members.view',         'members',        'View member list and profiles'),
('members.create',       'members',        'Add new members'),
('members.edit',         'members',        'Edit member details'),
('members.delete',       'members',        'Delete/deactivate members'),
('members.export',       'members',        'Export member data to CSV'),
-- Departments
('departments.view',     'departments',    'View departments'),
('departments.create',   'departments',    'Create new departments'),
('departments.edit',     'departments',    'Edit department details'),
('departments.delete',   'departments',    'Delete departments'),
-- Events
('events.view',          'events',         'View events and calendar'),
('events.create',        'events',         'Create new events'),
('events.edit',          'events',         'Edit event details'),
('events.delete',        'events',         'Delete events'),
-- Attendance
('attendance.view',      'attendance',     'View attendance records'),
('attendance.record',    'attendance',     'Record attendance'),
-- Finance
('finance.view',         'finance',        'View finance records and reports'),
('finance.create',       'finance',        'Create income/expense entries'),
('finance.approve',      'finance',        'Approve finance entries'),
('finance.delete',       'finance',        'Delete finance entries'),
-- Procurement
('procurement.view',     'procurement',    'View purchase requests'),
('procurement.create',   'procurement',    'Create purchase requests'),
('procurement.approve',  'procurement',    'Approve/reject purchase requests'),
('procurement.complete', 'procurement',    'Mark orders as complete'),
-- Assets
('assets.view',          'assets',         'View asset register'),
('assets.create',        'assets',         'Add new assets'),
('assets.edit',          'assets',         'Edit asset details'),
('assets.delete',        'assets',         'Delete/retire assets'),
-- Communication
('communication.view',   'communication',  'View messages and broadcasts'),
('communication.send',   'communication',  'Send messages and broadcasts'),
-- Reports
('reports.view',         'reports',        'View all reports'),
('reports.export',       'reports',        'Export reports to CSV/Excel'),
-- Settings
('settings.manage',      'settings',       'Manage system settings, users, and roles');

-- ─── 4. Assign permissions to roles ───

-- Admin (role_id=1): ALL permissions
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT 1, id FROM permissions;

-- Finance Officer (role_id=2): finance + procurement + assets view + reports
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT 2, id FROM permissions WHERE name IN (
  'finance.view','finance.create','finance.approve','finance.delete',
  'procurement.view','procurement.approve','procurement.complete','procurement.create',
  'assets.view',
  'reports.view','reports.export',
  'events.view',
  'members.view'
);

-- Secretary (role_id=3): members + events + attendance + communication + departments view + reports view
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT 3, id FROM permissions WHERE name IN (
  'members.view','members.create','members.edit','members.export',
  'departments.view',
  'events.view','events.create','events.edit',
  'attendance.view','attendance.record',
  'communication.view','communication.send',
  'reports.view'
);

-- Standard User (role_id=4): view only
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT 4, id FROM permissions WHERE name IN (
  'members.view',
  'events.view',
  'attendance.view',
  'departments.view'
);

SET foreign_key_checks = 1;
