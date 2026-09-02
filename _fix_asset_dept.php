<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3306;dbname=church_cms', 'root', 'root');
$count = $pdo->query("SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='church_cms' AND TABLE_NAME='asset_assignments' AND COLUMN_NAME='assigned_department_id'")->fetchColumn();
echo "assigned_department_id exists: " . ($count > 0 ? "YES" : "NO") . PHP_EOL;
if ($count == 0) {
    echo "Adding column..." . PHP_EOL;
    $pdo->exec("ALTER TABLE asset_assignments ADD COLUMN assigned_department_id BIGINT UNSIGNED NULL AFTER assigned_location");
    echo "Done" . PHP_EOL;
    // Add index
    try { $pdo->exec("CREATE INDEX idx_asset_assign_dept ON asset_assignments(assigned_department_id)"); } catch(Exception $e) {}
}
