<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Response;
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
        $members = (int) $this->pdo->query("SELECT COUNT(*) FROM members WHERE member_status = 'active'")->fetchColumn();
        $income  = (float) $this->pdo->query(
            "SELECT COALESCE(SUM(fe.amount),0) FROM finance_entries fe
             INNER JOIN finance_categories fc ON fc.id = fe.category_id
             WHERE fc.category_type='income' AND DATE_FORMAT(fe.entry_date,'%Y-%m') = DATE_FORMAT(CURRENT_DATE,'%Y-%m')"
        )->fetchColumn();
        $expenses = (float) $this->pdo->query(
            "SELECT COALESCE(SUM(fe.amount),0) FROM finance_entries fe
             INNER JOIN finance_categories fc ON fc.id = fe.category_id
             WHERE fc.category_type='expense' AND DATE_FORMAT(fe.entry_date,'%Y-%m') = DATE_FORMAT(CURRENT_DATE,'%Y-%m')"
        )->fetchColumn();
        $guests = 0;
        try { $guests = (int) $this->pdo->query("SELECT COUNT(*) FROM guests")->fetchColumn(); } catch (\Throwable $e) {}
        $attendance = 0;
        try { $attendance = (int) $this->pdo->query("SELECT COALESCE(SUM(total_count),0) FROM attendance_snapshots WHERE DATE_FORMAT(service_date,'%Y-%m') = DATE_FORMAT(CURRENT_DATE,'%Y-%m')")->fetchColumn(); } catch (\Throwable $e) {}
        $themeVerse = $this->resolveThemeVerse();

        $brand = Response::loadChurchBranding();
        Response::view('pages/dashboard.php', [
            'title' => $brand['church_name'] . ' Dashboard',
            'page'  => 'dashboard',
            'stats' => compact('members', 'guests', 'attendance', 'income', 'expenses'),
            'themeVerse' => $themeVerse,
        ]);
    }

    public function eventDetails(int $eventId): void
    {
        Response::view('pages/event_details.php', [
            'title' => 'Event Details',
            'page' => 'events',
            'eventId' => $eventId,
            'themeVerse' => $this->resolveThemeVerse(),
        ]);
    }

    public function departmentDetail(int $deptId): void
    {
        Response::view('pages/department_view.php', [
            'title' => 'Department Detail',
            'page'  => 'departments',
            'deptId' => $deptId,
            'themeVerse' => $this->resolveThemeVerse(),
        ]);
    }

    public function module(string $module): void
    {
        $allowed = ['members', 'guests', 'attendance', 'finance', 'assets', 'communication', 'ai', 'reports', 'settings', 'sadaka'];
        if (!in_array($module, $allowed, true)) {
            Response::view('pages/404.php', ['title' => 'Not Found', 'page' => '404']);
            return;
        }

        // Permission guard: map each module to the required permission
        $modulePermissions = [
            'members'       => 'members.view',
            'guests'        => 'members.view',
            'attendance'    => 'attendance.view',
            'finance'       => 'finance.view',
            'assets'        => 'assets.view',
            'communication' => 'communication.view',
            'ai'            => 'reports.view',
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
            'attendance'    => 'Attendance',
            'finance'       => 'Church Giving',
            'assets'        => 'Assets',
            'communication' => 'Communication',
            'ai'            => 'AI Assistant',
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
