<?php
$config = require __DIR__ . '/app/config.php';
$db = $config['db'];

try {
    $pdo = new PDO(
        sprintf('mysql:host=%s;port=%d;dbname=%s;charset=%s', $db['host'], $db['port'], $db['name'], $db['charset']),
        $db['user'],
        $db['pass']
    );
    
    // Check if zones table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'zones'");
    $zonesExists = $stmt->rowCount() > 0;
    
    echo "<h3>Zones Table: " . ($zonesExists ? "✅ EXISTS" : "❌ MISSING") . "</h3>";
    
    if ($zonesExists) {
        echo "<h3>Checking Zone Table Structure:</h3>";
        $stmt = $pdo->query("SHOW COLUMNS FROM zones");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>Field</th><th>Type</th></tr>";
        foreach ($columns as $col) {
            echo "<tr><td>" . htmlspecialchars($col['Field']) . "</td><td>" . htmlspecialchars($col['Type']) . "</td></tr>";
        }
        echo "</table>";
        
        // Now try to add zone_id to members
        echo "<h3>Attempting to add zone_id to members table...</h3>";
        try {
            $pdo->exec("ALTER TABLE members
ADD COLUMN zone_id BIGINT UNSIGNED NULL AFTER region,
ADD CONSTRAINT fk_members_zone FOREIGN KEY (zone_id) REFERENCES zones(id)
    ON UPDATE CASCADE ON DELETE SET NULL,
ADD INDEX idx_members_zone (zone_id)");
            
            echo "<p style='color:green;'>✅ Successfully added zone_id column to members!</p>";
        } catch (Exception $e) {
            echo "<p style='color:red;'>❌ Error adding zone_id: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . htmlspecialchars($e->getMessage());
}
