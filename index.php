<?php
// Redirect to public front controller (path auto-detected so this
// works no matter what folder the app is deployed into).
require_once __DIR__ . '/app/env.php';
$appRootUrlPath = detect_app_root_url_path(__DIR__);
header('Location: ' . ($appRootUrlPath ?? '') . '/public/');
exit;
