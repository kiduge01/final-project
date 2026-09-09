<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>🔍 Direct Database Test</h2><pre>";

try {
    // Load config
    $config = require __DIR__ . '/../app/config.php';
    
    // Load Database class
    require_once __DIR__ . '/../app/core/Database.php';
    
    // Initialize database connection
    $pdo = \App\Core\Database::connection($config);
    
    echo "✅ Database connection established\n\n";
    
    // Direct query to test
    echo "Running direct database query...\n";
    $result = $pdo->query("SELECT setting_key, setting_value FROM church_settings WHERE setting_key IN ('church_name', 'church_logo')");
    
    if (!$result) {
        echo "❌ Query failed\n";
    } else {
        $rows = $result->fetchAll(PDO::FETCH_KEY_PAIR);
        echo "✅ Query succeeded\n";
        echo "Rows returned: " . count($rows) . "\n\n";
        
        foreach ($rows as $key => $value) {
            echo "  $key = '" . htmlspecialchars($value) . "'\n";
        }
        
        echo "\n✅ Church Name: " . (isset($rows['church_name']) ? $rows['church_name'] : 'NOT SET') . "\n";
        echo "✅ Church Logo: " . (isset($rows['church_logo']) ? $rows['church_logo'] : 'NOT SET') . "\n";
    }
    
} catch (\Throwable $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}

echo "</pre>";
?>
