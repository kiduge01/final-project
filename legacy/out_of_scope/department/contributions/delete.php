<?php
/**
 * Delete Department Contribution
 */

require_once __DIR__ . '/../includes/auth_check.php';

$pdo = require __DIR__ . '/../includes/db.php';
$departmentId = getCurrentDepartmentId();
$contribId = $_GET['id'] ?? null;

if (!$contribId || !is_numeric($contribId)) {
    header('Location: view.php');
    exit;
}

try {
    // Verify contribution belongs to this department
    $stmt = $pdo->prepare('
        SELECT id, amount, contributor_name, member_id
        FROM department_contributions
        WHERE department_id = ? AND id = ?
    ');
    $stmt->execute([$departmentId, $contribId]);
    $contrib = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$contrib) {
        header('Location: view.php');
        exit;
    }

    // Delete the record
    $stmt = $pdo->prepare('
        DELETE FROM department_contributions
        WHERE department_id = ? AND id = ?
    ');
    $stmt->execute([$departmentId, $contribId]);

    // Log action
    logDepartmentAction(
        $pdo,
        'contribution_deleted',
        'contribution',
        $contribId,
        "Deleted contribution of Tsh " . number_format($contrib['amount']) . " (ID: $contribId)"
    );

    // Redirect with deleted message
    header('Location: view.php?deleted=1');
    exit;

} catch (Exception $e) {
    error_log('Delete contribution error: ' . $e->getMessage());
    header('Location: view.php?error=delete_failed');
    exit;
}

// logDepartmentAction is now centralized in includes/session.php
