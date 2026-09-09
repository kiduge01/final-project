<?php

declare(strict_types=1);

namespace App\Services;

use PDO;

final class ReportComparisonService
{
    public function __construct(private PDO $pdo) {}

    public function build(string $start, string $end): array
    {
        [$prevStart, $prevEnd] = $this->previousRange($start, $end);
        $current = $this->snapshot($start, $end);
        $previous = $this->snapshot($prevStart, $prevEnd);

        $changes = [];
        foreach (['attendance','attendance_average','guests_period','giving','new_members'] as $key) {
            $changes[$key] = $this->change((float)($current[$key] ?? 0), (float)($previous[$key] ?? 0));
        }

        $trend = $this->describe($current, $previous, $changes, $start, $end, $prevStart, $prevEnd);
        return [
            'range' => ['start'=>$start,'end'=>$end],
            'comparison_range' => ['start'=>$prevStart,'end'=>$prevEnd],
            'current' => $current,
            'previous' => $previous,
            'changes' => $changes,
            'trend_summary' => $trend,
        ];
    }

    private function snapshot(string $start, string $end): array
    {
        $stats = (new StatisticsService($this->pdo))->dashboard($start, $end);
        $attSvc = new AttendanceService($this->pdo);
        $att = $attSvc->summaryBetween($start, $end);
        $rows = $attSvc->listBetween($start, $end);
        $highest = null;
        foreach ($rows as $row) {
            if ($highest === null || (int)$row['total_count'] > (int)$highest['total_count']) $highest = $row;
        }

        $guestsPeriod = $this->scalarSafe(
            'SELECT COUNT(*) FROM guests WHERE service_date BETWEEN :s AND :e', [':s'=>$start, ':e'=>$end]
        );
        $newMembers = $this->scalarSafe(
            'SELECT COUNT(*) FROM members WHERE join_date BETWEEN :s AND :e', [':s'=>$start, ':e'=>$end]
        );

        return [
            'members_active' => (int)($stats['members'] ?? 0),
            'new_members' => $newMembers,
            'guests_total' => (int)($stats['guests'] ?? 0),
            'guests_period' => $guestsPeriod,
            'attendance' => (int)($att['total'] ?? 0),
            'attendance_services' => (int)($att['services'] ?? 0),
            'attendance_average' => round((float)($att['average'] ?? 0), 1),
            'attendance_breakdown' => [
                'men'=>(int)($att['men']??0),'women'=>(int)($att['women']??0),'children'=>(int)($att['children']??0),
                'youth'=>(int)($att['youth']??0),'guests'=>(int)($att['guests']??0),
            ],
            'highest_attendance' => $highest ? [
                'service_name'=>(string)$highest['service_name'], 'service_date'=>(string)$highest['service_date'], 'total'=>(int)$highest['total_count']
            ] : null,
            'giving' => round((float)($stats['income'] ?? 0), 2),
            'expenses' => round((float)($stats['expenses'] ?? 0), 2),
            'assets' => (int)($stats['assets'] ?? 0),
            'attendance_rows' => $rows,
            'giving_breakdown' => (new ReportService($this->pdo))->giving($start,$end)['categories'] ?? [],
        ];
    }

    private function previousRange(string $start, string $end): array
    {
        $s = new \DateTimeImmutable($start);
        $e = new \DateTimeImmutable($end);
        $days = (int)$s->diff($e)->days + 1;
        $prevEnd = $s->modify('-1 day');
        $prevStart = $prevEnd->modify('-'.($days-1).' days');
        return [$prevStart->format('Y-m-d'), $prevEnd->format('Y-m-d')];
    }

    private function change(float $current, float $previous): array
    {
        $diff = $current - $previous;
        $percent = $previous == 0.0 ? ($current == 0.0 ? 0.0 : null) : round(($diff / $previous) * 100, 1);
        return ['current'=>$current,'previous'=>$previous,'difference'=>round($diff,2),'percent'=>$percent];
    }

    private function describe(array $c, array $p, array $ch, string $s, string $e, string $ps, string $pe): string
    {
        $parts = [];
        $parts[] = "Comparison for {$s} to {$e} against {$ps} to {$pe}.";
        $parts[] = $this->sentence('Attendance', $ch['attendance'], 'attendees');
        $parts[] = $this->sentence('Average attendance per Ibada', $ch['attendance_average'], 'people');
        $parts[] = $this->sentence('Guest visits', $ch['guests_period'], 'guests');
        $parts[] = $this->sentence('Church giving', $ch['giving'], 'TZS');
        $parts[] = $this->sentence('New members', $ch['new_members'], 'members');
        if (!empty($c['highest_attendance'])) {
            $h=$c['highest_attendance'];
            $parts[] = "The highest recorded attendance in the selected period was {$h['total']} at {$h['service_name']} on {$h['service_date']}.";
        }
        return implode(' ', array_filter($parts));
    }

    private function sentence(string $label, array $x, string $unit): string
    {
        $cur = $x['current']; $prev = $x['previous']; $pct = $x['percent'];
        if ($cur == $prev) return "{$label} was unchanged at ".number_format($cur,1)." {$unit}.";
        $dir = $cur > $prev ? 'increased' : 'decreased';
        if ($pct === null) return "{$label} {$dir} from ".number_format($prev,1)." to ".number_format($cur,1)." {$unit}.";
        return "{$label} {$dir} by ".abs($pct)."%, from ".number_format($prev,1)." to ".number_format($cur,1)." {$unit}.";
    }

    private function scalarSafe(string $sql, array $params): int
    {
        try { $st=$this->pdo->prepare($sql); $st->execute($params); return (int)$st->fetchColumn(); } catch (\Throwable) { return 0; }
    }
}
