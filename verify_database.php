<?php
/**
 * Database Verification Script
 * Check which tables and migrations are present
 */

echo "<h1>Database Verification</h1>";
echo "<hr>";

$config = require __DIR__ . '/app/config.php';
$db = $config['db'];

try {
    $pdo = new PDO(
        sprintf('mysql:host=%s;port=%d;dbname=%s;charset=%s', $db['host'], $db['port'], $db['name'], $db['charset']),
        $db['user'],
        $db['pass'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "<p><strong style='color:green'>✓ Connected to database: " . htmlspecialchars($db['name']) . "</strong></p>";
} catch (PDOException $e) {
    echo "<p><strong style='color:red'>✗ Connection failed:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    exit;
}

// Check critical tables
$criticalTables = [
    'users',
    'roles',
    'departments',
    'church_settings',
    'finance_entries',
    'events',
];

echo "<h2>Critical Tables Status</h2>";
echo "<table border='1' style='border-collapse:collapse; margin:10px 0;'>";
echo "<tr style='background:#f0f0f0;'><th style='padding:8px;'>Table Name</th><th style='padding:8px;'>Status</th></tr>";

foreach ($criticalTables as $table) {
    try {
        $result = $pdo->query("SELECT 1 FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '{$table}' LIMIT 1");
        $exists = $result->fetchColumn() ? true : false;
        
        if ($exists) {
            // Get column count
            $cols = $pdo->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '{$table}'");
            $colCount = $cols->rowCount();
            echo "<tr><td style='padding:8px;'><strong>{$table}</strong></td><td style='padding:8px; color:green;'>✓ EXISTS (" . $colCount . " columns)</td></tr>";
        } else {
            echo "<tr><td style='padding:8px;'><strong>{$table}</strong></td><td style='padding:8px; color:red;'>✗ MISSING</td></tr>";
        }
    } catch (Exception $e) {
        echo "<tr><td style='padding:8px;'><strong>{$table}</strong></td><td style='padding:8px; color:orange;'>? ERROR: " . htmlspecialchars($e->getMessage()) . "</td></tr>";
    }
}
echo "</table>";

// Check church_settings columns
echo "<h2>Church Settings Table Details</h2>";
try {
    $result = $pdo->query("SELECT 1 FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'church_settings' LIMIT 1");
    if ($result->fetchColumn()) {
        $cols = $pdo->query("SELECT COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'church_settings' ORDER BY ORDINAL_POSITION");
        echo "<table border='1' style='border-collapse:collapse; margin:10px 0;'>";
        echo "<tr style='background:#f0f0f0;'><th style='padding:8px;'>Column</th><th style='padding:8px;'>Type</th><th style='padding:8px;'>Nullable</th></tr>";
        while ($col = $cols->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr><td style='padding:8px;'>" . htmlspecialchars($col['COLUMN_NAME']) . "</td><td style='padding:8px;'>" . htmlspecialchars($col['COLUMN_TYPE']) . "</td><td style='padding:8px;'>" . htmlspecialchars($col['IS_NULLABLE']) . "</td></tr>";
        }
        echo "</table>";
        
        // Try to query church_settings
        echo "<h3>Church Settings Data</h3>";
        try {
            $data = $pdo->query("SELECT * FROM church_settings LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
            if ($data) {
                echo "<table border='1' style='border-collapse:collapse; margin:10px 0;'>";
                echo "<tr style='background:#f0f0f0;'>";
                foreach (array_keys($data[0]) as $key) {
                    echo "<th style='padding:8px;'>" . htmlspecialchars($key) . "</th>";
                }
                echo "</tr>";
                foreach ($data as $row) {
                    echo "<tr>";
                    foreach ($row as $val) {
                        echo "<td style='padding:8px; max-width:300px; overflow:hidden;'>" . htmlspecialchars(substr($val, 0, 100)) . "</td>";
                    }
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "<p style='color:orange;'>No data in church_settings table</p>";
            }
        } catch (Exception $e) {
            echo "<p style='color:red;'>Error querying church_settings: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
    } else {
        echo "<p style='color:red;'>church_settings table does not exist</p>";
    }
} catch (Exception $e) {
    echo "<p style='color:red;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<hr>";
echo "<p><a href='run_migrations.php'>Run Migrations</a> | <a href='public/'>Dashboard</a></p>";
?>
