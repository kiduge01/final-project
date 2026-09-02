<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>🔍 Response::loadChurchBranding() Test</h2><pre>";

try {
    // Load all required classes
    $config = require __DIR__ . '/../app/config.php';
    require_once __DIR__ . '/../app/core/Database.php';
    require_once __DIR__ . '/../app/core/Response.php';
    
    // Initialize database
    \App\Core\Database::connection($config);
    
    echo "✅ Database initialized\n\n";
    
    // Test Response::loadChurchBranding()
    echo "Calling Response::loadChurchBranding()...\n";
    $brand = \App\Core\Response::loadChurchBranding();
    
    echo "✅ Method executed successfully\n\n";
    echo "church_name: '" . htmlspecialchars($brand['church_name']) . "'\n";
    echo "church_logo: '" . htmlspecialchars($brand['church_logo']) . "'\n";
    
    if ($brand['church_name'] === 'TAG MSASANI') {
        echo "\n✅ PASS: Church name is correct!";
    } else {
        echo "\n❌ FAIL: Church name is incorrect!";
    }
    
    if ($brand['church_logo'] === '/uploads/logos/church_logo_1781278855.png') {
        echo "\n✅ PASS: Church logo is correct!";
    } else {
        echo "\n❌ FAIL: Church logo is: " . $brand['church_logo'];
    }
    
} catch (\Throwable $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}

echo "</pre>";
?>
