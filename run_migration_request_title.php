<?php
$dsn = 'mysql:host=localhost;dbname=church_cms';
$pdo = new PDO($dsn, 'root', '');
$sql = file_get_contents('database/migrations/2026_05_30_001_add_request_title_to_budgets.sql');
try {
    $pdo->exec($sql);
    echo "Migration applied successfully\n";
} catch (Exception $e) {
    echo "Migration error: " . $e->getMessage() . "\n";
}
?>
