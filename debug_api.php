<?php
/**
 * Debug API Tester
 * Tests individual API endpoints and shows raw responses
 */

$config = require __DIR__ . '/app/config.php';

try {
    $pdo = new PDO(
        sprintf('mysql:host=%s;port=%d;dbname=%s;charset=%s', $config['db']['host'], $config['db']['port'], $config['db']['name'], $config['db']['charset']),
        $config['db']['user'],
        $config['db']['pass'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Start session for Auth
session_name($config['security']['session_name']);
session_start();

// Set up a fake user session for testing
$_SESSION['user'] = [
    'id' => 1,
    'full_name' => 'Test User',
    'role' => 'Admin',
    'role_id' => 1,
    'permissions' => [],
];

?>
<!DOCTYPE html>
<html>
<head>
    <title>API Debug Tester</title>
    <style>
        body { font-family: monospace; margin: 20px; }
        .test { border: 1px solid #ccc; padding: 10px; margin: 10px 0; }
        .response { background: #f5f5f5; padding: 10px; margin: 5px 0; border-left: 3px solid #0066cc; overflow: auto; max-height: 300px; }
        .error { color: red; }
        .success { color: green; }
        button { padding: 5px 10px; cursor: pointer; }
    </style>
</head>
<body>

<h1>Church CMS - API Debug Tester</h1>

<div class="test">
    <h3>Test: Get Church Profile</h3>
    <button onclick="testEndpoint('/api/v1/settings/church-profile')">Test Endpoint</button>
    <div id="church-profile-response" class="response hidden">Loading...</div>
</div>

<div class="test">
    <h3>Test: List Departments</h3>
    <button onclick="testEndpoint('/api/v1/departments')">Test Endpoint</button>
    <div id="departments-response" class="response hidden">Loading...</div>
</div>

<div class="test">
    <h3>Test: Dashboard Stats</h3>
    <button onclick="testEndpoint('/api/v1/dashboard/stats')">Test Endpoint</button>
    <div id="dashboard-response" class="response hidden">Loading...</div>
</div>

<script>
const BASE_URL = '<?= $config['app']['base_path'] ?>';

async function testEndpoint(endpoint) {
    const responseEl = document.querySelector('[id*="' + endpoint.split('/').pop() + '-response"]') || document.createElement('div');
    responseEl.classList.remove('hidden');
    responseEl.innerHTML = '<strong>Testing...</strong>';
    
    try {
        const response = await fetch(BASE_URL + endpoint);
        
        // Show status
        let result = `<strong>HTTP Status: ${response.status}</strong><br>`;
        result += `<strong>Content-Type:</strong> ${response.headers.get('content-type')}<br><br>`;
        
        // Read as text first
        const text = await response.text();
        result += `<strong>Raw Response (first 500 chars):</strong><br><pre>${escapeHtml(text.substring(0, 500))}</pre>`;
        
        // Try to parse as JSON
        try {
            const json = JSON.parse(text);
            result += `<span class="success"><strong>✓ Valid JSON</strong></span><br><br>`;
            result += `<strong>Parsed JSON:</strong><br><pre>${escapeHtml(JSON.stringify(json, null, 2))}</pre>`;
        } catch (e) {
            result += `<span class="error"><strong>✗ Invalid JSON:</strong> ${escapeHtml(e.message)}</span><br>`;
        }
        
        responseEl.innerHTML = result;
    } catch (e) {
        responseEl.innerHTML = `<span class="error"><strong>Error:</strong> ${escapeHtml(e.message)}</span>`;
    }
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>

</body>
</html>
