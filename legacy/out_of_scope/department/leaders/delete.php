<?php
/**
 * Delete Department Leader
 * 
 * Removes a leadership role from the department.
 */

require_once __DIR__ . '/../includes/auth_check.php';

$pdo = require __DIR__ . '/../includes/db.php';
$departmentId = getCurrentDepartmentId();
$leaderId = $_GET['id'] ?? null;

if (!$leaderId || !is_numeric($leaderId)) {
    header('Location: view.php');
    exit;
}

try {
    // Verify leader belongs to this department
    $stmt = $pdo->prepare('
        SELECT id, leader_name
        FROM department_leaders
        WHERE department_id = ? AND id = ?
    ');
    $stmt->execute([$departmentId, $leaderId]);
    $leader = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$leader) {
        header('Location: view.php');
        exit;
    }

    // Delete the leader
    $stmt = $pdo->prepare('
        DELETE FROM department_leaders
        WHERE department_id = ? AND id = ?
    ');
    $stmt->execute([$departmentId, $leaderId]);

    // Log action
    logDepartmentAction($pdo, 'leader_removed', 'leader', $leaderId, "Removed leader: " . $leader['leader_name']);

    // Redirect with success message
    header('Location: view.php?success=1');
    exit;

} catch (Exception $e) {
    error_log('Delete leader error: ' . $e->getMessage());
    header('Location: view.php?error=1');
    exit;
}

// logDepartmentAction is now centralized in includes/session.php
