<?php
$cfg = require 'app/config.php';
$db = $cfg['db'];
$dsn = 'mysql:host='.$db['host'].';port='.$db['port'].';dbname='.$db['name'].';charset=utf8mb4';
try {
    $pdo = new PDO($dsn, $db['user'], $db['pass'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    
    // Simplest possible query first
    echo "Test 1: SELECT *\n";
    $stmt = $pdo->query("SELECT id, request_no FROM purchase_requests LIMIT 1");
    print_r($stmt->fetch());
    
    // Test with users join
    echo "\nTest 2: With user join\n";
    $sql = "SELECT pr.id, pr.request_no, u.full_name AS requested_by_name
            FROM purchase_requests pr
            LEFT JOIN users u ON u.id = pr.requested_by
            LIMIT 1";
    $stmt = $pdo->query($sql);
    print_r($stmt->fetch());
    
    // Test with all joins
    echo "\nTest 3: With all joins\n";
    $sql = "SELECT pr.id, pr.request_no,
                   u.full_name AS requested_by_name,
                   db.department AS budget_department,
                   ev.title AS event_title,
                   a.full_name AS approved_by_name
            FROM purchase_requests pr
            LEFT JOIN users u ON u.id = pr.requested_by
            LEFT JOIN users a ON a.id = pr.approved_by
            LEFT JOIN department_budgets db ON db.id = pr.budget_id
            LEFT JOIN events ev ON ev.id = pr.event_id
            LIMIT 1";
    $stmt = $pdo->query($sql);
    print_r($stmt->fetch());
    
    // Test with subqueries
    echo "\nTest 4: With subqueries\n";
    $sql = "SELECT pr.id,
            (SELECT COALESCE(SUM(pri.line_total), 0) FROM purchase_request_items pri WHERE pri.purchase_request_id = pr.id) AS items_total,
            (SELECT COUNT(*) FROM purchase_request_items pri WHERE pri.purchase_request_id = pr.id) AS item_count
            FROM purchase_requests pr
            LIMIT 1";
    $stmt = $pdo->query($sql);
    print_r($stmt->fetch());
    
    echo "\n✓ All tests passed!\n";
} catch (Exception $e) {
    echo "✗ ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}
