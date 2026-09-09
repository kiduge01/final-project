<?php
// Comprehensive database connection test

echo "<h1>Database Connection Diagnostic</h1>";
echo "<p>Testing multiple connection methods...</p>";

$results = [];

// Test 1: MySQL socket connection
echo "<h2>Test 1: Using Unix Socket</h2>";
$sock = @fsockopen('localhost', 3306, $errno, $errstr, 5);
if ($sock) {
    echo "✓ Socket connection successful<br>";
    fclose($sock);
    $results['socket'] = true;
} else {
    echo "✗ Socket connection failed: $errstr ($errno)<br>";
    $results['socket'] = false;
}

// Test 2: TCP connection
echo "<h2>Test 2: Using TCP 127.0.0.1</h2>";
$sock = @fsockopen('127.0.0.1', 3306, $errno, $errstr, 5);
if ($sock) {
    echo "✓ TCP connection successful<br>";
    fclose($sock);
    $results['tcp'] = true;
} else {
    echo "✗ TCP connection failed: $errstr ($errno)<br>";
    $results['tcp'] = false;
}

// Test 3: MySQLi with empty password
echo "<h2>Test 3: MySQLi Connection (root, no password)</h2>";
@$conn = new mysqli('127.0.0.1', 'root', '', '', 3306);
if ($conn->connect_error) {
    echo "✗ Error: " . $conn->connect_error . "<br>";
    $results['mysqli'] = false;
} else {
    echo "✓ Connected!<br>";
    echo "Server info: " . $conn->server_info . "<br>";
    $results['mysqli'] = true;
    $conn->close();
}

// Test 4: Try different credentials
echo "<h2>Test 4: Try Different Credentials</h2>";
$creds = [
    ['user' => 'root', 'pass' => ''],
    ['user' => 'root', 'pass' => 'root'],
    ['user' => 'root', 'pass' => 'password'],
    ['user' => 'root', 'pass' => '123456'],
];

foreach ($creds as $cred) {
    @$conn = new mysqli('127.0.0.1', $cred['user'], $cred['pass'], '', 3306);
    if ($conn->connect_error) {
        echo "✗ {$cred['user']} / '{$cred['pass']}': " . $conn->connect_error . "<br>";
    } else {
        echo "✓ {$cred['user']} / '{$cred['pass']}': SUCCESS!<br>";
        $conn->close();
        break;
    }
}

// Test 5: Check PHP extensions
echo "<h2>Test 5: PHP Extensions</h2>";
echo "PDO: " . (extension_loaded('pdo') ? '✓ Loaded' : '✗ Not loaded') . "<br>";
echo "PDO MySQL: " . (extension_loaded('pdo_mysql') ? '✓ Loaded' : '✗ Not loaded') . "<br>";
echo "MySQLi: " . (extension_loaded('mysqli') ? '✓ Loaded' : '✗ Not loaded') . "<br>";

// Test 6: Show PHP info
echo "<h2>Test 6: MySQL config from PHP</h2>";
echo "mysql.default_host: " . ini_get('mysql.default_host') . "<br>";
echo "mysql.default_user: " . ini_get('mysql.default_user') . "<br>";
echo "mysql.default_password: " . (ini_get('mysql.default_password') ? '(set)' : '(not set)') . "<br>";
echo "mysqli.default_host: " . ini_get('mysqli.default_host') . "<br>";
echo "mysqli.default_user: " . ini_get('mysqli.default_user') . "<br>";
echo "mysqli.default_pw: " . (ini_get('mysqli.default_pw') ? '(set)' : '(not set)') . "<br>";

echo "<h2>Summary</h2>";
echo "<pre>" . print_r($results, true) . "</pre>";
?>
