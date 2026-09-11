<?php

declare(strict_types=1);

namespace App\Services;

use PDO;

final class StatisticsService
{
    public function __construct(private PDO $pdo) {}

    public function dashboard(?string $start = null, ?string $end = null): array
    {
        $start ??= date('Y-m-01');
        $end ??= date('Y-m-t');
        $members = (int)$this->pdo->query("SELECT COUNT(*) FROM members WHERE member_status='active'")->fetchColumn();
        $guests = $this->safeCount('guests');
        $assets = $this->safeCount('assets');
        $att = new AttendanceService($this->pdo);
        $attendance = (int)($att->summaryBetween($start, $end)['total'] ?? 0);

        $stmt = $this->pdo->prepare("SELECT fc.category_type, COALESCE(SUM(fe.amount),0) amount FROM finance_entries fe INNER JOIN finance_categories fc ON fc.id=fe.category_id WHERE fe.entry_date BETWEEN :s AND :e GROUP BY fc.category_type");
        $stmt->execute([':s'=>$start, ':e'=>$end]);
        $income = 0.0; $expenses = 0.0;
        foreach ($stmt->fetchAll() as $r) {
            if ($r['category_type']==='income') $income=(float)$r['amount'];
            if ($r['category_type']==='expense') $expenses=(float)$r['amount'];
        }
        return compact('members','guests','attendance','income','expenses','assets','start','end');
    }

    private function safeCount(string $table): int
    {
        try { return (int)$this->pdo->query("SELECT COUNT(*) FROM {$table}")->fetchColumn(); } catch (\Throwable) { return 0; }
    }
}
