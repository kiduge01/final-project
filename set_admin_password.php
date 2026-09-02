<?php
$pdo = new PDO('mysql:host=localhost;dbname=church_cms2', 'root', '');

$password = "password";
$hash = password_hash($password, PASSWORD_BCRYPT);

// Update all admin users
$stmt = $pdo->prepare('UPDATE users SET password_hash = ? WHERE role_id IN (1, 2, 3)');
$result = $stmt->execute([$hash]);

echo "Updated " . $stmt->rowCount() . " user(s) with password 'password' and hash:\n";
echo $hash . "\n\n";

// Verify
$stmt = $pdo->prepare('SELECT id, full_name, email FROM users WHERE role_id IN (1, 2, 3)');
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "Users updated:\n";
foreach ($users as $user) {
    echo "- {$user['full_name']} ({$user['email']})\n";
}
