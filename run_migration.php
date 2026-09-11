<?php
declare(strict_types=1);

$config = require __DIR__ . '/app/config.php';

try {
    $pdo = new PDO(
        'mysql:host=' . $config['db']['host'] . ';port=' . $config['db']['port'] . ';charset=utf8mb4',
        $config['db']['user'],
        $config['db']['pass']
    );
    
    $pdo->exec('USE ' . $config['db']['name']);
    
    $sql = file_get_contents(__DIR__ . '/database/migrations/2026_06_01_001_create_sadaka_module.sql');
    
    // Split by semicolon and execute each statement
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    foreach ($statements as $statement) {
        if (!empty($statement)) {
            $pdo->exec($statement);
        }
    }
    
    echo '<h1 style="color: green;">✓ Sadaka module tables created successfully!</h1>';
    echo '<p>The following tables have been created:</p>';
    echo '<ul>';
    echo '<li>sadaka_categories</li>';
    echo '<li>sadaka_entries</li>';
    echo '<li>sadaka_uploads</li>';
    echo '</ul>';
    echo '<p><a href="/CMAIN/public/sadaka">Go to Sadaka Module →</a></p>';
    
} catch (Exception $e) {
    echo '<h1 style="color: red;">✗ Error creating tables</h1>';
    echo '<pre>' . htmlspecialchars($e->getMessage()) . '</pre>';
}
