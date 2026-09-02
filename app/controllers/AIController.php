<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Audit;
use App\Core\Response;
use PDO;

final class AIController
{
    public function __construct(private PDO $pdo) {}

    public function query(array $input): void
    {
        if (!Auth::can('reports.view')) {
            Response::json(['success' => false, 'message' => 'Forbidden'], 403);
        }

        $question = trim((string)($input['question'] ?? ''));
        if ($question === '') {
            Response::json(['success' => false, 'message' => 'Question is required'], 422);
        }
        if (mb_strlen($question) > 500) {
            Response::json(['success' => false, 'message' => 'Question is too long'], 422);
        }

        $answer = $this->answerFromApprovedFunctions($question);
        $user = Auth::user();
        $actorId = $user ? (int)$user['id'] : null;
        $this->ensureLogTable();
        $this->pdo->prepare('INSERT INTO ai_logs (user_id, question, answer, created_at) VALUES (:uid,:q,:a,NOW())')
            ->execute([':uid' => $actorId, ':q' => $question, ':a' => $answer]);
        if ($actorId) {
            Audit::log($this->pdo, $actorId, 'ai', 'query', 'ai_logs', (int)$this->pdo->lastInsertId(), null, ['question' => $question], 'AI administrative query');
        }
        Response::json(['success' => true, 'data' => ['answer' => $answer, 'mode' => 'controlled-data-assistant']]);
    }

    public function summary(): void
    {
        if (!Auth::can('reports.view')) {
            Response::json(['success' => false, 'message' => 'Forbidden'], 403);
        }
        $answer = $this->buildSummary();
        Response::json(['success' => true, 'data' => ['answer' => $answer]]);
    }

    private function answerFromApprovedFunctions(string $q): string
    {
        $l = mb_strtolower($q);
        if (str_contains($l, 'member')) {
            $total = (int)$this->pdo->query("SELECT COUNT(*) FROM members")->fetchColumn();
            $active = (int)$this->pdo->query("SELECT COUNT(*) FROM members WHERE member_status='active'")->fetchColumn();
            return "The church currently has {$total} registered members, of whom {$active} are active.";
        }
        if (str_contains($l, 'guest')) {
            $this->ensureGuestsTable();
            $total = (int)$this->pdo->query("SELECT COUNT(*) FROM guests")->fetchColumn();
            $returning = (int)$this->pdo->query("SELECT COUNT(*) FROM guests WHERE visit_type='returning'")->fetchColumn();
            $follow = (int)$this->pdo->query("SELECT COUNT(*) FROM guests WHERE follow_up_date IS NOT NULL AND follow_up_date <= CURRENT_DATE AND status NOT IN ('converted','inactive')")->fetchColumn();
            return "There are {$total} registered guests. {$returning} are marked as returning guests, and {$follow} currently require follow-up.";
        }
        if (str_contains($l, 'attendance')) {
            $this->ensureAttendanceTable();
            $total = (int)$this->pdo->query("SELECT COALESCE(SUM(total_count),0) FROM attendance_snapshots WHERE DATE_FORMAT(service_date,'%Y-%m')=DATE_FORMAT(CURRENT_DATE,'%Y-%m')")->fetchColumn();
            return "Recorded attendance for the current month totals {$total} people across the available attendance snapshots.";
        }
        if (str_contains($l, 'giving') || str_contains($l, 'offering') || str_contains($l, 'tithe')) {
            $income = (float)$this->pdo->query("SELECT COALESCE(SUM(fe.amount),0) FROM finance_entries fe INNER JOIN finance_categories fc ON fc.id=fe.category_id WHERE fc.category_type='income' AND DATE_FORMAT(fe.entry_date,'%Y-%m')=DATE_FORMAT(CURRENT_DATE,'%Y-%m')")->fetchColumn();
            return 'Recorded church giving/income for the current month is TZS ' . number_format($income, 2) . '.';
        }
        if (str_contains($l, 'asset')) {
            $total = (int)$this->pdo->query("SELECT COUNT(*) FROM assets")->fetchColumn();
            return "The asset register contains {$total} recorded assets.";
        }
        if (str_contains($l, 'report') || str_contains($l, 'summary')) {
            return $this->buildSummary();
        }
        return "I can answer questions about members, guests, attendance, church giving, assets, and administrative summaries. Please ask a question about one of those areas.";
    }

    private function buildSummary(): string
    {
        $members = (int)$this->pdo->query("SELECT COUNT(*) FROM members WHERE member_status='active'")->fetchColumn();
        $this->ensureGuestsTable();
        $guests = (int)$this->pdo->query("SELECT COUNT(*) FROM guests")->fetchColumn();
        $this->ensureAttendanceTable();
        $attendance = (int)$this->pdo->query("SELECT COALESCE(SUM(total_count),0) FROM attendance_snapshots WHERE DATE_FORMAT(service_date,'%Y-%m')=DATE_FORMAT(CURRENT_DATE,'%Y-%m')")->fetchColumn();
        $income = (float)$this->pdo->query("SELECT COALESCE(SUM(fe.amount),0) FROM finance_entries fe INNER JOIN finance_categories fc ON fc.id=fe.category_id WHERE fc.category_type='income' AND DATE_FORMAT(fe.entry_date,'%Y-%m')=DATE_FORMAT(CURRENT_DATE,'%Y-%m')")->fetchColumn();
        $assets = (int)$this->pdo->query("SELECT COUNT(*) FROM assets")->fetchColumn();
        return "Administrative summary: {$members} active members, {$guests} registered guests, {$attendance} recorded attendance for the current month, TZS " . number_format($income, 2) . " in recorded church income/giving for the current month, and {$assets} registered assets. These figures are generated from approved application data functions.";
    }

    private function ensureGuestsTable(): void
    {
        $this->pdo->exec('CREATE TABLE IF NOT EXISTS guests (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, guest_code VARCHAR(50) NOT NULL UNIQUE, first_name VARCHAR(100) NOT NULL, last_name VARCHAR(100) NOT NULL, phone VARCHAR(30) NOT NULL, location VARCHAR(255) NOT NULL, email VARCHAR(150) NULL, age_group VARCHAR(30) NULL, visit_type VARCHAR(30) NOT NULL DEFAULT "first_time", invited_by_name VARCHAR(100) NULL, service_date DATE NOT NULL, follow_up_date DATE NULL, notes TEXT NULL, status VARCHAR(30) NOT NULL DEFAULT "registered", created_by BIGINT UNSIGNED NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, INDEX idx_guests_phone(phone), INDEX idx_guests_service_date(service_date)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
    }
    private function ensureAttendanceTable(): void
    {
        $this->pdo->exec('CREATE TABLE IF NOT EXISTS attendance_snapshots (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, service_date DATE NOT NULL, service_name VARCHAR(150) NOT NULL, service_type VARCHAR(40) NOT NULL DEFAULT "sunday_service", men_count INT UNSIGNED DEFAULT 0, women_count INT UNSIGNED DEFAULT 0, children_count INT UNSIGNED DEFAULT 0, youth_count INT UNSIGNED DEFAULT 0, guests_count INT UNSIGNED DEFAULT 0, total_count INT UNSIGNED DEFAULT 0, notes VARCHAR(255) NULL, created_by BIGINT UNSIGNED NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, INDEX idx_attendance_date(service_date)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
    }
    private function ensureLogTable(): void
    {
        $this->pdo->exec('CREATE TABLE IF NOT EXISTS ai_logs (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, user_id BIGINT UNSIGNED NULL, question TEXT NOT NULL, answer TEXT NOT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, INDEX idx_ai_logs_user(user_id), INDEX idx_ai_logs_created(created_at)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
    }
}
