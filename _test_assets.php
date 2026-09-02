<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3306;dbname=church_cms', 'root', 'root');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

echo "=== assets table ===" . PHP_EOL;
$cols = $pdo->query('SHOW COLUMNS FROM assets')->fetchAll();
foreach ($cols as $c) echo $c['Field'] . ' ' . $c['Type'] . ' ' . $c['Null'] . PHP_EOL;

echo PHP_EOL . "=== asset_assignments exists? ===" . PHP_EOL;
try {
    $pdo->query('SELECT 1 FROM asset_assignments LIMIT 1');
    echo "YES" . PHP_EOL;
    $cols = $pdo->query('SHOW COLUMNS FROM asset_assignments')->fetchAll();
    foreach ($cols as $c) echo $c['Field'] . ' ' . $c['Type'] . ' ' . $c['Null'] . PHP_EOL;
} catch (Exception $e) {
    echo "NO: " . $e->getMessage() . PHP_EOL;
}

echo PHP_EOL . "=== departments ===" . PHP_EOL;
$rows = $pdo->query('SELECT id, name FROM departments LIMIT 20')->fetchAll();
foreach ($rows as $r) echo $r['id'] . ' ' . $r['name'] . PHP_EOL;

echo PHP_EOL . "=== assets count ===" . PHP_EOL;
echo $pdo->query('SELECT COUNT(*) FROM assets')->fetchColumn() . PHP_EOL;

echo PHP_EOL . "=== assets sample ===" . PHP_EOL;
$rows = $pdo->query('SELECT id, asset_tag, name, category, condition_status, current_location FROM assets LIMIT 5')->fetchAll();
foreach ($rows as $r) echo $r['id'] . ' | ' . $r['asset_tag'] . ' | ' . $r['name'] . ' | ' . $r['category'] . ' | ' . $r['condition_status'] . ' | ' . $r['current_location'] . PHP_EOL;

echo PHP_EOL . "=== asset_assignments data ===" . PHP_EOL;
try {
    $rows = $pdo->query('SELECT * FROM asset_assignments LIMIT 10')->fetchAll();
    echo count($rows) . " rows" . PHP_EOL;
    foreach ($rows as $r) print_r($r);
} catch (Exception $e) {
    echo "N/A" . PHP_EOL;
}
