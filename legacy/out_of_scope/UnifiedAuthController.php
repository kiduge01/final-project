<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Audit;
use App\Core\Auth;
use App\Core\Url;
use App\Core\Response;
use PDO;

/**
 * Unified Authentication Controller
 * Handles login for both Admin and Department roles
 */
final class UnifiedAuthController
{
    public function __construct(private PDO $pdo)
    {
    }

    /**
     * Unified login endpoint for all roles
     * Accepts role + email + password
     * Returns redirect URL based on role
     */
    public function login(array $input): void
    {
        $role     = trim((string) ($input['role'] ?? ''));
        $email    = trim((string) ($input['email'] ?? ''));
        $password = (string) ($input['password'] ?? '');

        // Validate inputs
        if ($role === '' || $email === '' || $password === '') {
            Response::json(['success' => false, 'message' => 'Role, email, and password are required'], 422);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Response::json(['success' => false, 'message' => 'Invalid email format'], 422);
        }

        // Route based on role
        match ($role) {
            'admin', 'pastor', 'accountant', 'secretary' => $this->loginAdmin($email, $password, $role),
            'department' => $this->loginDepartment($email, $password),
            default => Response::json(['success' => false, 'message' => 'Invalid role'], 400),
        };
    }

    /**
     * Login for Admin system users (all roles)
     */
    private function loginAdmin(string $email, string $password, string $roleName): void
    {
        // Brute-force check
        $check = Auth::checkLoginAllowed($this->pdo, $email);
        if (!$check['allowed']) {
            Response::json([
                'success' => false,
                'message' => 'Too many login attempts. Please try again in ' . ceil($check['retry_after'] / 60) . ' minutes.'
            ], 429);
        }

        // Find user with matching email and role
        $stmt = $this->pdo->prepare(
            'SELECT u.id, u.full_name, u.password_hash, u.role_id, r.name AS role_name
             FROM users u
             INNER JOIN roles r ON r.id = u.role_id
             WHERE u.email = :email AND u.is_active = 1
             LIMIT 1'
        );
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            Auth::recordLoginAttempt($this->pdo, $email);
            Response::json(['success' => false, 'message' => 'Invalid credentials'], 401);
        }

        // Verify password
        if (!password_verify($password, $user['password_hash'])) {
            Auth::recordLoginAttempt($this->pdo, $email);
            Response::json(['success' => false, 'message' => 'Invalid credentials'], 401);
        }

        // Verify role matches requested role (case-insensitive)
        if (strtolower($user['role_name']) !== strtolower($roleName)) {
            Auth::recordLoginAttempt($this->pdo, $email);
            Response::json(['success' => false, 'message' => 'Your role does not match the selected role'], 401);
        }

        // Load permissions for this role
        $user['permissions'] = $this->loadRolePermissions((int) $user['role_id']);

        // Log in user
        Auth::login($user);
        Auth::clearLoginAttempts($this->pdo, $email);

        // Update last_login_at
        $this->pdo->prepare('UPDATE users SET last_login_at = NOW() WHERE id = :id')
            ->execute([':id' => $user['id']]);

        // Audit log
        Audit::log($this->pdo, (int) $user['id'], 'auth', 'login_unified', 'users', (int) $user['id'], null,
            ['role' => $roleName, 'status' => 'logged_in'],
            'Unified login as ' . $roleName
        );

        Response::json([
            'success' => true,
            'message' => 'Login successful',
            'data'    => [
                'user'     => Auth::user(),
                'redirect' => Url::app('/')
            ],
        ]);
    }

    /**
     * Login for Department system heads
     */
    private function loginDepartment(string $email, string $password): void
    {
        // Brute-force check
        $check = Auth::checkLoginAllowed($this->pdo, $email);
        if (!$check['allowed']) {
            Response::json([
                'success' => false,
                'message' => 'Too many login attempts. Please try again in ' . ceil($check['retry_after'] / 60) . ' minutes.'
            ], 429);
        }

        // Find department by email
        $stmt = $this->pdo->prepare(
            'SELECT id, name, head_email, head_password_hash AS password, is_active
             FROM departments
             WHERE head_email = :email AND is_active = 1
             LIMIT 1'
        );
        $stmt->execute([':email' => strtolower($email)]);
        $department = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$department || !password_verify($password, $department['password'])) {
            Auth::recordLoginAttempt($this->pdo, $email);
            Response::json(['success' => false, 'message' => 'Invalid credentials'], 401);
        }

        // Clear login attempts and start session
        Auth::clearLoginAttempts($this->pdo, $email);

        // Regenerate session ID to prevent fixation attacks
        session_regenerate_id(true);

        // Store department session
        $_SESSION['department_id'] = $department['id'];
        $_SESSION['department_name'] = $department['name'];
        $_SESSION['head_name'] = $department['name']; // For compatibility with dashboard
        $_SESSION['user_role'] = 'department_head';
        $_SESSION['is_department_logged_in'] = true;

        // Log the login (if audit table supports it)
        try {
            $this->pdo->prepare(
                'INSERT INTO audit_logs (user_id, action_type, module, entity_type, entity_id, description, created_at)
                 VALUES (NULL, :action, :module, :entity_type, :entity_id, :description, NOW())'
            )->execute([
                ':action' => 'login',
                ':module' => 'department_auth',
                ':entity_type' => 'departments',
                ':entity_id' => $department['id'],
                ':description' => 'Department head login: ' . $department['name']
            ]);
        } catch (\Throwable $e) {
            // Audit logging failed, but login should continue
        }

        Response::json([
            'success' => true,
            'message' => 'Login successful',
            'data'    => [
                'department'  => [
                    'id'   => $department['id'],
                    'name' => $department['name']
                ],
                'redirect' => Url::department('dashboard.php')
            ],
        ]);
    }

    /**
     * Load permission names for a given role_id
     */
    private function loadRolePermissions(int $roleId): array
    {
        try {
            $stmt = $this->pdo->prepare(
                'SELECT p.name FROM permissions p
                 INNER JOIN role_permissions rp ON rp.permission_id = p.id
                 WHERE rp.role_id = :rid'
            );
            $stmt->execute([':rid' => $roleId]);
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (\Throwable $e) {
            return [];
        }
    }
}
