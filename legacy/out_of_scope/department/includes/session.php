<?php
/**
 * Department Session Management
 * Uses the same session as the main app
 * so a single login works for both admin and department.
 */

if (session_status() === PHP_SESSION_NONE) {
    // Use the same session name as the main application
    // Load the config to get the correct session name
    $config = require __DIR__ . '/../../app/config.php';
    session_name($config['security']['session_name']);
    session_start();
}

// Define BASE_URL if not already defined (since department pages are accessed directly)
if (!defined('BASE_URL')) {
    $config = $config ?? require __DIR__ . '/../../app/config.php';
    define('BASE_URL', $config['app']['base_path'] ?? '');
}

require_once __DIR__ . '/../../app/core/Url.php';

function appUrl(string $path = ''): string {
    return \App\Core\Url::app($path);
}

function departmentUrl(string $path = ''): string {
    return \App\Core\Url::department($path);
}

/**
 * Check if department head is authenticated
 * 
 * @return bool True if authenticated
 */
function isDepartmentLoggedIn() {
    return isset($_SESSION['department_id']) && !empty($_SESSION['department_id']);
}

/**
 * Get current logged-in department ID
 * 
 * @return int|null Department ID or null if not logged in
 */
function getCurrentDepartmentId() {
    return $_SESSION['department_id'] ?? null;
}

/**
 * Get current department head name
 * 
 * @return string|null Head name or null if not logged in
 */
function getCurrentHeadName() {
    return $_SESSION['head_name'] ?? $_SESSION['department_name'] ?? null;
}

/**
 * Get current department head email
 * 
 * @return string|null Head email or null if not logged in
 */
function getCurrentHeadEmail() {
    return $_SESSION['head_email'] ?? null;
}

/**
 * Set department session after successful login
 * 
 * @param int $departmentId
 * @param string $headName
 * @param string $headEmail
 * @return void
 */
function setDepartmentSession($departmentId, $headName, $headEmail) {
    // Regenerate session ID to prevent fixation attacks
    session_regenerate_id(true);
    
    $_SESSION['department_id'] = (int)$departmentId;
    $_SESSION['head_name'] = trim($headName);
    $_SESSION['head_email'] = trim($headEmail);
}

/**
 * Destroy department session (logout)
 * 
 * @return void
 */
function destroyDepartmentSession() {
    $_SESSION = [];
    
    // Delete session cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }
    
    session_destroy();
}

/**
 * Log department action to audit trail
 */
function logDepartmentAction($pdo, $action, $entityType, $entityId, $summary, $oldValues = null, $newValues = null) {
    try {
        $departmentId = getCurrentDepartmentId();
        $stmt = $pdo->prepare('
            INSERT INTO audit_logs (actor_user_id, module_name, action_name, entity_type, entity_id, change_summary, old_values, new_values, ip_address, user_agent)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ');
        $stmt->execute([
            null, // actor_user_id is for users table, we are in department context
            'department',
            $action,
            $entityType,
            $entityId,
            $summary,
            $oldValues ? json_encode($oldValues) : null,
            $newValues ? json_encode($newValues) : null,
            $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            substr($_SERVER['HTTP_USER_AGENT'] ?? 'unknown', 0, 255)
        ]);
    } catch (Exception $e) {
        error_log('Audit log error: ' . $e->getMessage());
    }
}
