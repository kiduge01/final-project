<?php
$config = require_once __DIR__ . '/app/config.php';
$db = $config['db'];

try {
    $pdo = new PDO(
        'mysql:host=' . $db['host'] . ';dbname=' . $db['name'] . ';charset=' . $db['charset'],
        $db['user'],
        $db['pass'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    echo "📋 Current purchase_requests columns:\n\n";
    
    $stmt = $pdo->query("DESCRIBE purchase_requests");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($columns as $col) {
        echo "  - " . $col['Field'] . " (" . $col['Type'] . ")\n";
    }
    
    // Check if key columns exist
    $requiredCols = ['budget_id', 'vendor_name', 'approved_by', 'approved_at', 'rejection_reason', 'completed_at'];
    $existingCols = array_column($columns, 'Field');
    
    echo "\n✓ Status:\n";
    foreach ($requiredCols as $col) {
        $exists = in_array($col, $existingCols) ? '✓' : '✗';
        echo "  $exists $col\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
