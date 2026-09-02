<?php
/**
 * Submit Department Report
 * 
 * Change report status from draft to submitted (ready for admin review).
 */

require_once __DIR__ . '/../includes/auth_check.php';

$pdo = require __DIR__ . '/../includes/db.php';
$departmentId = getCurrentDepartmentId();
$reportId = $_GET['id'] ?? null;

if (!$reportId || !is_numeric($reportId)) {
    header('Location: view.php');
    exit;
}

try {
    // Verify report exists and belongs to this department
    $stmt = $pdo->prepare('
        SELECT id, status, title
        FROM department_reports
        WHERE department_id = ? AND id = ?
    ');
    $stmt->execute([$departmentId, $reportId]);
    $report = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$report) {
        header('Location: view.php');
        exit;
    }

    // Only allow submit if status is draft
    if ($report['status'] !== 'draft') {
        header('Location: view-detail.php?id=' . $reportId . '&error=not_draft');
        exit;
    }

    // Update report status to submitted
    $stmt = $pdo->prepare('
        UPDATE department_reports
        SET status = "submitted", submitted_at = NOW()
        WHERE department_id = ? AND id = ?
    ');
    $stmt->execute([$departmentId, $reportId]);

    // Log action
    logDepartmentAction($pdo, 'report_submitted', 'report', $reportId, "Submitted report: " . $report['title']);

    // Redirect with success message
    header('Location: view.php?success=submitted');
    exit;

} catch (Exception $e) {
    error_log('Submit report error: ' . $e->getMessage());
    header('Location: view.php?error=1');
    exit;
}

// logDepartmentAction is now centralized in includes/session.php
