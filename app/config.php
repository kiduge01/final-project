<?php

declare(strict_types=1);

require_once __DIR__ . '/env.php';
load_env(__DIR__ . '/../.env');

// Auto-detect the folder the app is deployed into (e.g. '/htdocs/public',
// '/church-cms/public', or '/public' if deployed at the domain root).
// Works whether this is loaded by public/index.php or a department/*.php
// page run directly. This makes the app portable across hosts/folders
// without editing code. APP_BASE_PATH in .env overrides it if detection
// is ever wrong (e.g. behind a reverse proxy).
$appRootUrlPath = detect_app_root_url_path(dirname(__DIR__));
$autoBasePath = ($appRootUrlPath ?? '') . '/public';

$basePath = env('APP_BASE_PATH', '');
if ($basePath === '') {
    $basePath = $autoBasePath;
}

return [
    'app' => [
        'base_path' => $basePath, // No trailing slash
        'debug' => env('APP_DEBUG', false),
        'timezone' => env('APP_TIMEZONE', 'Africa/Dar_es_Salaam'),
    ],
    'db' => [
        'host' => env('DB_HOST', '127.0.0.1'),
        'port' => (int) env('DB_PORT', 3306),
        'name' => env('DB_NAME', 'dpp'),
        'charset' => env('DB_CHARSET', 'utf8mb4'),
        'user' => env('DB_USER', 'root'),
        'pass' => env('DB_PASS', ''),
    ],
    'security' => [
        'session_name' => env('SESSION_NAME', 'CHURCH_CMS_SESSION'),
        'session_cookie_path' => '/',
        'max_login_attempts' => 5,
        'lockout_minutes' => 15,
    ],
];
