<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3306;dbname=church_cms', 'root', 'root');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

echo "=== Approval Workflows ===" . PHP_EOL;
$rows = $pdo->query('SELECT aw.*, r.name AS role_name FROM approval_workflows aw JOIN roles r ON r.id = aw.role_id ORDER BY aw.workflow_type, aw.level_no')->fetchAll();
foreach ($rows as $r) {
    echo $r['workflow_type'] . ' L' . $r['level_no'] . ' => ' . $r['role_name'] . ' (role_id=' . $r['role_id'] . ', active=' . $r['is_active'] . ')' . PHP_EOL;
}

echo PHP_EOL . "=== Pending Finance Entries ===" . PHP_EOL;
$rows = $pdo->query("SELECT id, entry_no, approval_status FROM finance_entries WHERE approval_status = 'pending' LIMIT 5")->fetchAll();
if (empty($rows)) echo "None pending" . PHP_EOL;
foreach ($rows as $r) echo $r['id'] . ' ' . $r['entry_no'] . ' ' . $r['approval_status'] . PHP_EOL;

echo PHP_EOL . "=== Submitted Budgets ===" . PHP_EOL;
$rows = $pdo->query("SELECT id, department, status FROM department_budgets WHERE status = 'submitted' LIMIT 5")->fetchAll();
if (empty($rows)) echo "None submitted" . PHP_EOL;
foreach ($rows as $r) echo $r['id'] . ' ' . $r['department'] . ' ' . $r['status'] . PHP_EOL;

echo PHP_EOL . "=== Recent Approval Logs ===" . PHP_EOL;
$rows = $pdo->query('SELECT entity_type, entity_id, level_no, action, actor_id FROM approval_logs ORDER BY acted_at DESC LIMIT 10')->fetchAll();
foreach ($rows as $r) echo $r['entity_type'] . ' #' . $r['entity_id'] . ' L' . $r['level_no'] . ' ' . $r['action'] . ' by user ' . $r['actor_id'] . PHP_EOL;

echo PHP_EOL . "=== Users ===" . PHP_EOL;
$rows = $pdo->query('SELECT id, full_name, role_id FROM users ORDER BY id LIMIT 10')->fetchAll();
foreach ($rows as $r) echo 'User ' . $r['id'] . ': ' . $r['full_name'] . ' (role_id=' . $r['role_id'] . ')' . PHP_EOL;
