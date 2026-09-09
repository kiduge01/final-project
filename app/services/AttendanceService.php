<?php

declare(strict_types=1);

namespace App\Services;

use PDO;

final class AttendanceService
{
    public function __construct(private PDO $pdo) {}

    public function listBetween(string $start, string $end): array
    {
        $stmt = $this->pdo->prepare('SELECT id, service_date, service_name, service_type, session_type, men_count, women_count, children_count, youth_count, guests_count, total_count, notes FROM attendance_snapshots WHERE service_date BETWEEN :start AND :end ORDER BY service_date ASC, id ASC');
        $stmt->execute([':start' => $start, ':end' => $end]);
        return $stmt->fetchAll();
    }

    public function servicesOnDate(string $date): array
    {
        $stmt = $this->pdo->prepare('SELECT id, service_date, service_name, service_type, session_type, men_count, women_count, children_count, youth_count, guests_count, total_count, notes FROM attendance_snapshots WHERE service_date = :d ORDER BY id ASC');
        $stmt->execute([':d' => $date]);
        return $stmt->fetchAll();
    }

    public function summaryBetween(string $start, string $end): array
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) services, COALESCE(SUM(total_count),0) total, COALESCE(SUM(men_count),0) men, COALESCE(SUM(women_count),0) women, COALESCE(SUM(children_count),0) children, COALESCE(SUM(youth_count),0) youth, COALESCE(SUM(guests_count),0) guests, COALESCE(AVG(total_count),0) average FROM attendance_snapshots WHERE service_date BETWEEN :start AND :end');
        $stmt->execute([':start' => $start, ':end' => $end]);
        return $stmt->fetch() ?: [];
    }
}
