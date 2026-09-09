<?php
/**
 * Delete Finance Record
 * 
 * Soft delete a finance transaction (mark as deleted).
 */

require_once __DIR__ . '/../includes/auth_check.php';

$pdo = require __DIR__ . '/../includes/db.php';
$departmentId = getCurrentDepartmentId();
$recordId = $_GET['id'] ?? null;

if (!$recordId || !is_numeric($recordId)) {
    header('Location: view.php');
    exit;
}

try {
    // Verify record belongs to this department
    $stmt = $pdo->prepare('
        SELECT fe.id, fe.amount, fc.category_type as type, fc.name as category
        FROM finance_entries fe
        JOIN finance_categories fc ON fe.category_id = fc.id
        WHERE fe.department_id = ? AND fe.id = ?
    ');
    $stmt->execute([$departmentId, $recordId]);
    $record = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$record) {
        header('Location: view.php');
        exit;
    }

    // Delete the record
    $stmt = $pdo->prepare('
        DELETE FROM finance_entries
        WHERE department_id = ? AND id = ?
    ');
    $stmt->execute([$departmentId, $recordId]);

    // Log action
    logDepartmentAction(
        $pdo,
        'finance_deleted',
        'finance',
        $recordId,
        "Deleted " . $record['type'] . ": " . $record['category'] . " - Tsh " . number_format($record['amount'], 2)
    );

    // Redirect with success message
    header('Location: view.php?success=1');
    exit;

} catch (Exception $e) {
    error_log('Delete finance error: ' . $e->getMessage());
    header('Location: view.php?error=1');
    exit;
}

// logDepartmentAction is now centralized in includes/session.php
