<?php
/**
 * EMERGENCY FIX: Run procurement schema migration
 */

// Load config
$configFile = __DIR__ . '/app/config.php';
if (!file_exists($configFile)) {
    die("❌ Config file not found: $configFile\n");
}
$config = require_once $configFile;

if (!$config || !isset($config['db'])) {
    die("❌ Invalid config file\n");
}

$db = $config['db'];

try {
    $pdo = new PDO(
        'mysql:host=' . $db['host'] . ';dbname=' . $db['name'] . ';charset=' . $db['charset'],
        $db['user'],
        $db['pass'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 30
        ]
    );

    echo "🔧 Starting Procurement Schema Migration...\n\n";

    // Read the migration file
    $migrationFile = __DIR__ . '/database/migrations/2026_04_14_005_procurement_full_schema.sql';
    if (!file_exists($migrationFile)) {
        die("❌ Migration file not found: $migrationFile\n");
    }

    $sql = file_get_contents($migrationFile);
    
    // Split and execute each statement
    $statements = array_filter(
        array_map('trim', explode(';', $sql)),
        function($stmt) { return !empty($stmt) && !preg_match('/^--/', $stmt); }
    );

    foreach ($statements as $i => $stmt) {
        try {
            echo "Executing statement " . ($i + 1) . " of " . count($statements) . "...\n";
            $pdo->exec($stmt);
            echo "  ✓ Success\n";
        } catch (Exception $e) {
            echo "  ⚠ Warning: " . $e->getMessage() . "\n";
        }
    }

    echo "\n✅ Migration completed!\n";
    echo "\nNow checking if procurement columns exist...\n";

    // Verify columns exist
    $check = $pdo->query("SHOW COLUMNS FROM purchase_requests WHERE Field IN ('budget_id', 'vendor_name', 'approved_by', 'approved_at', 'rejection_reason', 'completed_at')");
    $columns = $check->fetchAll(PDO::FETCH_COLUMN, 0);
    
    echo "\nColumns found: " . count($columns) . " of 6 expected\n";
    if (count($columns) === 6) {
        echo "✅ All procurement columns exist! Ready to go.\n";
    } else {
        echo "⚠ Some columns missing. Check migration output above.\n";
    }

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
