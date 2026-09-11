<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Response;
use App\Services\StatisticsService;
use PDO;

final class PageController
{
    public function __construct(private PDO $pdo)
    {
    }

    public function loginPage(?string $error = null): void
    {
        $brand = Response::loadChurchBranding();
        Response::view('pages/login.php', [
            'title' => $brand['church_name'] . ' Login',
            'page'  => 'login',
            'error' => $error,
        ]);
    }

    public function dashboard(): void
    {
        // Single source of truth shared by dashboard, reports and AI.
        $stats = (new StatisticsService($this->pdo))->dashboard();
        $themeVerse = $this->resolveThemeVerse();
        $brand = Response::loadChurchBranding();
        Response::view('pages/dashboard.php', [
            'title' => $brand['church_name'] . ' Dashboard',
            'page'  => 'dashboard',
            'stats' => $stats,
            'themeVerse' => $themeVerse,
        ]);
    }

    public function eventDetails(int $eventId): void
    {
        http_response_code(410);
        Response::view('pages/404.php', ['title' => 'Out of Scope', 'page' => '404']);
    }

    public function departmentDetail(int $deptId): void
    {
        http_response_code(410);
        Response::view('pages/404.php', ['title' => 'Out of Scope', 'page' => '404']);
    }

    public function module(string $module): void
    {
        $allowed = ['members', 'guests', 'events', 'attendance', 'finance', 'assets', 'communication', 'reports', 'settings', 'sadaka'];
        if (!in_array($module, $allowed, true)) {
            Response::view('pages/404.php', ['title' => 'Not Found', 'page' => '404']);
            return;
        }

        // Permission guard: map each module to the required permission
        $modulePermissions = [
            'members'       => 'members.view',
            'guests'        => 'members.view',
            'events'        => 'events.view',
            'attendance'    => 'attendance.view',
            'finance'       => 'finance.view',
            'assets'        => 'assets.view',
            'communication' => 'communication.view',
            'reports'       => 'reports.view',
            'settings'      => 'settings.manage',
            'sadaka'        => 'finance.view',
        ];
        if (isset($modulePermissions[$module]) && !Auth::can($modulePermissions[$module])) {
            http_response_code(403);
            Response::view('pages/403.php', [
                'title'      => 'Access Denied',
                'page'       => $module,
                'themeVerse' => $this->resolveThemeVerse(),
            ]);
            return;
        }

        $titles = [
            'members'       => 'Members',
            'guests'        => 'Guests',
            'events'        => 'Events / Ibada',
            'attendance'    => 'Attendance',
            'finance'       => 'Church Giving',
            'assets'        => 'Assets',
            'communication' => 'Communication',
            'reports'       => 'Reports',
            'settings'      => 'Settings',
            'sadaka'        => 'Giving Details',
        ];

        Response::view('pages/' . $module . '.php', [
            'title' => $titles[$module] ?? ucfirst($module),
            'page'  => $module,
            'themeVerse' => $this->resolveThemeVerse(),
        ]);
    }

    private function resolveThemeVerse(): array
    {
        $themeVerse = [
            'reference' => '1 Wakorintho 14:40',
            'verse' => 'Mambo yote na yatendeke kwa uzuri na kwa utaratibu.',
        ];

        try {
            $verseStmt = $this->pdo->query(
                "SELECT verse_reference, verse_text
                 FROM theme_verses
                 WHERE is_active = 1
                   AND (start_date IS NULL OR start_date <= CURRENT_DATE)
                   AND (end_date IS NULL OR end_date >= CURRENT_DATE)
                 ORDER BY RAND()
                 LIMIT 1"
            );
            $row = $verseStmt ? $verseStmt->fetch() : false;
            if ($row && !empty($row['verse_text'])) {
                $themeVerse = [
                    'reference' => (string) ($row['verse_reference'] ?? ''),
                    'verse' => (string) $row['verse_text'],
                ];
            }
        } catch (\Throwable $e) {
            // Keep fallback verse when migration has not yet been applied.
        }

        return $themeVerse;
    }

    public function forgotPasswordPage(?string $error = null, ?string $success = null): void
    {
        Response::view('pages/forgot_password.php', [
            'title'   => 'Forgot Password',
            'page'    => 'login',
            'error'   => $error,
            'success' => $success,
        ]);
    }

    public function resetPasswordPage(string $token = '', ?string $error = null): void
    {
        Response::view('pages/reset_password.php', [
            'title' => 'Reset Password',
            'page'  => 'login',
            'token' => $token,
            'error' => $error,
        ]);
    }
}
