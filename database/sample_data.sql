-- Sample data for quick demo/testing after importing schema.sql

-- First, insert roles (must be before users due to FK constraint)
INSERT INTO `roles` (id, name, description)
VALUES
(1, 'Admin', 'System administrator with full access'),
(2, 'Finance Officer', 'Manages financial records and reports'),
(3, 'Secretary', 'Church secretary managing communications and records'),
(4, 'Standard User', 'Regular user with limited access');

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
