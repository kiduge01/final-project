<?php
$config = require __DIR__ . '/app/config.php';
$db = $config['db'];
$pdo = new PDO(sprintf('mysql:host=%s;port=%d;dbname=%s;charset=%s',$db['host'],$db['port'],$db['name'],$db['charset']),$db['user'],$db['pass'],[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);

// Departments table columns
echo "<b>departments:</b> ";
$cols = $pdo->query("SHOW COLUMNS FROM departments")->fetchAll(PDO::FETCH_COLUMN);
echo implode(', ',$cols) . "<br><br>";

// Sample departments data
echo "<b>departments data:</b><br>";
$rows = $pdo->query("SELECT id, name, is_active FROM departments LIMIT 10")->fetchAll();
foreach($rows as $r) echo "id={$r['id']} name={$r['name']} active={$r['is_active']}<br>";
