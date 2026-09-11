<?php
// Fix for MySQL root user access denied error

$config = require __DIR__ . '/app/config.php';

echo "<h2>MySQL Root User Fix</h2>";
echo "<pre>";

try {
    // First, try to connect with standard method
    $db = $config['db'];
    echo "Attempting connection to: {$db['host']}:{$db['port']}\n";
    echo "User: {$db['user']}\n";
    echo "Pass: (empty)\n\n";
    
    $pdo = new PDO(
        sprintf('mysql:host=%s;port=%d;charset=%s', 
            $db['host'], $db['port'], $db['charset']),
        $db['user'],
        $db['pass'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    echo "✓ Connected to MySQL Server\n\n";
    
    // Run the fix SQL  
    $fixSQL = <<<'SQL'
FLUSH PRIVILEGES;
DELETE FROM mysql.user WHERE User='root';
CREATE USER 'root'@'localhost' IDENTIFIED BY '';
CREATE USER 'root'@'127.0.0.1' IDENTIFIED BY '';
CREATE USER 'root'@'::1' IDENTIFIED BY '';
GRANT ALL PRIVILEGES ON *.* TO 'root'@'localhost' WITH GRANT OPTION;
GRANT ALL PRIVILEGES ON *.* TO 'root'@'127.0.0.1' WITH GRANT OPTION;
GRANT ALL PRIVILEGES ON *.* TO 'root'@'::1' WITH GRANT OPTION;
FLUSH PRIVILEGES;
SQL;

    $statements = array_filter(array_map('trim', explode(';', $fixSQL)), function($s) { 
        return !empty($s); 
    });
    
    foreach ($statements as $stmt) {
        echo "Executing: $stmt\n";
        $pdo->exec($stmt);
        echo "✓ Success\n\n";
    }
    
    echo "✓✓✓ MySQL root user has been fixed! ✓✓✓\n";
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo "\nThis usually means MySQL is not running or is not accepting connections.\n";
    echo "Make sure the MariaDB service is started.\n";
}

echo "</pre>";
?>
