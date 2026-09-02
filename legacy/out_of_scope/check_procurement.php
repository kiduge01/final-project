<?php
$cfg = require 'app/config.php';
$db = $cfg['db'];
$dsn = 'mysql:host='.$db['host'].';port='.$db['port'].';dbname='.$db['name'].';charset=utf8mb4';
$pdo = new PDO($dsn, $db['user'], $db['pass']);
$tables = ['purchase_requests','purchase_request_items','department_budgets','approval_logs','budget_expenses'];
foreach($tables as $t) {
    $r = $pdo->query("SHOW TABLES LIKE '$t'")->fetch();
    echo $t . ': ' . ($r ? 'EXISTS' : 'MISSING') . "\n";
}

// Also check purchase_requests columns
echo "\n--- purchase_requests columns ---\n";
try {
    $cols = $pdo->query("DESCRIBE purchase_requests")->fetchAll(PDO::FETCH_COLUMN);
    echo implode(', ', $cols) . "\n";
} catch(Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

// Check permissions
echo "\n--- procurement permissions ---\n";
$stmt = $pdo->query("SELECT p.name FROM permissions p WHERE p.name LIKE 'procurement%'");
$perms = $stmt->fetchAll(PDO::FETCH_COLUMN);
echo implode(', ', $perms) . "\n";
