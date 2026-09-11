<?php
echo "<h2>Test 1: PDO Connection</h2>";
echo "<pre>";

try {
    $pdo = new PDO('mysql:host=127.0.0.1;port=3306;dbname=church_cms;charset=utf8mb4', 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    echo "✓ PDO Connection successful!\n";
} catch (Exception $e) {
    echo "✗ PDO failed: " . $e->getMessage() . "\n";
}

echo "\n<h2>Test 2: MySQLi Connection</h2>";
echo "<pre>";

$conn = @mysqli_connect('127.0.0.1', 'root', '', '', 3306);
if ($conn) {
    echo "✓ MySQLi Connection successful!\n";
    mysqli_close($conn);
} else {
    echo "✗ MySQLi failed: " . mysqli_connect_error() . "\n";
}

echo "\n<h2>Test 3: Check PHP Modules</h2>";
echo "PDO loaded: " . (extension_loaded('pdo') ? 'Yes' : 'No') . "\n";
echo "PDO MySQL loaded: " . (extension_loaded('pdo_mysql') ? 'Yes' : 'No') . "\n";
echo "MySQLi loaded: " . (extension_loaded('mysqli') ? 'Yes' : 'No') . "\n";

echo "\n<h2>Test 4: Try to connect with different host</h2>";
$hosts = ['localhost', '127.0.0.1', '::1', '0.0.0.0', 'localhost.localdomain'];
foreach ($hosts as $host) {
    echo "Testing host '$host': ";
    $conn = @mysqli_connect($host, 'root', '', '', 3306);
    if ($conn) {
        echo "✓ Connected\n";
        mysqli_close($conn);
    } else {
        echo "✗ " . mysqli_connect_error() . "\n";
    }
}

echo "</pre>";
?>
