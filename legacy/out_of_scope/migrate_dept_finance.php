<?php
/**
 * Quick migration runner for department_finance_records table
 * Delete this file after running once.
 */
$config = require __DIR__ . '/app/config.php';
require_once __DIR__ . '/app/core/Database.php';
$pdo = \App\Core\Database::connection($config);

$sql = file_get_contents(__DIR__ . '/database/migrations/2026_04_14_002_create_department_finance_records.sql');

try {
    $pdo->exec($sql);
    echo '<p style="color:green;font-weight:bold">✓ Migration applied: department_finance_records table created.</p>';
} catch (PDOException $e) {
    echo '<p style="color:red">Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
}
