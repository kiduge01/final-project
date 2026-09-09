<?php
$config = require __DIR__ . '/app/config.php';
$db = $config['db'];

try {
    $pdo = new PDO(
        sprintf('mysql:host=%s;port=%d;dbname=%s;charset=%s', $db['host'], $db['port'], $db['name'], $db['charset']),
        $db['user'],
        $db['pass']
    );
    
    $stmt = $pdo->query("SHOW COLUMNS FROM members");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h2>Members Table Columns:</h2>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    
    foreach ($columns as $col) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($col['Field']) . "</td>";
        echo "<td>" . htmlspecialchars($col['Type']) . "</td>";
        echo "<td>" . htmlspecialchars($col['Null']) . "</td>";
        echo "<td>" . htmlspecialchars($col['Key']) . "</td>";
        echo "<td>" . htmlspecialchars($col['Default'] ?? 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Check if zone_id exists
    $zoneExists = array_filter($columns, fn($c) => $c['Field'] === 'zone_id');
    echo "<h3>" . (empty($zoneExists) ? "❌ zone_id column MISSING" : "✅ zone_id column EXISTS") . "</h3>";
    
} catch (Exception $e) {
    echo "Error: " . htmlspecialchars($e->getMessage());
}
