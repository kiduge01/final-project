<?php

echo "<h1>Simple Database Diagnostic</h1>";

$config = require __DIR__ . '/app/config.php';

echo "<h2>Database Config</h2>";
echo "<pre>";
print_r($config['db']);
echo "</pre>";

try {
    $pdo = new PDO(
        sprintf('mysql:host=%s;port=%d;dbname=%s;charset=%s',
            $config['db']['host'],
            $config['db']['port'],
            $config['db']['name'],
            $config['db']['charset']
        ),
        $config['db']['user'],
        $config['db']['pass']
    );
    
    echo "<h2 style='color:green'>✓ Database Connection: SUCCESS</h2>";
    
    // Check departments table
    $stmt = $pdo->query("SELECT * FROM departments LIMIT 1");
    $dept = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($dept) {
        echo "<h3>Sample Department:</h3>";
        echo "<pre>";
        print_r($dept);
        echo "</pre>";
        
        // Check if head_email column exists
        $cols = $pdo->query("SHOW COLUMNS FROM departments WHERE Field = 'head_email'")->fetchAll();
        if ($cols) {
            echo "<h3 style='color:green'>✓ head_email column exists</h3>";
        } else {
            echo "<h3 style='color:red'>✗ head_email column missing</h3>";
        }
    } else {
        echo "<h3 style='color:orange'>⚠ No departments found</h3>";
    }
    
} catch (\Exception $e) {
    echo "<h2 style='color:red'>✗ Database Connection: FAILED</h2>";
    echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
}
