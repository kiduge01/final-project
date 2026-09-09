<?php

declare(strict_types=1);

namespace App\Core;

final class Auth
{
    public static function boot(array $config): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            // Set cookie path from config
            ini_set('session.cookie_path', $config['security']['session_cookie_path'] ?? '/');
            session_name($config['security']['session_name']);
            session_start();
        }
    }

    public static function login(array $user): void
    {
        // Regenerate session ID to prevent session fixation
        session_regenerate_id(true);

        $_SESSION['user'] = [
            'id'          => (int) $user['id'],
            'full_name'   => $user['full_name'],
            'role'        => $user['role_name'] ?? ($user['role'] ?? ''),
            'role_id'     => (int) ($user['role_id'] ?? 0),
            'permissions' => $user['permissions'] ?? [],
        ];
    }

    public static function user(): ?array
    {
        return $_SESSION['user'] ?? null;
    }

    public static function check(): bool
    {
        return isset($_SESSION['user']);
    }

    /** Check if current user has a specific permission. Admins bypass all checks. */
    public static function can(string $permission): bool
    {
        $user = self::user();
        if (!$user) return false;
        if (strtolower($user['role'] ?? '') === 'admin') return true;
        return in_array($permission, $user['permissions'] ?? [], true);
    }

    /** Get all permissions for the current user. */
    public static function permissions(): array
    {
        $user = self::user();
        return $user['permissions'] ?? [];
    }

    public static function hasRole(array $roles): bool
    {
        if (!self::check()) {
            return false;
        }

        return in_array($_SESSION['user']['role'], $roles, true);
    }

    public static function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
    }

    /* ── CSRF Protection ── */

    /** Generate a fresh CSRF token and store it in the session. */
    public static function generateCsrfToken(): string
    {
        $token = bin2hex(random_bytes(32));
        $_SESSION['_csrf_token'] = $token;
        return $token;
    }

    /** Return the current CSRF token (creates one if missing). */
    public static function getCsrfToken(): string
    {
        if (empty($_SESSION['_csrf_token'])) {
            return self::generateCsrfToken();
        }
        return $_SESSION['_csrf_token'];
    }

    /** Verify a submitted token against the session token (timing-safe). */
    public static function validateCsrfToken(?string $token): bool
    {
        if (empty($token) || empty($_SESSION['_csrf_token'])) {
            return false;
        }
        return hash_equals($_SESSION['_csrf_token'], $token);
    }

    /* ── Brute-Force Protection ── */

    /**
     * Check whether the given identifier (email/IP) is allowed to attempt login.
     * Returns ['allowed' => bool, 'remaining' => int, 'retry_after' => int (seconds)|null].
     */
    public static function checkLoginAllowed(\PDO $pdo, string $identifier): array
    {
        // Load config for brute force limits
        $config = require __DIR__ . '/../config.php';
        $maxAttempts = $config['security']['max_login_attempts'] ?? 5;
        $lockoutMinutes = $config['security']['lockout_minutes'] ?? 15;

        $identifier = strtolower(trim($identifier));
        $window = date('Y-m-d H:i:s', strtotime('-' . $lockoutMinutes . ' minutes'));

        $stmt = $pdo->prepare(
            'SELECT COUNT(*) FROM login_attempts WHERE identifier = :id AND attempted_at > :since'
        );
        $stmt->execute([':id' => $identifier, ':since' => $window]);
        $count = (int) $stmt->fetchColumn();

        if ($count >= $maxAttempts) {
            // Find the oldest attempt in the window to compute retry_after
            $stmt2 = $pdo->prepare(
                'SELECT MIN(attempted_at) FROM login_attempts WHERE identifier = :id AND attempted_at > :since'
            );
            $stmt2->execute([':id' => $identifier, ':since' => $window]);
            $oldest = $stmt2->fetchColumn();
            $retryAfter = $oldest ? max(0, (strtotime($oldest) + $lockoutMinutes * 60) - time()) : $lockoutMinutes * 60;

            return ['allowed' => false, 'remaining' => 0, 'retry_after' => $retryAfter];
        }

        return ['allowed' => true, 'remaining' => $maxAttempts - $count, 'retry_after' => null];
    }

    /** Record a failed login attempt. */
    public static function recordLoginAttempt(\PDO $pdo, string $identifier): void
    {
        $identifier = strtolower(trim($identifier));
        $pdo->prepare(
            'INSERT INTO login_attempts (identifier, attempted_at) VALUES (:id, NOW())'
        )->execute([':id' => $identifier]);

        // Prune old entries (> 24h) to keep table small
        $pdo->exec("DELETE FROM login_attempts WHERE attempted_at < DATE_SUB(NOW(), INTERVAL 24 HOUR)");
    }

    /** Clear attempts after successful login. */
    public static function clearLoginAttempts(\PDO $pdo, string $identifier): void
    {
        $identifier = strtolower(trim($identifier));
        $pdo->prepare('DELETE FROM login_attempts WHERE identifier = :id')->execute([':id' => $identifier]);
    }
}
