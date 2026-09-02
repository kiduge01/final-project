<?php
/**
 * Quick Migration Runner for Zone Column
 */

$config = require __DIR__ . '/app/config.php';
$db = $config['db'];

try {
    $pdo = new PDO(
        sprintf('mysql:host=%s;port=%d;dbname=%s;charset=%s', $db['host'], $db['port'], $db['name'], $db['charset']),
        $db['user'],
        $db['pass'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    echo "<h1>Running Zone Migration</h1>";
    echo "<hr>";
    
    $sql = file_get_contents(__DIR__ . '/database/migrations/2026_05_14_002_add_zone_to_members.sql');
    $statements = array_filter(
        array_map('trim', preg_split('/;[\s\n]+/', $sql)),
        fn($s) => !empty($s) && !str_starts_with(trim($s), '--')
    );
    
    foreach ($statements as $statement) {
        if (trim($statement)) {
            $pdo->exec($statement);
            echo "<p><strong>✓ Executed:</strong> " . substr($statement, 0, 60) . "...</p>";
        }
    }
    
    echo "<hr>";
    echo "<p style='color: green; font-weight: bold;'>✓ Migration completed successfully!</p>";
    echo "<p>The zone_id column has been added to the members table.</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red; font-weight: bold;'>✗ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}
?>
