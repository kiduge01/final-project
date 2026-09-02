<?php

declare(strict_types=1);

$config = require __DIR__ . '/../app/config.php';

date_default_timezone_set($config['app']['timezone'] ?? 'Africa/Dar_es_Salaam');

define('BASE_URL', $config['app']['base_path']);

require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/core/Url.php';
require_once __DIR__ . '/../app/core/Auth.php';
require_once __DIR__ . '/../app/core/Audit.php';
require_once __DIR__ . '/../app/core/Response.php';
require_once __DIR__ . '/../app/controllers/PageController.php';
require_once __DIR__ . '/../app/controllers/ApiController.php';
require_once __DIR__ . '/../app/controllers/SadakaController.php';
require_once __DIR__ . '/../app/controllers/AIController.php';

use App\Controllers\ApiController;
use App\Controllers\PageController;
use App\Controllers\SadakaController;
use App\Controllers\AIController;
use App\Core\Auth;
use App\Core\Database;
use App\Core\Url;
use App\Core\Route;
use App\Core\Response;

Auth::boot($config);

try {
    $pdo = Database::connection($config);
} catch (\Throwable $e) {
    http_response_code(500);
    echo '<h1>Database connection failed</h1>';
    if ($config['app']['debug']) {
        echo '<pre>' . htmlspecialchars($e->getMessage()) . '</pre>';
    }
    exit;
}

$pageController = new PageController($pdo);
$apiController  = new ApiController($pdo);
$sadakaController = new SadakaController($pdo);
$aiController = new AIController($pdo);

$uri    = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// Strip base path prefix from URI (happens when root .htaccess rewrites to public/index.php)
$fullPrefix = rtrim(BASE_URL, '/') . '/';
if (str_starts_with($uri, $fullPrefix)) {
    $uri = substr($uri, strlen($fullPrefix) - 1); 
} elseif ($uri === rtrim(BASE_URL, '/')) {
    $uri = '/';
}

// Strip base path to get clean route if it still exists
if ($uri && str_starts_with($uri, BASE_URL)) {
    $uri = substr($uri, strlen(BASE_URL));
}

// Ensure we have a valid URI
if ($uri === '' || $uri === false) {
    $uri = '/';
}

// Ensure leading slash
if ($uri[0] !== '/') {
    $uri = '/' . $uri;
}

// ────── API routes (no HTML, JSON only) ──────
if (str_starts_with($uri, '/api/v1/')) {
    // Ensure no output before JSON
    if (ob_get_level() === 0) ob_start();
    
    // Set strict error handling for API
    error_reporting(E_ALL);
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
    
    // Catch all errors and convert to JSON
    set_error_handler(function ($errno, $errstr, $errfile, $errline) {
        // Clear any output buffers
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => false,
            'message' => 'Internal error: ' . $errstr,
            'debug' => [
                'file' => $errfile,
                'line' => $errline,
                'code' => $errno
            ]
        ]);
        exit;
    });
    
    set_exception_handler(function (\Throwable $e) {
        // Clear any output buffers
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => false,
            'message' => 'Exception: ' . $e->getMessage(),
            'debug' => [
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]
        ]);
        exit;
    });
    
    header('Content-Type: application/json; charset=utf-8');

    // Auth-exempt API endpoints
    $authExempt = ['/api/v1/auth/login', '/api/v1/auth/forgot-password', '/api/v1/auth/reset-password'];

    if (!Auth::check() && !in_array($uri, $authExempt, true)) {
        Response::json(['success' => false, 'message' => 'Unauthenticated'], 401);
    }

    // CSRF validation for state-changing API requests
    if (in_array($method, ['POST', 'PUT', 'DELETE'], true)) {
        $csrfToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!Auth::validateCsrfToken($csrfToken)) {
            Response::json(['success' => false, 'message' => 'Invalid CSRF token'], 403);
        }
    }

    match (true) {

        $method === 'POST' && $uri === '/api/v1/auth/login'
            => $apiController->login(json_decode((string) file_get_contents('php://input'), true) ?: $_POST),

        $method === 'POST' && $uri === '/api/v1/auth/forgot-password'
            => $apiController->forgotPassword(json_decode((string) file_get_contents('php://input'), true) ?: $_POST),

        $method === 'POST' && $uri === '/api/v1/auth/reset-password'
            => $apiController->resetPassword(json_decode((string) file_get_contents('php://input'), true) ?: $_POST),

        $method === 'GET' && $uri === '/api/v1/dashboard/stats'
            => $apiController->dashboardStats(),

        $method === 'GET' && $uri === '/api/v1/dashboard/insights'
            => $apiController->dashboardInsights(),

        $method === 'GET' && $uri === '/api/v1/members'
            => $apiController->listMembers(),

        $method === 'GET' && $uri === '/api/v1/members/stats'
            => $apiController->memberStats(),

        $method === 'POST' && $uri === '/api/v1/members'
            => $apiController->createMember(json_decode((string) file_get_contents('php://input'), true) ?: $_POST),

        $method === 'POST' && $uri === '/api/v1/members/import'
            => $apiController->importMembers(),

        $method === 'PUT' && preg_match('#^/api/v1/members/(\d+)$#', $uri, $m) === 1
            => $apiController->updateMember((int) $m[1], json_decode((string) file_get_contents('php://input'), true) ?: []),

        $method === 'GET' && $uri === '/api/v1/attendance/overview'
            => $apiController->attendanceOverview(),

        $method === 'GET' && $uri === '/api/v1/attendance/snapshots'
            => $apiController->listAttendanceSnapshots(),

        $method === 'POST' && $uri === '/api/v1/attendance/snapshots'
            => $apiController->recordAttendanceSnapshot(json_decode((string) file_get_contents('php://input'), true) ?: $_POST),

        $method === 'POST' && $uri === '/api/v1/attendance/register-guest'
            => $apiController->registerGuest(json_decode((string) file_get_contents('php://input'), true) ?: $_POST),

        $method === 'GET' && $uri === '/api/v1/attendance/guests'
            => $apiController->getGuests(),

        $method === 'GET' && $uri === '/api/v1/assets/overview'
            => $apiController->assetsOverview(),

        $method === 'GET' && $uri === '/api/v1/assets'
            => $apiController->listAssets(),

        $method === 'POST' && $uri === '/api/v1/assets'
            => $apiController->createAsset(json_decode((string) file_get_contents('php://input'), true) ?: $_POST),

        $method === 'PUT' && preg_match('#^/api/v1/assets/(\d+)$#', $uri, $m) === 1
            => $apiController->updateAsset((int) $m[1], json_decode((string) file_get_contents('php://input'), true) ?: []),

        $method === 'GET' && preg_match('#^/api/v1/assets/(\d+)/maintenance$#', $uri, $m) === 1
            => $apiController->listAssetMaintenance((int) $m[1]),

        $method === 'POST' && preg_match('#^/api/v1/assets/(\d+)/maintenance$#', $uri, $m) === 1
            => $apiController->createAssetMaintenance((int) $m[1], json_decode((string) file_get_contents('php://input'), true) ?: $_POST),

        $method === 'POST' && preg_match('#^/api/v1/assets/(\d+)/assign$#', $uri, $m) === 1
            => $apiController->assignAsset((int) $m[1], json_decode((string) file_get_contents('php://input'), true) ?: []),

        $method === 'POST' && preg_match('#^/api/v1/assets/(\d+)/unassign$#', $uri, $m) === 1
            => $apiController->unassignAsset((int) $m[1]),

        $method === 'GET' && preg_match('#^/api/v1/assets/(\d+)/assignments$#', $uri, $m) === 1
            => $apiController->listAssetAssignments((int) $m[1]),

        $method === 'POST' && $uri === '/api/v1/finance/entries'
            => $apiController->createFinanceEntry(json_decode((string) file_get_contents('php://input'), true) ?: $_POST),

        $method === 'GET' && $uri === '/api/v1/meta/users'
            => $apiController->listUsers(),

        $method === 'GET' && $uri === '/api/v1/finance/entries'
            => $apiController->listFinanceEntries(),

        $method === 'GET' && $uri === '/api/v1/finance/categories'
            => $apiController->listFinanceCategories(),

        /* ── Finance Module ── */
        $method === 'GET' && $uri === '/api/v1/finance/accounts'
            => $apiController->listChurchAccounts(),

        $method === 'POST' && $uri === '/api/v1/finance/accounts'
            => $apiController->createChurchAccount(json_decode((string) file_get_contents('php://input'), true) ?: $_POST),

        $method === 'PUT' && preg_match('#^/api/v1/finance/accounts/(\d+)$#', $uri, $m) === 1
            => $apiController->updateChurchAccount((int) $m[1], json_decode((string) file_get_contents('php://input'), true) ?: []),

        $method === 'GET' && $uri === '/api/v1/finance/overview'
            => $apiController->financeOverview(),

        $method === 'GET' && $uri === '/api/v1/finance/entries/filtered'
            => $apiController->financeEntries(),

        $method === 'PUT' && preg_match('#^/api/v1/finance/entries/(\d+)/approve$#', $uri, $m) === 1
            => $apiController->approveFinanceEntry((int) $m[1], json_decode((string) file_get_contents('php://input'), true) ?: []),

        $method === 'GET' && $uri === '/api/v1/finance/pledges'
            => $apiController->listPledges(),

        $method === 'GET' && $uri === '/api/v1/finance/pledges/stats'
            => $apiController->pledgeStats(),

        $method === 'POST' && $uri === '/api/v1/finance/pledges'
            => $apiController->createPledge(json_decode((string) file_get_contents('php://input'), true) ?: $_POST),
        $method === 'POST' && $uri === '/api/v1/finance/pledges/upload'
            => $apiController->importPledges($_POST, $_FILES),

        $method === 'GET' && $uri === '/api/v1/campaigns'
            => $apiController->listCampaigns(),

        $method === 'POST' && $uri === '/api/v1/campaigns'
            => $apiController->createCampaign(json_decode((string) file_get_contents('php://input'), true) ?: $_POST),

        $method === 'GET' && $uri === '/api/v1/finance/budgets'
            => $apiController->listBudgets(),

        $method === 'POST' && $uri === '/api/v1/finance/budgets'
            => $apiController->createBudget(json_decode((string) file_get_contents('php://input'), true) ?: $_POST),

        $method === 'PUT' && preg_match('#^/api/v1/finance/budgets/(\d+)/approve$#', $uri, $m) === 1
            => $apiController->approveBudget((int) $m[1], json_decode((string) file_get_contents('php://input'), true) ?: []),

        $method === 'POST' && preg_match('#^/api/v1/finance/budgets/(\d+)/actual$#', $uri, $m) === 1
            => $apiController->addBudgetActualExpenses((int) $m[1], json_decode((string) file_get_contents('php://input'), true) ?: []),

        $method === 'POST' && preg_match('#^/api/v1/finance/budgets/(\d+)/close$#', $uri, $m) === 1
            => $apiController->closeBudget((int) $m[1], json_decode((string) file_get_contents('php://input'), true) ?: []),

        $method === 'GET' && preg_match('#^/api/v1/finance/budgets/(\d+)/expenses$#', $uri, $m) === 1
            => $apiController->listBudgetExpenses((int) $m[1]),

        $method === 'POST' && preg_match('#^/api/v1/finance/budgets/(\d+)/expenses$#', $uri, $m) === 1
            => $apiController->addBudgetExpense((int) $m[1], json_decode((string) file_get_contents('php://input'), true) ?: []),

        $method === 'DELETE' && preg_match('#^/api/v1/finance/budgets/(\d+)/expenses/(\d+)$#', $uri, $m) === 1
            => $apiController->deleteBudgetExpense((int) $m[1], (int) $m[2]),

        /* ── Settings: Approval Workflows ── */
        $method === 'GET' && $uri === '/api/v1/settings/approval-workflows'
            => $apiController->listApprovalWorkflows(),

        $method === 'POST' && $uri === '/api/v1/settings/approval-workflows'
            => $apiController->saveApprovalWorkflow(json_decode((string) file_get_contents('php://input'), true) ?: []),

        $method === 'DELETE' && preg_match('#^/api/v1/settings/approval-workflows/(\d+)$#', $uri, $m) === 1
            => $apiController->deleteApprovalWorkflow((int) $m[1]),

        $method === 'GET' && $uri === '/api/v1/settings/roles'
            => $apiController->listRolesWithPermissions(),

        $method === 'GET' && $uri === '/api/v1/settings/permissions'
            => $apiController->listPermissions(),

        $method === 'POST' && preg_match('#^/api/v1/settings/roles/(\d+)/permissions$#', $uri, $m) === 1
            => $apiController->updateRolePermissions((int) $m[1], json_decode((string) file_get_contents('php://input'), true) ?: []),

        /* ── Settings: Users CRUD ── */
        $method === 'GET' && $uri === '/api/v1/settings/users'
            => $apiController->listAllUsers(),

        $method === 'POST' && $uri === '/api/v1/settings/users'
            => $apiController->createUser(json_decode((string) file_get_contents('php://input'), true) ?: []),

        $method === 'PUT' && preg_match('#^/api/v1/settings/users/(\d+)$#', $uri, $m) === 1
            => $apiController->updateUser((int) $m[1], json_decode((string) file_get_contents('php://input'), true) ?: []),

        $method === 'DELETE' && preg_match('#^/api/v1/settings/users/(\d+)$#', $uri, $m) === 1
            => $apiController->deleteUser((int) $m[1]),

        /* ── Settings: Church Profile ── */
        $method === 'GET' && $uri === '/api/v1/settings/church-profile'
            => $apiController->getChurchProfile(),

        $method === 'PUT' && $uri === '/api/v1/settings/church-profile'
            => $apiController->updateChurchProfile(json_decode((string) file_get_contents('php://input'), true) ?: []),

        $method === 'POST' && $uri === '/api/v1/settings/church-logo'
            => $apiController->uploadChurchLogo(),

        $method === 'DELETE' && $uri === '/api/v1/settings/church-logo'
            => $apiController->deleteChurchLogo(),

        /* ── Communication / Messaging ── */
        $method === 'POST' && $uri === '/api/v1/messages/send'
            => $apiController->sendMessage(json_decode((string) file_get_contents('php://input'), true) ?: []),

        $method === 'GET' && $uri === '/api/v1/messages'
            => $apiController->listMessages(),

        $method === 'GET' && preg_match('#^/api/v1/messages/(\d+)$#', $uri, $m) === 1
            => $apiController->getMessageDetail((int) $m[1]),

        $method === 'GET' && $uri === '/api/v1/messages/recipients/members'
            => $apiController->listMembersForMessaging(),

        /* ── Unified Reports ── */

        $method === 'GET' && $uri === '/api/v1/reports/dashboard'
            => $apiController->reportsDashboard(),

        $method === 'GET' && $uri === '/api/v1/reports/export/csv'
            => $apiController->exportReportCsv(),

        /* ── Approval History ── */
        $method === 'GET' && preg_match('#^/api/v1/approvals/(\w+)/(\d+)$#', $uri, $m) === 1
            => $apiController->getApprovalHistory($m[1], (int) $m[2]),

        $method === 'GET' && preg_match('#^/api/v1/members/(\d+)/contributions$#', $uri, $m) === 1
            => $apiController->memberContributions((int) $m[1]),

        /* ── Sadaka Module ── */
        $method === 'GET' && $uri === '/api/v1/sadaka/categories'
            => (Auth::check() ? $sadakaController->getCategories() : Response::json(['success' => false, 'message' => 'Unauthenticated'], 401)),

        $method === 'GET' && preg_match('#^/api/v1/sadaka/entries/([\w-]+)$#', $uri, $m) === 1
            => (Auth::check() ? $sadakaController->getEntriesByCategory($m[1], $_GET['month'] ?? null, $_GET['year'] ?? null) : Response::json(['success' => false, 'message' => 'Unauthenticated'], 401)),

        $method === 'POST' && $uri === '/api/v1/sadaka/entries'
            => (Auth::check() ? $sadakaController->addEntry(json_decode((string) file_get_contents('php://input'), true) ?: $_POST) : Response::json(['success' => false, 'message' => 'Unauthenticated'], 401)),

        $method === 'PUT' && preg_match('#^/api/v1/sadaka/entries/(\d+)$#', $uri, $m) === 1
            => (Auth::check() ? $sadakaController->updateEntry((int) $m[1], json_decode((string) file_get_contents('php://input'), true) ?: $_POST) : Response::json(['success' => false, 'message' => 'Unauthenticated'], 401)),

        $method === 'POST' && $uri === '/api/v1/sadaka/upload'
            => (Auth::check() ? $sadakaController->uploadEntries($_POST, $_FILES) : Response::json(['success' => false, 'message' => 'Unauthenticated'], 401)),

        $method === 'DELETE' && preg_match('#^/api/v1/sadaka/entries/(\d+)$#', $uri, $m) === 1
            => (Auth::check() ? $sadakaController->deleteEntry((int) $m[1]) : Response::json(['success' => false, 'message' => 'Unauthenticated'], 401)),

        $method === 'GET' && $uri === '/api/v1/sadaka/statistics'
            => (Auth::check() ? $sadakaController->getStatistics($_GET['year'] ?? null) : Response::json(['success' => false, 'message' => 'Unauthenticated'], 401)),

        $method === 'GET' && preg_match('#^/api/v1/sadaka/report/(\d+)/(\d+)$#', $uri, $m) === 1
            => (Auth::check() ? $sadakaController->getReport((int) $m[1], (int) $m[2]) : Response::json(['success' => false, 'message' => 'Unauthenticated'], 401)),

        $method === 'POST' && $uri === '/api/v1/ai/query'
            => $aiController->query(json_decode((string) file_get_contents('php://input'), true) ?: $_POST),

        $method === 'GET' && $uri === '/api/v1/ai/summary'
            => $aiController->summary(),

        default => $apiController->notFound(),
    };
    exit;
}

// ────── Web routes ──────

// POST: login
if ($method === 'POST' && $uri === '/login') {
    // CSRF validation
    $csrfToken = (string) ($_POST['_csrf_token'] ?? '');
    if (!Auth::validateCsrfToken($csrfToken)) {
        $pageController->loginPage('Invalid security token. Please try again.');
        exit;
    }

    $email    = trim((string) ($_POST['email'] ?? ''));
    $phone    = trim((string) ($_POST['phone'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    // Determine identifier (email primary, phone fallback)
    $identifier = $email !== '' ? $email : $phone;
    if ($identifier === '' || $password === '') {
        $pageController->loginPage('Email and password are required.');
        exit;
    }

    // Brute-force check
    $bruteCheck = Auth::checkLoginAllowed($pdo, $identifier);
    if (!$bruteCheck['allowed']) {
        $pageController->loginPage('Too many login attempts. Please try again in ' . ceil($bruteCheck['retry_after'] / 60) . ' minute(s).');
        exit;
    }

    // Look up by email or phone — strict validation per mode
    if ($email !== '') {
        // Email mode: must be a valid email address
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $pageController->loginPage('Please enter a valid email address.');
            exit;
        }
        $stmt = $pdo->prepare(
            'SELECT u.id, u.full_name, u.password_hash, u.role_id, r.name AS role_name
             FROM users u INNER JOIN roles r ON r.id = u.role_id
             WHERE u.email = :email AND u.is_active = 1 LIMIT 1'
        );
        $stmt->execute([':email' => $email]);
    } elseif ($phone !== '') {
        // Phone mode: must contain only digits, +, spaces, dashes
        if (!preg_match('/^[+0-9\s\-]+$/', $phone)) {
            $pageController->loginPage('Please enter a valid phone number.');
            exit;
        }
        $stmt = $pdo->prepare(
            'SELECT u.id, u.full_name, u.password_hash, u.role_id, r.name AS role_name
             FROM users u INNER JOIN roles r ON r.id = u.role_id
             WHERE u.phone = :phone AND u.is_active = 1 LIMIT 1'
        );
        $stmt->execute([':phone' => $phone]);
    } else {
        $pageController->loginPage('Email or phone number is required.');
        exit;
    }
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password_hash'])) {
        Auth::recordLoginAttempt($pdo, $identifier);
        $remaining = $bruteCheck['remaining'] - 1;
        $msg = 'Invalid email or password.';
        if ($remaining <= 2 && $remaining > 0) {
            $msg .= " {$remaining} attempt(s) remaining.";
        }
        $pageController->loginPage($msg);
        exit;
    }

    // Success — clear attempts and update last login
    Auth::clearLoginAttempts($pdo, $identifier);
    $pdo->prepare('UPDATE users SET last_login_at = NOW() WHERE id = :id')->execute([':id' => $user['id']]);

    // Load permissions for this role
    try {
        $permStmt = $pdo->prepare(
            'SELECT p.name FROM permissions p
             INNER JOIN role_permissions rp ON rp.permission_id = p.id
             WHERE rp.role_id = :rid'
        );
        $permStmt->execute([':rid' => (int) $user['role_id']]);
        $user['permissions'] = $permStmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (Throwable $e) {
        $user['permissions'] = [];
    }

    Auth::login($user);
    Response::redirect('/');
    exit;
}

// POST: logout
if ($method === 'POST' && $uri === '/logout') {
    Auth::logout();
    header('Location: ' . Route::get('login'));
    exit;
}

// ────── Forgot / Reset Password (public pages) ──────
if ($method === 'GET' && $uri === '/forgot-password') {
    $pageController->forgotPasswordPage();
    exit;
}
if ($method === 'POST' && $uri === '/forgot-password') {
    $csrfToken = (string) ($_POST['_csrf_token'] ?? '');
    if (!Auth::validateCsrfToken($csrfToken)) {
        $pageController->forgotPasswordPage('Invalid security token. Please try again.');
        exit;
    }
    $email = trim((string) ($_POST['email'] ?? ''));
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $pageController->forgotPasswordPage('A valid email address is required.');
        exit;
    }
    // Look up user by email
    $stmt = $pdo->prepare('SELECT id, full_name FROM users WHERE email = :email AND is_active = 1 LIMIT 1');
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch();
    if (!$user) {
        // Generic message to avoid user enumeration
        $pageController->forgotPasswordPage(null, 'If an account exists with that email, a reset link has been sent.');
        exit;
    }
    // Generate secure token (no 6-digit code)
    $token = bin2hex(random_bytes(32));
    $expiresAt = date('Y-m-d H:i:s', strtotime('+15 minutes'));
    // Remove any old tokens for this user
    $pdo->prepare('DELETE FROM password_reset_tokens WHERE user_id = :uid')->execute([':uid' => $user['id']]);
    // Insert new token
    $pdo->prepare(
        'INSERT INTO password_reset_tokens (user_id, token, code_hash, expires_at, created_at)
         VALUES (:uid, :tok, :hash, :exp, NOW())'
    )->execute([':uid' => $user['id'], ':tok' => $token, ':hash' => '', ':exp' => $expiresAt]);

    // In production, send reset link via email. For now, redirect to reset page.
    header('Location: ' . Route::get('reset_password') . '?token=' . urlencode($token));
    exit;
}

if ($method === 'GET' && $uri === '/reset-password') {
    $token = trim((string) ($_GET['token'] ?? ''));
    if ($token === '') { header('Location: ' . Route::get('login')); exit; }
    $pageController->resetPasswordPage($token);
    exit;
}
if ($method === 'POST' && $uri === '/reset-password') {
    $csrfToken = (string) ($_POST['_csrf_token'] ?? '');
    if (!Auth::validateCsrfToken($csrfToken)) {
        $pageController->resetPasswordPage((string) ($_POST['token'] ?? ''), 'Invalid security token. Please try again.');
        exit;
    }
    $token   = trim((string) ($_POST['token'] ?? ''));
    $newPass = (string) ($_POST['password'] ?? '');
    $confirm = (string) ($_POST['password_confirm'] ?? '');

    if ($token === '' || $newPass === '') {
        $pageController->resetPasswordPage($token, 'All fields are required.');
        exit;
    }
    if ($newPass !== $confirm) {
        $pageController->resetPasswordPage($token, 'Passwords do not match.');
        exit;
    }
    if (mb_strlen($newPass) < 8) {
        $pageController->resetPasswordPage($token, 'Password must be at least 8 characters.');
        exit;
    }
    // Validate token
    $stmt = $pdo->prepare(
        'SELECT prt.id, prt.user_id, prt.expires_at
         FROM password_reset_tokens prt
         WHERE prt.token = :tok LIMIT 1'
    );
    $stmt->execute([':tok' => $token]);
    $resetRow = $stmt->fetch();
    if (!$resetRow || strtotime($resetRow['expires_at']) < time()) {
        $pdo->prepare('DELETE FROM password_reset_tokens WHERE token = :tok')->execute([':tok' => $token]);
        $pageController->resetPasswordPage('', 'Reset link has expired. Please request a new one.');
        exit;
    }
    // Update password
    $hash = password_hash($newPass, PASSWORD_DEFAULT);
    $pdo->prepare('UPDATE users SET password_hash = :hash, updated_at = NOW() WHERE id = :uid')
        ->execute([':hash' => $hash, ':uid' => $resetRow['user_id']]);
    // Delete used token
    $pdo->prepare('DELETE FROM password_reset_tokens WHERE id = :id')->execute([':id' => $resetRow['id']]);

    $pageController->loginPage('Password reset successfully. Please sign in.');
    exit;
}

// Auth guard for all other web pages
$publicPages = ['/login', '/forgot-password', '/reset-password'];
if (!in_array($uri, $publicPages, true) && !Auth::check()) {
    header('Location: ' . Route::get('login'));
    exit;
}

// GET: login page
if ($method === 'GET' && $uri === '/login') {
    if (Auth::check()) {
        header('Location: ' . Route::get('home'));
        exit;
    }
    
    // Load church branding consistently for both initial load and failed login render
    $brand = Response::loadChurchBranding();
    $churchLogo = $brand['church_logo'] ?? '';
    $baseUrl = BASE_URL;
    $churchName = $brand['church_name'] ?? 'Church CMS';
    $error = null;
    
    require __DIR__ . '/../app/views/pages/login.php';
    exit;
}

// GET: dashboard
if ($method === 'GET' && $uri === '/') {
    $pageController->dashboard();
    exit;
}

// GET: assets module page (separate slug to avoid collision with static /assets folder)
if ($method === 'GET' && $uri === '/asset-center') {
    $pageController->module('assets');
    exit;
}

// GET: module pages
$webModules = ['members', 'guests', 'attendance', 'finance', 'assets', 'communication', 'ai', 'reports', 'settings', 'sadaka'];
$trimmed = ltrim($uri, '/');
if ($method === 'GET' && in_array($trimmed, $webModules, true)) {
    $pageController->module($trimmed);
    exit;
}



// 404 fallback
http_response_code(404);
$pageController->module('404');
exit;
