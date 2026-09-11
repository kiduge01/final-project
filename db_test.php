<?php
// Test database connection
$config = require __DIR__ . '/app/config.php';

echo "<h2>Database Connection Test</h2>";
echo "<pre>";

try {
    $db = $config['db'];
    echo "Attempting to connect to: {$db['host']}:{$db['port']}/{$db['name']}\n";
    echo "User: {$db['user']}\n";
    echo "DSN: mysql:host={$db['host']};port={$db['port']};dbname={$db['name']};charset={$db['charset']}\n\n";
    
    $pdo = new PDO(
        sprintf('mysql:host=%s;port=%d;dbname=%s;charset=%s', 
            $db['host'], $db['port'], $db['name'], $db['charset']),
        $db['user'],
        $db['pass'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    echo "✓ Connection successful!\n";
    
    $result = $pdo->query("SELECT VERSION()");
    $version = $result->fetchColumn();
    echo "MariaDB Version: $version\n";
    
} catch (Exception $e) {
    echo "✗ Connection failed!\n";
    echo "Error: " . $e->getMessage() . "\n";
    echo "\nAttempting to check what's in the database directory...\n";
    exec('dir C:\\xampp\\mysql\\data', $output);
    foreach ($output as $line) {
        echo htmlspecialchars($line) . "\n";
    }
}

echo "</pre>";
?>
