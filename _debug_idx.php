<?php
$config = require __DIR__ . '/app/config.php';
require_once __DIR__ . '/app/core/Database.php';
$pdo = \App\Core\Database::connection($config);
echo 'members indexes:' . PHP_EOL;
$r = $pdo->query('SHOW INDEX FROM members')->fetchAll(PDO::FETCH_ASSOC);
foreach($r as $i) echo $i['Key_name'] . ' - ' . $i['Column_name'] . PHP_EOL;
echo PHP_EOL . 'department_members indexes:' . PHP_EOL;
$r2 = $pdo->query('SHOW INDEX FROM department_members')->fetchAll(PDO::FETCH_ASSOC);
foreach($r2 as $i) echo $i['Key_name'] . ' - ' . $i['Column_name'] . PHP_EOL;
echo PHP_EOL . 'duplicate check (same phone):' . PHP_EOL;
$dups = $pdo->query('SELECT phone, COUNT(*) as cnt FROM members GROUP BY phone HAVING cnt > 1 LIMIT 5')->fetchAll(PDO::FETCH_ASSOC);
foreach($dups as $d) echo $d['phone'] . ' x' . $d['cnt'] . PHP_EOL;
