<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$config = require __DIR__ . '/app/config.php';
$db = $config['db'];

$pdo = new PDO(
    sprintf('mysql:host=%s;port=%d;dbname=%s;charset=%s', $db['host'], $db['port'], $db['name'], $db['charset']),
    $db['user'],
    $db['pass']
);

// Disable foreign key checks temporarily
$pdo->exec("SET FOREIGN_KEY_CHECKS=0");

try {
    // Add the zone_id column if it doesn't exist
    $pdo->exec("ALTER TABLE members ADD COLUMN zone_id BIGINT UNSIGNED NULL AFTER region");
    echo "✅ Added zone_id column to members table\n";
} catch (PDOException $e) {
    if (str_contains($e->getMessage(), 'Duplicate column')) {
        echo "⚠️ zone_id column already exists\n";
    } else {
        echo "❌ Error adding column: " . $e->getMessage() . "\n";
    }
}

try {
    // Add the index
    $pdo->exec("ALTER TABLE members ADD INDEX idx_members_zone (zone_id)");
    echo "✅ Added index on zone_id\n";
} catch (PDOException $e) {
    if (str_contains($e->getMessage(), 'Duplicate key')) {
        echo "⚠️ Index already exists\n";
    } else {
        echo "❌ Error adding index: " . $e->getMessage() . "\n";
    }
}

try {
    // Add the foreign key constraint
    $pdo->exec("ALTER TABLE members ADD CONSTRAINT fk_members_zone FOREIGN KEY (zone_id) REFERENCES zones(id) ON UPDATE CASCADE ON DELETE SET NULL");
    echo "✅ Added foreign key constraint\n";
} catch (PDOException $e) {
    if (str_contains($e->getMessage(), 'Duplicate constraint') || str_contains($e->getMessage(), 'already')) {
        echo "⚠️ Constraint already exists\n";
    } else {
        echo "❌ Error adding constraint: " . $e->getMessage() . "\n";
    }
}

// Re-enable foreign key checks
$pdo->exec("SET FOREIGN_KEY_CHECKS=1");

echo "\n✅ Migration complete!\n";
