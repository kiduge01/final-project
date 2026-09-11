<?php
define('BASE_URL', '/Cmain/public');
session_start();
$_SESSION['user'] = ['id' => 1, 'full_name' => 'Admin', 'role' => 'admin'];

$cfg = require 'app/config.php';
$db = $cfg['db'];
$dsn = 'mysql:host='.$db['host'].';port='.$db['port'].';dbname='.$db['name'].';charset=utf8mb4';
try {
    $pdo = new PDO($dsn, $db['user'], $db['pass']);
    echo "DB connected\n";
    
    // Test simple query
    $stmt = $pdo->query("SELECT COUNT(*) as cnt FROM purchase_requests");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "PR count: " . $result['cnt'] . "\n";
    
    // Test the query from API
    $sql = "SELECT pr.id, pr.request_no, u.full_name AS requested_by_name
            FROM purchase_requests pr
            LEFT JOIN users u ON u.id = pr.requested_by
            LIMIT 1";
    $stmt = $pdo->query($sql);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Sample PR: " . json_encode($row) . "\n";
    
} catch (PDOException $e) {
    echo "DB Error: " . $e->getMessage() . "\n";
}
