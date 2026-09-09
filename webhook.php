<?php

declare(strict_types=1);

$config = require __DIR__ . '/app/config.php';

header('Content-Type: application/json; charset=utf-8');

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'POST required']);
    exit;
}

$webhookSecret = trim((string) env('SMS_WEBHOOK_SECRET', ''));
if ($webhookSecret !== '') {
    $providedSecret = (string) ($_SERVER['HTTP_X_WEBHOOK_SECRET'] ?? '');
    if ($providedSecret === '' || !hash_equals($webhookSecret, $providedSecret)) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Invalid webhook secret']);
        exit;
    }
}

require_once __DIR__ . '/app/core/Database.php';
require_once __DIR__ . '/app/core/Auth.php';
require_once __DIR__ . '/app/core/Audit.php';
require_once __DIR__ . '/app/core/Response.php';
require_once __DIR__ . '/app/controllers/ApiController.php';

try {
    $pdo = \App\Core\Database::connection($config);
    $payload = json_decode((string) file_get_contents('php://input'), true);
    if (!is_array($payload)) {
        $payload = $_POST;
    }

    (new \App\Controllers\ApiController($pdo))->smsWebhook($payload);
} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Webhook processing failed']);
}
