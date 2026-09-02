<?php
/**
 * Diagnostic Script: Test Church Branding Database Operations
 */

require_once 'app/config.php';
require_once 'app/core/Database.php';
require_once 'app/core/Response.php';

echo "<h2>🔍 Church Branding Diagnostic Test</h2>";
echo "<pre>";

try {
    $pdo = \App\Core\Database::getConnection();
    
    if (!$pdo) {
        echo "❌ ERROR: Could not connect to database\n";
        exit;
    }
    
    echo "✅ Database connection successful\n\n";
    
    // Test 1: Check if church_settings table exists
    echo "TEST 1: Checking church_settings table existence...\n";
    $result = $pdo->query("SHOW TABLES LIKE 'church_settings'")->fetchAll();
    if (count($result) > 0) {
        echo "✅ church_settings table exists\n\n";
    } else {
        echo "❌ church_settings table does NOT exist\n";
        exit;
    }
    
    // Test 2: Show current table contents
    echo "TEST 2: Current church_settings table contents:\n";
    $rows = $pdo->query("SELECT * FROM church_settings")->fetchAll(\PDO::FETCH_ASSOC);
    foreach ($rows as $row) {
        echo sprintf("  • %s = '%s' (updated: %s)\n", 
            $row['setting_key'], 
            $row['setting_value'],
            $row['updated_at'] ?? 'N/A'
        );
    }
    echo "\n";
    
    // Test 3: Test the exact query used by loadChurchBranding()
    echo "TEST 3: Testing loadChurchBranding() query:\n";
    $stmt = $pdo->query(
        "SELECT setting_key, setting_value FROM church_settings WHERE setting_key IN ('church_name', 'church_logo')"
    );
    $queryRows = $stmt->fetchAll(\PDO::FETCH_KEY_PAIR);
    echo "  Query returned " . count($queryRows) . " rows\n";
    echo "  church_name: '" . ($queryRows['church_name'] ?? 'NOT FOUND') . "'\n";
    echo "  church_logo: '" . ($queryRows['church_logo'] ?? 'NOT FOUND') . "'\n\n";
    
    // Test 4: Test loading branding via Response class
    echo "TEST 4: Testing Response::loadChurchBranding():\n";
    $brand = \App\Core\Response::loadChurchBranding();
    echo "  church_name: '" . $brand['church_name'] . "'\n";
    echo "  church_logo: '" . $brand['church_logo'] . "'\n\n";
    
    // Test 5: Test INSERT/UPDATE operation
    echo "TEST 5: Testing INSERT...ON DUPLICATE KEY UPDATE:\n";
    $testStmt = $pdo->prepare(
        'INSERT INTO church_settings (setting_key, setting_value)
         VALUES (:k, :v)
         ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)'
    );
    $testStmt->execute([':k' => 'church_name', ':v' => 'TEST CHURCH ' . time()]);
    echo "  ✅ INSERT executed without error\n";
    
    // Test 6: Verify INSERT worked
    echo "\nTEST 6: Verifying INSERT worked:\n";
    $testValue = $pdo->query("SELECT setting_value FROM church_settings WHERE setting_key = 'church_name'")->fetchColumn();
    if (strpos($testValue, 'TEST CHURCH') === 0) {
        echo "  ✅ Value persisted to database: '" . $testValue . "'\n";
    } else {
        echo "  ❌ Value NOT persisted. Current value: '" . $testValue . "'\n";
    }
    
    // Test 7: Restore original value
    echo "\nTEST 7: Restoring original church name:\n";
    $restoreStmt = $pdo->prepare(
        'INSERT INTO church_settings (setting_key, setting_value)
         VALUES (:k, :v)
         ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)'
    );
    $restoreStmt->execute([':k' => 'church_name', ':v' => 'TAG MSASANI']);
    echo "  ✅ Restored to: 'TAG MSASANI'\n";
    
    echo "\n✅ All tests completed successfully!\n";
    
} catch (\Throwable $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}

echo "</pre>";

// Add simple form to test logo upload and name update
echo <<<'HTML'
<hr>
<h2>🧪 Test Form</h2>
<form method="POST" enctype="multipart/form-data">
    <div>
        <label>Church Name:</label>
        <input type="text" name="church_name" value="TAG MSASANI" style="width: 300px; padding: 5px;">
        <button type="submit" name="action" value="update_name">Update Name</button>
    </div>
    <hr>
    <div>
        <label>Church Logo:</label>
        <input type="file" name="church_logo" accept="image/*">
        <button type="submit" name="action" value="update_logo">Upload Logo</button>
    </div>
</form>

<?php
// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo = \App\Core\Database::getConnection();
        
        if ($_POST['action'] === 'update_name') {
            $churchName = $_POST['church_name'] ?? 'TAG MSASANI';
            $stmt = $pdo->prepare(
                'INSERT INTO church_settings (setting_key, setting_value)
                 VALUES (:k, :v)
                 ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)'
            );
            $stmt->execute([':k' => 'church_name', ':v' => trim($churchName)]);
            echo "<p style='color: green; font-weight: bold;'>✅ Church name updated to: " . htmlspecialchars($churchName) . "</p>";
        } else if ($_POST['action'] === 'update_logo') {
            if (!isset($_FILES['church_logo']) || $_FILES['church_logo']['error'] !== UPLOAD_ERR_OK) {
                echo "<p style='color: red; font-weight: bold;'>❌ No file uploaded</p>";
            } else {
                $file = $_FILES['church_logo'];
                $uploadDir = __DIR__ . '/public/uploads/logos/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                
                $filename = 'church_logo_' . time() . '.' . pathinfo($file['name'], PATHINFO_EXTENSION);
                $destPath = $uploadDir . $filename;
                
                if (move_uploaded_file($file['tmp_name'], $destPath)) {
                    $relativePath = '/uploads/logos/' . $filename;
                    $stmt = $pdo->prepare(
                        'INSERT INTO church_settings (setting_key, setting_value)
                         VALUES (:k, :v)
                         ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)'
                    );
                    $stmt->execute([':k' => 'church_logo', ':v' => $relativePath]);
                    echo "<p style='color: green; font-weight: bold;'>✅ Logo uploaded successfully: " . htmlspecialchars($relativePath) . "</p>";
                } else {
                    echo "<p style='color: red; font-weight: bold;'>❌ Failed to save file</p>";
                }
            }
        }
    } catch (\Throwable $e) {
        echo "<p style='color: red; font-weight: bold;'>❌ Error: " . $e->getMessage() . "</p>";
    }
}
?>
HTML;
