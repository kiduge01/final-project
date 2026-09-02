<?php
/**
 * Church CMS - Full Database Fix Script
 * Fixes all missing tables, columns and relationships for the Events + Finance system
 * Run once at: http://localhost/Cmain/run_event_budget_migration.php
 */
$config = require __DIR__ . '/app/config.php';

$ok = [];
$errors = [];

function check(PDO $pdo, string $table, string $col): bool {
    $r = $pdo->query("SHOW COLUMNS FROM `$table` LIKE '$col'")->fetch();
    return (bool)$r;
}
function checkTable(PDO $pdo, string $table): bool {
    $r = $pdo->query("SHOW TABLES LIKE '$table'")->fetch();
    return (bool)$r;
}
function checkIndex(PDO $pdo, string $table, string $index): bool {
    $r = $pdo->query("SHOW INDEX FROM `$table` WHERE Key_name='$index'")->fetch();
    return (bool)$r;
}

try {
    $db = $config['db'];
    $pdo = new PDO(
        sprintf('mysql:host=%s;port=%d;dbname=%s;charset=%s', $db['host'], $db['port'], $db['name'], $db['charset']),
        $db['user'], $db['pass'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // ────────────────────────────────────────────────────────────────
    // 1. events table — add missing columns
    // ────────────────────────────────────────────────────────────────

    // Rename category → event_type if needed
    if (check($pdo, 'events', 'category') && !check($pdo, 'events', 'event_type')) {
        $pdo->exec("ALTER TABLE `events` CHANGE COLUMN `category`
            `event_type` ENUM('service','seminar','meeting','appointment','other') NOT NULL DEFAULT 'other'");
        $ok[] = 'events.category renamed to event_type';
    } elseif (!check($pdo, 'events', 'event_type')) {
        $pdo->exec("ALTER TABLE `events` ADD COLUMN `event_type`
            ENUM('service','seminar','meeting','appointment','other') NOT NULL DEFAULT 'other' AFTER `title`");
        $ok[] = 'events.event_type added';
    } else {
        $ok[] = 'events.event_type already exists';
    }

    // location (some schemas have venue instead)
    if (!check($pdo, 'events', 'location')) {
        if (check($pdo, 'events', 'venue')) {
            $pdo->exec("ALTER TABLE `events` ADD COLUMN `location` VARCHAR(180) NULL AFTER `venue`");
        } else {
            $pdo->exec("ALTER TABLE `events` ADD COLUMN `location` VARCHAR(180) NULL AFTER `end_datetime`");
        }
        $ok[] = 'events.location added';
    } else {
        $ok[] = 'events.location already exists';
    }

    // pastor_on_duty
    if (!check($pdo, 'events', 'pastor_on_duty')) {
        $pdo->exec("ALTER TABLE `events` ADD COLUMN `pastor_on_duty` VARCHAR(150) NULL AFTER `location`");
        $ok[] = 'events.pastor_on_duty added';
    } else {
        $ok[] = 'events.pastor_on_duty already exists';
    }

    // usher_on_duty
    if (!check($pdo, 'events', 'usher_on_duty')) {
        $pdo->exec("ALTER TABLE `events` ADD COLUMN `usher_on_duty` VARCHAR(150) NULL AFTER `pastor_on_duty`");
        $ok[] = 'events.usher_on_duty added';
    } else {
        $ok[] = 'events.usher_on_duty already exists';
    }

    // budget_status
    if (!check($pdo, 'events', 'budget_status')) {
        $pdo->exec("ALTER TABLE `events`
            ADD COLUMN `budget_status` ENUM('draft','pending_approval','approved','rejected','in_progress','completed')
            NOT NULL DEFAULT 'draft' AFTER `budget_total`");
        $ok[] = 'events.budget_status added';
    } else {
        $ok[] = 'events.budget_status already exists';
    }

    // budget_approved_by
    if (!check($pdo, 'events', 'budget_approved_by')) {
        $pdo->exec("ALTER TABLE `events`
            ADD COLUMN `budget_approved_by` BIGINT UNSIGNED NULL AFTER `budget_status`,
            ADD COLUMN `budget_approved_at` DATETIME NULL AFTER `budget_approved_by`,
            ADD COLUMN `budget_locked_at` DATETIME NULL AFTER `budget_approved_at`");
        $ok[] = 'events.budget_approved_by/at/locked_at added';
    } else {
        $ok[] = 'events.budget_approved_by already exists';
    }

    // ────────────────────────────────────────────────────────────────
    // 2. event_budget_items table
    // ────────────────────────────────────────────────────────────────
    if (!checkTable($pdo, 'event_budget_items')) {
        $pdo->exec("CREATE TABLE event_budget_items (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            event_id BIGINT UNSIGNED NOT NULL,
            item_type ENUM('income','expense') NOT NULL,
            budget_status ENUM('draft','pending_approval','approved','rejected','in_progress','completed') NOT NULL DEFAULT 'draft',
            item_name VARCHAR(180) NOT NULL,
            planned_amount DECIMAL(14,2) NOT NULL,
            actual_amount DECIMAL(14,2) NOT NULL DEFAULT 0,
            notes VARCHAR(255) NULL,
            approved_by BIGINT UNSIGNED NULL,
            approved_at DATETIME NULL,
            rejection_reason TEXT NULL,
            created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            CONSTRAINT fk_event_budget_event FOREIGN KEY (event_id) REFERENCES `events`(id) ON UPDATE CASCADE ON DELETE CASCADE,
            INDEX idx_event_budget_event_id (event_id),
            INDEX idx_event_budget_type (item_type),
            INDEX idx_event_budget_status (budget_status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        $ok[] = 'event_budget_items table CREATED';
    } else {
        $ok[] = 'event_budget_items table already exists';
        // Ensure extra columns exist
        foreach (['budget_status','approved_by','approved_at','rejection_reason'] as $col) {
            if (!check($pdo, 'event_budget_items', $col)) {
                if ($col === 'budget_status') {
                    $pdo->exec("ALTER TABLE event_budget_items
                        ADD COLUMN budget_status ENUM('draft','pending_approval','approved','rejected','in_progress','completed')
                        NOT NULL DEFAULT 'draft' AFTER item_type");
                } elseif ($col === 'approved_by') {
                    $pdo->exec("ALTER TABLE event_budget_items ADD COLUMN approved_by BIGINT UNSIGNED NULL AFTER notes");
                } elseif ($col === 'approved_at') {
                    $pdo->exec("ALTER TABLE event_budget_items ADD COLUMN approved_at DATETIME NULL AFTER approved_by");
                } elseif ($col === 'rejection_reason') {
                    $pdo->exec("ALTER TABLE event_budget_items ADD COLUMN rejection_reason TEXT NULL AFTER approved_at");
                }
                $ok[] = "event_budget_items.$col added";
            }
        }
    }

    // ────────────────────────────────────────────────────────────────
    // 3. finance_entries — add approval_status if missing
    // ────────────────────────────────────────────────────────────────
    if (!check($pdo, 'finance_entries', 'approval_status')) {
        $pdo->exec("ALTER TABLE finance_entries
            ADD COLUMN `approval_status` ENUM('pending','approved','rejected') NOT NULL DEFAULT 'approved' AFTER `approved_at`");
        $ok[] = 'finance_entries.approval_status added';
    } else {
        $ok[] = 'finance_entries.approval_status already exists';
    }

    // ────────────────────────────────────────────────────────────────
    // 4. event_finance_links — ensure relation_type column exists
    // ────────────────────────────────────────────────────────────────
    if (!checkTable($pdo, 'event_finance_links')) {
        $pdo->exec("CREATE TABLE event_finance_links (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            event_id BIGINT UNSIGNED NOT NULL,
            finance_entry_id BIGINT UNSIGNED NOT NULL,
            relation_type ENUM('income','expense') NOT NULL DEFAULT 'expense',
            created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT fk_efl_event FOREIGN KEY (event_id) REFERENCES `events`(id) ON UPDATE CASCADE ON DELETE CASCADE,
            CONSTRAINT fk_efl_entry FOREIGN KEY (finance_entry_id) REFERENCES finance_entries(id) ON UPDATE CASCADE ON DELETE CASCADE,
            UNIQUE KEY uq_event_finance_unique (event_id, finance_entry_id),
            INDEX idx_efl_type (relation_type)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        $ok[] = 'event_finance_links table CREATED with relation_type';
    } else {
        if (!check($pdo, 'event_finance_links', 'relation_type')) {
            $pdo->exec("ALTER TABLE event_finance_links
                ADD COLUMN `relation_type` ENUM('income','expense') NOT NULL DEFAULT 'expense' AFTER `finance_entry_id`");
            $ok[] = 'event_finance_links.relation_type added';
        } else {
            $ok[] = 'event_finance_links.relation_type already exists';
        }
    }

    // ────────────────────────────────────────────────────────────────
    // 5. event_attendance — create if missing
    // ────────────────────────────────────────────────────────────────
    if (!checkTable($pdo, 'event_attendance')) {
        $pdo->exec("CREATE TABLE event_attendance (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            event_id BIGINT UNSIGNED NOT NULL,
            member_id BIGINT UNSIGNED NOT NULL,
            status ENUM('registered','present','absent') NOT NULL DEFAULT 'registered',
            check_in_datetime DATETIME NULL,
            created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            CONSTRAINT fk_event_att_event FOREIGN KEY (event_id) REFERENCES `events`(id) ON UPDATE CASCADE ON DELETE CASCADE,
            CONSTRAINT fk_event_att_member FOREIGN KEY (member_id) REFERENCES members(id) ON UPDATE CASCADE ON DELETE CASCADE,
            UNIQUE KEY uq_event_member (event_id, member_id),
            INDEX idx_event_att_member (member_id),
            INDEX idx_event_att_status (status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        $ok[] = 'event_attendance table CREATED';
    } else {
        $ok[] = 'event_attendance already exists';
    }

    // ────────────────────────────────────────────────────────────────
    // 6. event_tasks — create if missing
    // ────────────────────────────────────────────────────────────────
    if (!checkTable($pdo, 'event_tasks')) {
        $pdo->exec("CREATE TABLE event_tasks (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            event_id BIGINT UNSIGNED NOT NULL,
            title VARCHAR(180) NOT NULL,
            details TEXT NULL,
            assigned_to_user_id BIGINT UNSIGNED NOT NULL,
            due_datetime DATETIME NULL,
            task_status ENUM('todo','in_progress','done','cancelled') NOT NULL DEFAULT 'todo',
            priority ENUM('low','medium','high') NOT NULL DEFAULT 'medium',
            created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            CONSTRAINT fk_event_tasks_event FOREIGN KEY (event_id) REFERENCES `events`(id) ON UPDATE CASCADE ON DELETE CASCADE,
            CONSTRAINT fk_event_tasks_user FOREIGN KEY (assigned_to_user_id) REFERENCES users(id) ON UPDATE CASCADE ON DELETE RESTRICT,
            INDEX idx_event_tasks_event_id (event_id),
            INDEX idx_event_tasks_status (task_status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        $ok[] = 'event_tasks table CREATED';
    } else {
        $ok[] = 'event_tasks already exists';
    }

    // ────────────────────────────────────────────────────────────────
    // 7. sms_logs — create if missing
    // ────────────────────────────────────────────────────────────────
    if (!checkTable($pdo, 'sms_logs')) {
        $pdo->exec("CREATE TABLE sms_logs (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            phone VARCHAR(30) NOT NULL,
            message_text TEXT NOT NULL,
            message_type ENUM('reminder','notification','report','event_reminder','other') NOT NULL DEFAULT 'other',
            recipient_type VARCHAR(50) NULL,
            provider VARCHAR(50) NULL DEFAULT 'internal',
            message_id BIGINT UNSIGNED NULL,
            event_id BIGINT UNSIGNED NULL,
            member_id BIGINT UNSIGNED NULL,
            group_id BIGINT UNSIGNED NULL,
            delivery_status ENUM('queued','sent','delivered','failed') NOT NULL DEFAULT 'queued',
            sent_by BIGINT UNSIGNED NOT NULL,
            sent_at DATETIME NULL,
            created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT fk_sms_event FOREIGN KEY (event_id) REFERENCES `events`(id) ON UPDATE CASCADE ON DELETE SET NULL,
            CONSTRAINT fk_sms_member FOREIGN KEY (member_id) REFERENCES members(id) ON UPDATE CASCADE ON DELETE SET NULL,
            CONSTRAINT fk_sms_sent_by FOREIGN KEY (sent_by) REFERENCES users(id) ON UPDATE CASCADE ON DELETE RESTRICT,
            INDEX idx_sms_event_id (event_id),
            INDEX idx_sms_status (delivery_status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        $ok[] = 'sms_logs table CREATED';
    } else {
        // Add missing columns to existing sms_logs
        $missingCols = [
            'recipient_type' => "ALTER TABLE sms_logs ADD COLUMN `recipient_type` VARCHAR(50) NULL AFTER message_type",
            'provider'       => "ALTER TABLE sms_logs ADD COLUMN `provider` VARCHAR(50) NULL DEFAULT 'internal' AFTER recipient_type",
            'message_id'     => "ALTER TABLE sms_logs ADD COLUMN `message_id` BIGINT UNSIGNED NULL AFTER id",
        ];
        foreach ($missingCols as $col => $sql) {
            if (!check($pdo, 'sms_logs', $col)) {
                $pdo->exec($sql);
                $ok[] = "sms_logs.$col added";
            }
        }
        $ok[] = 'sms_logs already exists (columns verified)';
    }

    // ────────────────────────────────────────────────────────────────
    // 8. finance_categories — ensure EVENT_EXPENSE category exists
    // ────────────────────────────────────────────────────────────────
    // ────────────────────────────────────────────────────────────────
    $cat = $pdo->query("SELECT id FROM finance_categories WHERE code='EVENT_EXPENSE' LIMIT 1")->fetchColumn();
    if (!$cat) {
        $pdo->exec("INSERT IGNORE INTO finance_categories (category_type, code, name, description, is_system, is_active)
            VALUES ('expense', 'EVENT_EXPENSE', 'Event Expenses', 'Budget expenses for events and activities', 1, 1)");
        $ok[] = 'finance_categories: EVENT_EXPENSE category added';
    } else {
        $ok[] = 'finance_categories: EVENT_EXPENSE already exists';
    }

    // ────────────────────────────────────────────────────────────────
    // 9. Verify indexes on events table
    // ────────────────────────────────────────────────────────────────
    if (!checkIndex($pdo, 'events', 'idx_events_budget_status')) {
        $pdo->exec("ALTER TABLE `events` ADD INDEX idx_events_budget_status (budget_status)");
        $ok[] = 'events: idx_events_budget_status index added';
    }

} catch (Exception $e) {
    $errors[] = $e->getMessage();
}

// ── OUTPUT ────────────────────────────────────────
?><!DOCTYPE html>
<html>
<head><title>Church CMS - DB Fix</title>
<style>
body{font-family:system-ui,sans-serif;max-width:800px;margin:40px auto;padding:0 20px}
h2{color:#1e3a8a}
.ok{color:#16a34a;padding:4px 0;border-bottom:1px solid #dcfce7}
.err{color:#dc2626;padding:4px 0;border-bottom:1px solid #fee2e2;font-weight:bold}
.box{border:1px solid #e2e8f0;border-radius:8px;padding:16px;margin:12px 0}
.done{background:#f0fdf4;border-color:#86efac;padding:16px;border-radius:8px;margin-top:20px}
</style>
</head>
<body>
<h2>Church CMS — Database Fix Report</h2>
<?php if ($errors): ?>
<div class="box">
<b>Errors:</b>
<?php foreach ($errors as $e): ?>
<div class="err">✗ <?= htmlspecialchars($e) ?></div>
<?php endforeach; ?>
</div>
<?php endif; ?>
<div class="box">
<?php foreach ($ok as $msg): ?>
<div class="ok">✓ <?= htmlspecialchars($msg) ?></div>
<?php endforeach; ?>
</div>
<?php if (!$errors): ?>
<div class="done">
<strong>✅ All fixes applied successfully!</strong><br>
You can now <a href="/Cmain/public/events">go to Events</a> and create/view events with budgets.<br><br>
<em>You may delete this file after verifying everything works.</em>
</div>
<?php endif; ?>
</body>
</html>
