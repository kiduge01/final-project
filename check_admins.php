<?php
$pdo = new PDO('mysql:host=localhost;dbname=church_cms2', 'root', '');
$stmt = $pdo->prepare('SHOW COLUMNS FROM users');
$stmt->execute();
$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "<h3>Users table columns:</h3><pre>";
print_r($columns);
echo "</pre>";

$stmt = $pdo->prepare('SELECT * FROM users LIMIT 5');
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "<h3>Sample users:</h3><pre>";
print_r($users);
echo "</pre>";
