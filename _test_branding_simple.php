<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'app/config.php';
require_once 'app/core/Database.php';
require_once 'app/core/Response.php';

$config = require 'app/config.php';

// Initialize database
\App\Core\Database::connection($config);

// Test branding loading
$brand = \App\Core\Response::loadChurchBranding();

echo "Church Name: " . $brand['church_name'] . "\n";
echo "Church Logo: " . $brand['church_logo'] . "\n";
echo "\nSuccess! Branding loaded correctly.\n";
