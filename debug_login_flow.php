<?php
/**
 * Debug Department Login - Trace the exact login process
 */

// Simulate POST request
$_POST['email'] = 'vijana@kanisa.local';
$_POST['password'] = 'password';
$_POST['_csrf_token'] = 'dummy_token';  // Will fail CSRF but let's see what happens

require 'app/config.php';
require 'app/core/Database.php';
require 'app/core/Auth.php';
require 'app/core/Response.php';

use App\Core\Auth;
use App\Core\Database;
use App\Core\Response;

$config = require 'app/config.php';
Auth::boot($config);
$pdo = Database::connection($config);

echo "<h2>Debug: Department Login Simulation</h2>";
echo "<p>POST data:</p>";
echo "<pre>";
print_r($_POST);
echo "</pre>";

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

echo "<h3>1. Check CSRF Token</h3>";
$csrfToken = $_POST['_csrf_token'] ?? '';
$csrfValid = Auth::validateCsrfToken($csrfToken);
echo "<p>CSRF valid: " . ($csrfValid ? 'YES' : 'NO (expected, using dummy token)') . "</p>";

// Skip CSRF for this test
echo "<h3>2. Check Brute Force</h3>";
$bruteCheck = Auth::checkLoginAllowed($pdo, $email);
echo "<p>Login allowed: " . ($bruteCheck['allowed'] ? 'YES' : 'NO') . "</p>";

if (!$bruteCheck['allowed']) {
    echo "<p>Retry in {$bruteCheck['retry_after']} seconds</p>";
} else {
    echo "<h3>3. Search in Users Table</h3>";
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = :email AND is_active = 1 LIMIT 1');
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch();
    echo "<p>Found in users table: " . ($user ? 'YES' : 'NO') . "</p>";
    
    if (!$user) {
        echo "<h3>4. Search in Departments Table</h3>";
        $deptStmt = $pdo->prepare(
            'SELECT id, name, head_email, head_password_hash
             FROM departments
             WHERE head_email = :email AND is_active = 1 LIMIT 1'
        );
        $deptStmt->execute([':email' => $email]);
        $dept = $deptStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($dept) {
            echo "<p>Found in departments table: YES</p>";
            echo "<ul>";
            echo "<li>ID: {$dept['id']}</li>";
            echo "<li>Name: {$dept['name']}</li>";
            echo "</ul>";
            
            echo "<h3>5. Verify Password</h3>";
            $verified = password_verify($password, $dept['head_password_hash']);
            echo "<p>Password verified: " . ($verified ? '<strong style="color:green">YES</strong>' : '<strong style="color:red">NO</strong>') . "</p>";
            
            if ($verified) {
                echo "<h3>6. Login Would Succeed</h3>";
                echo "<p>Would set session variables:</p>";
                echo "<ul>";
                echo "<li>department_id: {$dept['id']}</li>";
                echo "<li>department_name: {$dept['name']}</li>";
                echo "<li>head_name: {$dept['name']}</li>";
                echo "<li>head_email: {$dept['head_email']}</li>";
                echo "<li>user_role: department_head</li>";
                echo "</ul>";
                echo "<p>Would redirect to: /htdocs/department/dashboard.php</p>";
            } else {
                echo "<h3 style='color:red'>6. Login Would Fail - Invalid Password</h3>";
            }
        } else {
            echo "<p style='color:red'>Found in departments table: <strong>NO</strong></p>";
            echo "<p>Email not found in either users or departments tables</p>";
        }
    } else {
        echo "<p style='color:blue'>Would check admin user password...</p>";
    }
}
?>
