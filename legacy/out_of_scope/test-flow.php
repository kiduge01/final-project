<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Simulate Response::view() flow
$config = require __DIR__ . '/../app/config.php';
require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/core/Response.php';

define('BASE_URL', $config['app']['base_path']);

// Initialize database
\App\Core\Database::connection($config);

// Simulate what PageController::loginPage() passes to Response::view()
$data = [
    'title' => 'Test Login',
    'page'  => 'login',
    'error' => null,
];

// Simulate Response::view() injecting branding
$data['viewPath'] = 'pages/login.php';
$data['baseUrl']  = BASE_URL;

if (!isset($data['churchName'])) {
    $brand = \App\Core\Response::loadChurchBranding();
    $data['churchName'] = $brand['church_name'];
    $data['churchLogo'] = $brand['church_logo'];
}

// Now check what variables we have
echo "<h2>Variables after Response::view() injection:</h2>";
echo "<pre>";
echo "churchName: '" . htmlspecialchars($data['churchName']) . "'\n";
echo "churchLogo: '" . htmlspecialchars($data['churchLogo']) . "'\n";
echo "baseUrl: '" . htmlspecialchars($data['baseUrl']) . "'\n";
echo "</pre>";

// Now simulate extract() 
extract($data, EXTR_SKIP);

// After extract, verify variables are available
echo "<h2>Variables after extract():</h2>";
echo "<pre>";
echo "churchName: '" . htmlspecialchars($churchName ?? 'NOT SET') . "'\n";
echo "churchLogo: '" . htmlspecialchars($churchLogo ?? 'NOT SET') . "'\n";
echo "baseUrl: '" . htmlspecialchars($baseUrl ?? 'NOT SET') . "'\n";
echo "</pre>";
?>
