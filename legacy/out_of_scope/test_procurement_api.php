<?php
chdir(__DIR__);
define('BASE_URL', '/Cmain/public');
session_start();
// Simulate admin user session for testing
$_SESSION['user'] = ['id' => 1, 'full_name' => 'System Admin', 'role' => 'admin', 'role_name' => 'admin', 'permissions' => ['procurement.view','procurement.create','procurement.approve','procurement.complete']];

$cfg = require 'app/config.php';
$db = $cfg['db'];
$dsn = 'mysql:host='.$db['host'].';port='.$db['port'].';dbname='.$db['name'].';charset=utf8mb4';
$pdo = new PDO($dsn, $db['user'], $db['pass'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

// Test the procurement requests query
echo "=== Testing listPurchaseRequests query ===\n";
try {
    $sql = "SELECT pr.*, u.full_name AS requested_by_name,
                   db.department AS budget_department, db.fiscal_month AS budget_month,
                   db.planned_amount AS budget_amount,
                   COALESCE(db.reserved_amount, 0) AS budget_reserved,
                   ev.title AS event_title,
                   a.full_name AS approved_by_name,
                   (SELECT COALESCE(SUM(pri.line_total), 0) FROM purchase_request_items pri WHERE pri.purchase_request_id = pr.id) AS items_total,
                   (SELECT COUNT(*) FROM purchase_request_items pri WHERE pri.purchase_request_id = pr.id) AS item_count
            FROM purchase_requests pr
            LEFT JOIN users u ON u.id = pr.requested_by
            LEFT JOIN users a ON a.id = pr.approved_by
            LEFT JOIN department_budgets db ON db.id = pr.budget_id
            LEFT JOIN events ev ON ev.id = pr.event_id
            WHERE 1=1
            ORDER BY pr.id DESC";
    $stmt = $pdo->query($sql);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Rows found: " . count($rows) . "\n";
    if (count($rows) > 0) {
        print_r($rows[0]);
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

// Test active budgets query
echo "\n=== Testing listActiveBudgetsForProcurement query ===\n";
try {
    $sql = "SELECT db.id, db.department, db.fiscal_month, db.planned_amount,
                   COALESCE(db.actual_amount, 0) AS actual_amount,
                   COALESCE(db.reserved_amount, 0) AS reserved_amount,
                   (db.planned_amount - COALESCE(db.actual_amount, 0) - COALESCE(db.reserved_amount, 0)) AS available,
                   db.event_id, ev.title AS event_title, db.description
            FROM department_budgets db
            LEFT JOIN events ev ON ev.id = db.event_id
            WHERE db.status IN ('approved', 'expenses_added')
            ORDER BY db.fiscal_month DESC, db.department ASC";
    $rows = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    echo "Active budgets found: " . count($rows) . "\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

// Check what data exists
echo "\n=== purchase_requests count ===\n";
echo $pdo->query("SELECT COUNT(*) FROM purchase_requests")->fetchColumn() . " total rows\n";

echo "\n=== department_budgets count and statuses ===\n";
$stmt = $pdo->query("SELECT status, COUNT(*) as cnt FROM department_budgets GROUP BY status");
foreach ($stmt->fetchAll() as $r) {
    echo $r['status'] . ': ' . $r['cnt'] . "\n";
}

// Test the actual ApiController
echo "\n=== Testing ApiController::listPurchaseRequests() ===\n";
require_once 'app/core/Database.php';
require_once 'app/core/Auth.php';
require_once 'app/core/Audit.php';
require_once 'app/core/Response.php';
require_once 'app/controllers/ApiController.php';

\App\Core\Auth::boot($cfg);
$_SESSION['user'] = ['id' => 1, 'full_name' => 'System Admin', 'role' => 'admin', 'role_id' => 1, 'permissions' => []];

$api = new \App\Controllers\ApiController($pdo);
ob_start();
try {
    $api->listPurchaseRequests();
} catch (Throwable $e) {
    echo json_encode(['success' => false, 'exception' => $e->getMessage()]);
}
$raw = ob_get_clean();
echo "Raw response (first 500 chars): " . substr($raw, 0, 500) . "\n";
$json = json_decode($raw, true);
if ($json === null) {
    echo "JSON PARSE FAILED! Response was not valid JSON.\n";
} else {
    echo "success: " . ($json['success'] ? 'true' : 'false') . "\n";
    echo "data count: " . count($json['data'] ?? []) . "\n";
}
