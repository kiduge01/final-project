-- ============================================================
-- Migration: Finance-Procurement Integration
-- Roles, Permissions, Approval Workflows, Budget-Procurement Link
-- Date: 2026-04-02
-- ============================================================

-- 1. UPDATE AND ADD ROLES
-- --------------------------------------------------------
UPDATE roles SET name = 'Accountant', description = 'Reviews and manages budgets, tracks expenses' WHERE name = 'Finance Officer';

INSERT IGNORE INTO roles (name, description) VALUES
('Event Organizer', 'Can create events and request budgets'),
('Procurement Officer', 'Handles purchase requests and vendor processing'),
('Approver', 'Authority to approve budgets and procurement (e.g., Pastor/Manager)');

-- 2. PERMISSIONS TABLE
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS permissions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    module VARCHAR(50) NOT NULL,
    description VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO permissions (name, module, description) VALUES
('finance.budget.create',       'finance',     'Create budget requests'),
('finance.budget.approve',      'finance',     'Approve or reject budget requests'),
('finance.budget.close',        'finance',     'Close budgets and lock expenses'),
('finance.expense.create',      'finance',     'Add expenses to budgets'),
('finance.entries.create',      'finance',     'Create finance entries'),
('finance.entries.approve',     'finance',     'Approve finance entries'),
('finance.reports.view',        'finance',     'View financial reports'),
('procurement.request.create',  'procurement', 'Create procurement requests'),
('procurement.request.approve', 'procurement', 'Approve procurement requests'),
('procurement.po.create',       'procurement', 'Create purchase orders'),
('procurement.po.complete',     'procurement', 'Mark procurement as purchased/completed'),
('events.create',               'events',      'Create and manage events'),
('events.budget.request',       'events',      'Request budget for events'),
('settings.manage',             'settings',    'Manage system settings'),
('members.manage',              'members',     'Manage church members'),
('reports.view',                'reports',     'View all reports');

-- 3. ROLE_PERMISSIONS TABLE
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS role_permissions (
    role_id BIGINT UNSIGNED NOT NULL,
    permission_id BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (role_id, permission_id),
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
    FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Admin: all permissions
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r CROSS JOIN permissions p WHERE r.name = 'Admin';

-- Accountant
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r, permissions p
WHERE r.name = 'Accountant' AND p.name IN (
    'finance.budget.create', 'finance.budget.close', 'finance.expense.create',
    'finance.entries.create', 'finance.entries.approve', 'finance.reports.view',
    'reports.view'
);

-- Event Organizer
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r, permissions p
WHERE r.name = 'Event Organizer' AND p.name IN (
    'events.create', 'events.budget.request', 'finance.budget.create', 'reports.view'
);

-- Procurement Officer
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r, permissions p
WHERE r.name = 'Procurement Officer' AND p.name IN (
    'procurement.request.create', 'procurement.po.create', 'procurement.po.complete', 'reports.view'
);

-- Approver (Pastor/Manager)
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r, permissions p
WHERE r.name = 'Approver' AND p.name IN (
    'finance.budget.approve', 'finance.entries.approve',
    'procurement.request.approve', 'finance.reports.view', 'reports.view'
);

-- Secretary
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r, permissions p
WHERE r.name = 'Secretary' AND p.name IN (
    'members.manage', 'events.create', 'reports.view'
);

-- 4. APPROVAL WORKFLOWS TABLE
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS approval_workflows (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    workflow_type VARCHAR(50) NOT NULL,
    level_no TINYINT UNSIGNED NOT NULL DEFAULT 1,
    role_id BIGINT UNSIGNED NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
    UNIQUE KEY uq_workflow_level (workflow_type, level_no)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Default approval chains
INSERT IGNORE INTO approval_workflows (workflow_type, level_no, role_id) VALUES
('budget', 1, (SELECT id FROM roles WHERE name = 'Accountant')),
('budget', 2, (SELECT id FROM roles WHERE name = 'Approver')),
('procurement', 1, (SELECT id FROM roles WHERE name = 'Approver')),
('finance_entry', 1, (SELECT id FROM roles WHERE name = 'Accountant'));

-- 5. APPROVAL LOGS TABLE
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS approval_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    entity_type VARCHAR(50) NOT NULL,
    entity_id BIGINT UNSIGNED NOT NULL,
    level_no TINYINT UNSIGNED NOT NULL DEFAULT 1,
    action ENUM('submitted', 'approved', 'rejected', 'returned') NOT NULL,
    actor_id BIGINT UNSIGNED NOT NULL,
    notes TEXT NULL,
    acted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (actor_id) REFERENCES users(id),
    INDEX idx_approval_entity (entity_type, entity_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6. DEPARTMENT_BUDGETS MODIFICATIONS
-- --------------------------------------------------------
ALTER TABLE department_budgets ADD COLUMN IF NOT EXISTS reserved_amount DECIMAL(14,2) NOT NULL DEFAULT 0 AFTER spent_amount;
ALTER TABLE department_budgets ADD COLUMN IF NOT EXISTS event_id BIGINT UNSIGNED NULL AFTER department;
ALTER TABLE department_budgets ADD COLUMN IF NOT EXISTS description VARCHAR(255) NULL AFTER notes;

-- Ensure budget_expenses table exists (may have been created dynamically)
CREATE TABLE IF NOT EXISTS budget_expenses (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    budget_id BIGINT UNSIGNED NOT NULL,
    item_name VARCHAR(180) NOT NULL,
    amount DECIMAL(14,2) NOT NULL,
    expense_date DATE NOT NULL,
    notes VARCHAR(255) NULL,
    source_type VARCHAR(30) DEFAULT 'manual',
    source_id BIGINT UNSIGNED NULL,
    recorded_by BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (budget_id) REFERENCES department_budgets(id) ON DELETE CASCADE,
    INDEX idx_be_budget (budget_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Add source tracking columns to budget_expenses if missing
ALTER TABLE budget_expenses ADD COLUMN IF NOT EXISTS source_type VARCHAR(30) DEFAULT 'manual' AFTER notes;
ALTER TABLE budget_expenses ADD COLUMN IF NOT EXISTS source_id BIGINT UNSIGNED NULL AFTER source_type;

-- 7. PURCHASE_REQUESTS MODIFICATIONS
-- --------------------------------------------------------
ALTER TABLE purchase_requests ADD COLUMN IF NOT EXISTS budget_id BIGINT UNSIGNED NULL AFTER event_id;
ALTER TABLE purchase_requests ADD COLUMN IF NOT EXISTS vendor_name VARCHAR(255) NULL AFTER required_by_date;
ALTER TABLE purchase_requests ADD COLUMN IF NOT EXISTS approval_level TINYINT UNSIGNED NOT NULL DEFAULT 0 AFTER status;
ALTER TABLE purchase_requests ADD COLUMN IF NOT EXISTS approved_by BIGINT UNSIGNED NULL;
ALTER TABLE purchase_requests ADD COLUMN IF NOT EXISTS approved_at DATETIME NULL;
ALTER TABLE purchase_requests ADD COLUMN IF NOT EXISTS completed_at DATETIME NULL;
ALTER TABLE purchase_requests ADD COLUMN IF NOT EXISTS rejection_reason VARCHAR(500) NULL;

-- Expand status ENUM to support new workflow
ALTER TABLE purchase_requests MODIFY COLUMN status
    ENUM('draft','submitted','pending','approved','rejected','ordered','purchased','received','completed','cancelled','closed')
    NOT NULL DEFAULT 'draft';

-- 8. PURCHASE_REQUEST_ITEMS TABLE
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS purchase_request_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    purchase_request_id BIGINT UNSIGNED NOT NULL,
    item_name VARCHAR(255) NOT NULL,
    quantity DECIMAL(10,2) NOT NULL DEFAULT 1,
    estimated_unit_cost DECIMAL(14,2) NOT NULL DEFAULT 0,
    line_total DECIMAL(14,2) GENERATED ALWAYS AS (quantity * estimated_unit_cost) STORED,
    notes VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (purchase_request_id) REFERENCES purchase_requests(id) ON DELETE CASCADE,
    INDEX idx_pri_request (purchase_request_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
