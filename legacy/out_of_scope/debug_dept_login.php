<?php
/**
 * Department Login Debug Script
 * Traces through the login process step by step
 */

require 'app/config.php';
require 'app/core/Database.php';
require 'app/core/Auth.php';

$config = require 'app/config.php';
$pdo = \App\Core\Database::connection($config);

// Simulate a department login attempt
$testEmail = 'ujenzi@kanisa.local';  // Department with credentials
$testPassword = 'test'; // Try test password

echo "<h2>Department Login Debug</h2>";
echo "<p>Testing login for: $testEmail</p>";

// Step 1: Check if user exists in users table
echo "<h3>Step 1: Check Users Table</h3>";
$stmt = $pdo->prepare('SELECT id FROM users WHERE email = :email');
$stmt->execute([':email' => $testEmail]);
$user = $stmt->fetch();
echo $user ? "Found in users table: YES" : "Found in users table: NO";
echo "<br>";

// Step 2: Check if department exists in departments table
echo "<h3>Step 2: Check Departments Table</h3>";
$stmt = $pdo->prepare('SELECT id, name, head_email, head_password_hash FROM departments WHERE head_email = :email AND is_active = 1');
$stmt->execute([':email' => $testEmail]);
$dept = $stmt->fetch(PDO::FETCH_ASSOC);

if ($dept) {
    echo "Found in departments table: YES<br>";
    echo "Department ID: {$dept['id']}<br>";
    echo "Department Name: {$dept['name']}<br>";
    echo "Head Email: {$dept['head_email']}<br>";
    echo "Has Password Hash: " . (!empty($dept['head_password_hash']) ? 'YES' : 'NO') . "<br>";
    
    if (!empty($dept['head_password_hash'])) {
        echo "<h3>Step 3: Test Password Verification</h3>";
        $verified = password_verify($testPassword, $dept['head_password_hash']);
        echo "Password '$testPassword' verification: " . ($verified ? 'PASS' : 'FAIL') . "<br>";
        
        if (!$verified) {
            echo "<p style='color:red'><strong>Issue Found:</strong> Password doesn't match!</p>";
        }
    }
} else {
    echo "Found in departments table: NO<br>";
    echo "<p style='color:red'><strong>Issue Found:</strong> Department credentials not found in database!</p>";
}

echo "<h3>Departments with Credentials</h3>";
$stmt = $pdo->query('SELECT id, name, head_email, head_password_hash FROM departments WHERE head_email IS NOT NULL AND head_password_hash IS NOT NULL AND is_active = 1');
$validDepts = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "<ul>";
foreach ($validDepts as $d) {
    echo "<li>{$d['name']} - {$d['head_email']}</li>";
}
echo "</ul>";
?>
