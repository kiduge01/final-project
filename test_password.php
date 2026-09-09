<?php
$pdo = new PDO('mysql:host=localhost;dbname=church_cms2', 'root', '');

// Get admin user
$stmt = $pdo->prepare('SELECT id, full_name, email, password_hash FROM users WHERE email = ?');
$stmt->execute(['admin@kanisa.local']);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo "User not found!";
} else {
    echo "User found: " . htmlspecialchars($user['full_name']) . "\n";
    echo "Email: " . htmlspecialchars($user['email']) . "\n";
    echo "Hash: " . $user['password_hash'] . "\n\n";
    
    $testPassword = "password";
    $verified = password_verify($testPassword, $user['password_hash']);
    echo "Password 'password' verified: " . ($verified ? "YES ✓" : "NO ✗") . "\n";
    
    // Test other common passwords
    $commonPasswords = ["Password", "PASSWORD", "123456", "admin123"];
    echo "\nTesting other passwords:\n";
    foreach ($commonPasswords as $pwd) {
        $v = password_verify($pwd, $user['password_hash']);
        echo "  '$pwd': " . ($v ? "✓ MATCHES" : "✗") . "\n";
    }
}
