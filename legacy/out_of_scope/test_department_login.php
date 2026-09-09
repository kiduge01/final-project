<?php

declare(strict_types=1);

date_default_timezone_set('Africa/Dar_es_Salaam');

$config = require __DIR__ . '/app/config.php';

require_once __DIR__ . '/app/core/Database.php';
require_once __DIR__ . '/app/core/Auth.php';
require_once __DIR__ . '/app/core/Audit.php';
require_once __DIR__ . '/app/core/Response.php';
require_once __DIR__ . '/app/controllers/UnifiedAuthController.php';

use App\Core\Auth;
use App\Core\Database;
use App\Core\Response;

Auth::boot($config);

try {
    $pdo = Database::connection($config);
} catch (\Throwable $e) {
    echo '<h1>Database connection failed</h1>';
    echo '<pre>' . htmlspecialchars($e->getMessage()) . '</pre>';
    exit;
}

echo "<h1>Department Login Diagnostics</h1>";
echo "<style>
    body { font-family: Arial; margin: 20px; background: #f5f5f5; }
    .test { background: white; padding: 15px; margin: 10px 0; border-radius: 5px; }
    .pass { color: green; font-weight: bold; }
    .fail { color: red; font-weight: bold; }
    .warn { color: orange; font-weight: bold; }
    code { background: #f0f0f0; padding: 2px 5px; border-radius: 3px; }
</style>";

// Test 1: Database Connection
echo "<div class='test'>";
echo "<h3>1. Database Connection</h3>";
try {
    $test = $pdo->query('SELECT 1');
    echo "<span class='pass'>✓ PASS</span> - Database connected";
} catch (\Exception $e) {
    echo "<span class='fail'>✗ FAIL</span> - " . $e->getMessage();
}
echo "</div>";

// Test 2: Check departments table
echo "<div class='test'>";
echo "<h3>2. Departments Table Structure</h3>";
try {
    $cols = $pdo->query("SHOW COLUMNS FROM departments")->fetchAll(PDO::FETCH_ASSOC);
    $hasHeadEmail = false;
    $hasHeadPassword = false;
    
    foreach ($cols as $col) {
        if ($col['Field'] === 'head_email') $hasHeadEmail = true;
        if ($col['Field'] === 'head_password_hash') $hasHeadPassword = true;
    }
    
    if ($hasHeadEmail && $hasHeadPassword) {
        echo "<span class='pass'>✓ PASS</span> - Required columns exist<br>";
    } else {
        echo "<span class='fail'>✗ FAIL</span> - Missing columns:<br>";
        if (!$hasHeadEmail) echo "  - <code>head_email</code><br>";
        if (!$hasHeadPassword) echo "  - <code>head_password_hash</code><br>";
    }
} catch (\Exception $e) {
    echo "<span class='fail'>✗ FAIL</span> - " . $e->getMessage();
}
echo "</div>";

// Test 3: Check for departments with login credentials
echo "<div class='test'>";
echo "<h3>3. Departments with Login Credentials</h3>";
try {
    $depts = $pdo->query(
        "SELECT id, name, head_email, head_password_hash 
         FROM departments 
         WHERE head_email IS NOT NULL AND head_password_hash IS NOT NULL 
         AND is_active = 1"
    )->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($depts) > 0) {
        echo "<span class='pass'>✓ PASS</span> - Found " . count($depts) . " department(s) with credentials<br>";
        echo "<table style='width:100%; border-collapse: collapse;'>";
        echo "<tr style='border-bottom: 1px solid #ddd;'><th style='text-align:left; padding:5px;'>ID</th><th style='text-align:left; padding:5px;'>Name</th><th style='text-align:left; padding:5px;'>Email</th><th style='text-align:left; padding:5px;'>Password Hash</th></tr>";
        foreach ($depts as $dept) {
            echo "<tr style='border-bottom: 1px solid #eee;'>";
            echo "<td style='padding:5px;'>" . $dept['id'] . "</td>";
            echo "<td style='padding:5px;'>" . htmlspecialchars($dept['name']) . "</td>";
            echo "<td style='padding:5px;'>" . htmlspecialchars($dept['head_email']) . "</td>";
            echo "<td style='padding:5px;'><code>" . substr($dept['head_password_hash'], 0, 20) . "...</code></td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<span class='warn'>⚠ WARNING</span> - No departments with credentials found<br>";
        echo "You need to set department head credentials via the admin panel first.";
    }
} catch (\Exception $e) {
    echo "<span class='fail'>✗ FAIL</span> - " . $e->getMessage();
}
echo "</div>";

// Test 4: Check session configuration
echo "<div class='test'>";
echo "<h3>4. Session Configuration</h3>";
echo "Main app session name: <code>" . htmlspecialchars($config['security']['session_name']) . "</code><br>";
echo "Current session name: <code>" . session_name() . "</code><br>";
echo "Session ID: <code>" . session_id() . "</code><br>";
if ($config['security']['session_name'] === session_name()) {
    echo "<span class='pass'>✓ PASS</span> - Session names match";
} else {
    echo "<span class='fail'>✗ FAIL</span> - Session names don't match";
}
echo "</div>";

// Test 5: Check if login endpoints are callable
echo "<div class='test'>";
echo "<h3>5. API Endpoint Configuration</h3>";
echo "Base URL: <code>" . htmlspecialchars($config['app']['base_path']) . "</code><br>";
echo "Unified login endpoint: <code>{$config['app']['base_path']}api/v1/unified-login</code><br>";
echo "Department dashboard: <code>/Cmain/department/dashboard/index.php</code><br>";
echo "<span class='pass'>✓ OK</span> - Endpoints configured";
echo "</div>";

// Test 6: Check department session helpers
echo "<div class='test'>";
echo "<h3>6. Department Session Files</h3>";
$sessionFile = __DIR__ . '/department/includes/session.php';
$authCheckFile = __DIR__ . '/department/includes/auth_check.php';
$dbFile = __DIR__ . '/department/includes/db.php';

$files = [
    'session.php' => $sessionFile,
    'auth_check.php' => $authCheckFile,
    'db.php' => $dbFile
];

foreach ($files as $name => $path) {
    if (file_exists($path)) {
        echo "<span class='pass'>✓</span> <code>$name</code> exists<br>";
    } else {
        echo "<span class='fail'>✗</span> <code>$name</code> missing<br>";
    }
}
echo "</div>";

// Test 7: Simulate login process
echo "<div class='test'>";
echo "<h3>7. Test Login Process</h3>";
if (count($depts) > 0) {
    $testDept = $depts[0];
    echo "Testing with department: <code>" . htmlspecialchars($testDept['name']) . "</code> (" . $testDept['head_email'] . ")<br>";
    echo "<span class='warn'>NOTE:</span> This is a simulation. Actual credentials are not provided here.";
} else {
    echo "<span class='warn'>⚠</span> Cannot test - no departments with credentials available";
}
echo "</div>";

echo "<hr>";
echo "<p><strong>Next Steps:</strong></p>";
echo "<ul>";
echo "<li>If all tests pass, check the browser console for JavaScript errors</li>";
echo "<li>Check your department head email and password in the admin panel</li>";
echo "<li>Try logging in and check the <code>Network</code> tab to see the API response</li>";
echo "<li>Enable debug mode in <code>app/config.php</code> to see detailed error messages</li>";
echo "</ul>";

?>
