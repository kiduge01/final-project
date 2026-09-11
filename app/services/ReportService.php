<?php

declare(strict_types=1);

namespace App\Services;

use PDO;

final class ReportService
{
    public function __construct(private PDO $pdo) {}

    public function attendance(string $start, string $end): array
    {
        return (new AttendanceService($this->pdo))->summaryBetween($start, $end);
    }

    public function giving(string $start, string $end): array
    {
        $stmt = $this->pdo->prepare("SELECT fc.name category, COALESCE(SUM(fe.amount),0) amount FROM finance_entries fe INNER JOIN finance_categories fc ON fc.id=fe.category_id WHERE fc.category_type='income' AND fe.entry_date BETWEEN :s AND :e GROUP BY fc.id,fc.name ORDER BY amount DESC");
        $stmt->execute([':s'=>$start, ':e'=>$end]);
        $rows = $stmt->fetchAll();
        $total = array_sum(array_map(fn($r)=>(float)$r['amount'],$rows));
        return ['total'=>$total,'categories'=>$rows];
    }

    public function churchSummary(string $start, string $end): array
    {
        $stats = (new StatisticsService($this->pdo))->dashboard($start,$end);
        $stats['attendance_breakdown'] = $this->attendance($start,$end);
        $stats['giving_breakdown'] = $this->giving($start,$end);
        return $stats;
    }
}
