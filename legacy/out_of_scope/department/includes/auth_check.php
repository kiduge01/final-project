<?php
/**
 * Authentication Check for Protected Pages
 * 
 * Include this file at the top of all protected department pages.
 * It verifies that a department head is logged in.
 * If not authenticated, redirects to login page.
 */

// Ensure session is started
require_once __DIR__ . '/session.php';

// Check if department head is logged in
if (!isDepartmentLoggedIn()) {
    // Redirect to main unified login
    header('Location: ' . appUrl('login'));
    exit;
}

// Optional: Verify department still exists in database
// This prevents access if department is deleted or deactivated
$pdo = require __DIR__ . '/db.php';

try {
    $stmt = $pdo->prepare('
        SELECT id, is_active 
        FROM departments 
        WHERE id = ? AND is_active = 1
    ');
    $stmt->execute([getCurrentDepartmentId()]);
    $dept = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$dept) {
        // Department not found or inactive - logout and redirect
        destroyDepartmentSession();
        header('Location: ' . appUrl('login?error=department_inactive'));
        exit;
    }
} catch (Exception $e) {
    // Database error - redirect to login as fallback
    error_log('Auth check database error: ' . $e->getMessage());
    header('Location: ' . appUrl('login?error=db_error'));
    exit;
}
