<?php
require 'app/config.php';
require 'app/core/Database.php';

$config = require 'app/config.php';
$pdo = \App\Core\Database::connection($config);

// Set new passwords for departments with credentials
$testPassword = 'password'; // Common test password
$hashedPassword = password_hash($testPassword, PASSWORD_DEFAULT);

// Update Ujenzi department
$stmt = $pdo->prepare('UPDATE departments SET head_password_hash = ? WHERE id = 3');
$stmt->execute([$hashedPassword]);
echo "Updated Ujenzi department password to: <strong>password</strong><br>";

// Update Media Team department  
$stmt = $pdo->prepare('UPDATE departments SET head_password_hash = ? WHERE id = 7');
$stmt->execute([$hashedPassword]);
echo "Updated Media Team department password to: <strong>password</strong><br>";

// Now also set credentials for other departments so they can login
$departments = [1, 2, 4, 5, 6];
foreach ($departments as $deptId) {
    $stmt = $pdo->prepare('SELECT name FROM departments WHERE id = ?');
    $stmt->execute([$deptId]);
    $dept = $stmt->fetch();
    
    $email = strtolower(str_replace(' ', '_', $dept['name'])) . '@kanisa.local';
    $stmt = $pdo->prepare('UPDATE departments SET head_email = ?, head_password_hash = ? WHERE id = ?');
    $stmt->execute([$email, $hashedPassword, $deptId]);
    echo "Set credentials for {$dept['name']}: <strong>$email / password</strong><br>";
}

echo "<h3>All Department Login Credentials:</h3>";
$stmt = $pdo->query('SELECT id, name, head_email FROM departments WHERE is_active = 1 ORDER BY id');
$depts = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "<table border='1' style='margin-top:10px'>";
echo "<tr><th>Department</th><th>Email</th><th>Password</th></tr>";
foreach ($depts as $d) {
    echo "<tr><td>{$d['name']}</td><td>{$d['head_email']}</td><td>password</td></tr>";
}
echo "</table>";
?>
