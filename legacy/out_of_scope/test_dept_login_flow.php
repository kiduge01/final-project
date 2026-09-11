<?php
/**
 * Department Login Test - Simulate the exact flow
 */

require 'app/config.php';
require 'app/core/Database.php';
require 'app/core/Auth.php';

use App\Core\Auth;
use App\Core\Database;

$config = require 'app/config.php';
Auth::boot($config);  // Initialize session
$pdo = Database::connection($config);

echo "<h2>Department Login Debug Test</h2>";
echo "<p><strong>Current Session ID:</strong> " . session_id() . "</p>";
echo "<p><strong>Session Name:</strong> " . session_name() . "</p>";
echo "<p><strong>Session Variables:</strong> <pre>" . print_r($_SESSION, true) . "</pre></p>";

$email = 'ibada@kanisa.local';
$password = 'password';

echo "<h3>Step 1: Find Department</h3>";
$deptStmt = $pdo->prepare(
    'SELECT id, name, head_email, head_password_hash
     FROM departments
     WHERE head_email = :email AND is_active = 1 LIMIT 1'
);
$deptStmt->execute([':email' => $email]);
$dept = $deptStmt->fetch(PDO::FETCH_ASSOC);

if (!$dept) {
    echo "<p style='color:red'>Department NOT found!</p>";
    exit;
}

echo "<p>Department found:</p>";
echo "<ul>";
echo "<li>ID: {$dept['id']}</li>";
echo "<li>Name: {$dept['name']}</li>";
echo "<li>Email: {$dept['head_email']}</li>";
echo "<li>Password Hash: " . substr($dept['head_password_hash'], 0, 20) . "...</li>";
echo "</ul>";

echo "<h3>Step 2: Verify Password</h3>";
$verified = password_verify($password, $dept['head_password_hash']);
echo "<p>Password verification: " . ($verified ? '<strong style="color:green">PASS</strong>' : '<strong style="color:red">FAIL</strong>') . "</p>";

if ($verified) {
    echo "<h3>Step 3: Set Session</h3>";
    session_regenerate_id(true);
    $_SESSION['department_id']   = $dept['id'];
    $_SESSION['department_name'] = $dept['name'];
    $_SESSION['head_name']       = $dept['name'];
    $_SESSION['head_email']      = $dept['head_email'];
    $_SESSION['user_role']       = 'department_head';
    
    echo "<p>Session set. Session ID: " . session_id() . "</p>";
    echo "<p>Session variables:</p>";
    echo "<pre>";
    echo "department_id: " . $_SESSION['department_id'] . "\n";
    echo "department_name: " . $_SESSION['department_name'] . "\n";
    echo "head_name: " . $_SESSION['head_name'] . "\n";
    echo "head_email: " . $_SESSION['head_email'] . "\n";
    echo "user_role: " . $_SESSION['user_role'] . "\n";
    echo "</pre>";
    
    echo "<h3>Step 4: Simulate Redirect</h3>";
    echo "<p>Would redirect to: /htdocs/department/dashboard.php</p>";
    echo "<p><a href='/htdocs/department/dashboard.php'>Click here to go to department dashboard</a></p>";
    echo "<p>Or go to: <a href='/login'>back to login</a></p>";
} else {
    echo "<p style='color:red'><strong>Password verification failed!</strong></p>";
}

?>
