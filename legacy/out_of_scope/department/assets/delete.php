<?php
/**
 * Unassign/Delete Asset from Department
 */

require_once __DIR__ . '/../includes/auth_check.php';

$pdo = require __DIR__ . '/../includes/db.php';
$departmentId = getCurrentDepartmentId();
$assetId = $_GET['id'] ?? null;

if (!$assetId || !is_numeric($assetId)) {
    header('Location: view.php');
    exit;
}

try {
    // Verify asset is currently assigned to this department
    $stmt = $pdo->prepare('
        SELECT a.id, a.name
        FROM assets a
        INNER JOIN asset_assignments aa ON a.id = aa.asset_id
        WHERE aa.assigned_type = "department"
          AND aa.assigned_department_id = ?
          AND aa.assigned_to IS NULL
          AND a.id = ?
    ');
    $stmt->execute([$departmentId, $assetId]);
    $asset = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$asset) {
        header('Location: view.php');
        exit;
    }

    // Unassign the asset by setting assigned_to = NOW()
    $stmt = $pdo->prepare('
        UPDATE asset_assignments
        SET assigned_to = NOW()
        WHERE asset_id = ?
          AND assigned_department_id = ?
          AND assigned_to IS NULL
    ');
    $stmt->execute([$assetId, $departmentId]);

    // Log action
    logDepartmentAction(
        $pdo,
        'asset_unassigned',
        'asset',
        $assetId,
        "Unassigned asset: " . $asset['name'] . " (ID: $assetId)"
    );

    // Redirect with unassigned message
    header('Location: view.php?unassigned=1');
    exit;

} catch (Exception $e) {
    error_log('Unassign asset error: ' . $e->getMessage());
    header('Location: view.php?error=unassign_failed');
    exit;
}

// logDepartmentAction is now centralized in includes/session.php
