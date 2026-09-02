<?php
require 'app/config.php';
require 'app/core/Database.php';

$config = require 'app/config.php';
$pdo = \App\Core\Database::connection($config);

// Check departments with credentials
$stmt = $pdo->query('SELECT id, name, head_email, head_password_hash FROM departments WHERE is_active = 1');
$depts = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<h2>Active Departments with Credentials</h2>";
if (empty($depts)) {
    echo "No active departments found.<br>";
} else {
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Has Password</th></tr>";
    foreach ($depts as $dept) {
        $hasPwd = !empty($dept['head_password_hash']) ? 'YES' : 'NO';
        echo "<tr>";
        echo "<td>{$dept['id']}</td>";
        echo "<td>{$dept['name']}</td>";
        echo "<td>{$dept['head_email']}</td>";
        echo "<td>{$hasPwd}</td>";
        echo "</tr>";
    }
    echo "</table>";
}
?>
