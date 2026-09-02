<?php
/**
 * Attendance Report Generator
 * 
 * Provides attendance statistics and analytics for department reporting.
 */

require_once __DIR__ . '/../includes/auth_check.php';

$pdo = require __DIR__ . '/../includes/db.php';
$departmentId = getCurrentDepartmentId();

// Get report ID from query string (optional)
$reportId = $_GET['report_id'] ?? null;
$startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('first day of this month'));
$endDate = $_GET['end_date'] ?? date('Y-m-d');

$attendanceData = [];
$summary = [];
$error = '';

try {
    // Fetch attendance records for the date range, filtered by department members
    $stmt = $pdo->prepare('
        SELECT 
            ar.attendance_status,
            COUNT(DISTINCT ar.service_id) as service_count,
            COUNT(ar.member_id) as record_count,
            COUNT(DISTINCT ar.member_id) as unique_members
        FROM attendance_records ar
        JOIN services s ON ar.service_id = s.id
        INNER JOIN department_members dm ON ar.member_id = dm.member_id
        WHERE s.date >= ? AND s.date <= ? AND dm.department_id = ?
        GROUP BY ar.attendance_status
    ');
    $stmt->execute([$startDate, $endDate, $departmentId]);
    $statusBreakdown = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Calculate summary statistics
    $totalRecords = 0;
    $presentCount = 0;
    $absentCount = 0;
    $lateCount = 0;
    $excusedCount = 0;

    foreach ($statusBreakdown as $row) {
        $totalRecords += $row['record_count'];
        
        switch ($row['attendance_status']) {
            case 'present':
                $presentCount = $row['record_count'];
                break;
            case 'absent':
                $absentCount = $row['record_count'];
                break;
            case 'late':
                $lateCount = $row['record_count'];
                break;
            case 'excused':
                $excusedCount = $row['record_count'];
                break;
        }
    }

    $summary = [
        'total_records' => $totalRecords,
        'present' => $presentCount,
        'absent' => $absentCount,
        'late' => $lateCount,
        'excused' => $excusedCount,
        'present_percentage' => $totalRecords > 0 ? round(($presentCount / $totalRecords) * 100, 2) : 0,
        'absent_percentage' => $totalRecords > 0 ? round(($absentCount / $totalRecords) * 100, 2) : 0,
        'late_percentage' => $totalRecords > 0 ? round(($lateCount / $totalRecords) * 100, 2) : 0,
        'excused_percentage' => $totalRecords > 0 ? round(($excusedCount / $totalRecords) * 100, 2) : 0,
    ];

    // Fetch weekly attendance trends for department members
    $trendStmt = $pdo->prepare('
        SELECT 
            DATE(s.date) as service_date,
            WEEK(s.date) as week_number,
            COUNT(ar.member_id) as attendance_count,
            COUNT(CASE WHEN ar.attendance_status = "present" THEN 1 END) as present_count,
            COUNT(CASE WHEN ar.attendance_status = "absent" THEN 1 END) as absent_count,
            COUNT(CASE WHEN ar.attendance_status = "late" THEN 1 END) as late_count
        FROM attendance_records ar
        JOIN services s ON ar.service_id = s.id
        INNER JOIN department_members dm ON ar.member_id = dm.member_id
        WHERE s.date >= ? AND s.date <= ? AND dm.department_id = ?
        GROUP BY DATE(s.date)
        ORDER BY s.date ASC
    ');
    $trendStmt->execute([$startDate, $endDate, $departmentId]);
    $trends = $trendStmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch top attendees from this department
    $topStmt = $pdo->prepare('
        SELECT 
            m.id,
            m.first_name,
            m.last_name,
            COUNT(ar.id) as total_attendance,
            COUNT(CASE WHEN ar.attendance_status = "present" THEN 1 END) as present_count,
            COUNT(CASE WHEN ar.attendance_status = "absent" THEN 1 END) as absent_count
        FROM attendance_records ar
        JOIN members m ON ar.member_id = m.id
        INNER JOIN department_members dm ON m.id = dm.member_id
        WHERE ar.service_id IN (
            SELECT id FROM services WHERE date >= ? AND date <= ?
        ) AND dm.department_id = ?
        GROUP BY m.id
        ORDER BY present_count DESC
        LIMIT 10
    ');
    $topStmt->execute([$startDate, $endDate, $departmentId]);
    $topAttendees = $topStmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch members with poor attendance from this department
    $poorStmt = $pdo->prepare('
        SELECT 
            m.id,
            m.first_name,
            m.last_name,
            COUNT(ar.id) as total_attendance,
            COUNT(CASE WHEN ar.attendance_status = "present" THEN 1 END) as present_count,
            COUNT(CASE WHEN ar.attendance_status = "absent" THEN 1 END) as absent_count
        FROM attendance_records ar
        JOIN members m ON ar.member_id = m.id
        INNER JOIN department_members dm ON m.id = dm.member_id
        WHERE ar.service_id IN (
            SELECT id FROM services WHERE date >= ? AND date <= ?
        ) AND dm.department_id = ?
        GROUP BY m.id
        HAVING present_count < (total_attendance / 2)
        ORDER BY present_count ASC
        LIMIT 10
    ');
    $poorStmt->execute([$startDate, $endDate, $departmentId]);
    $poorAttendees = $poorStmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    error_log('Attendance report error: ' . $e->getMessage());
    $error = 'Failed to generate attendance report.';
}

$pageTitle = 'Attendance Report';
?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<div class="mb-6">
    <h2 class="text-2xl font-heading font-bold text-royal-800">Attendance Report</h2>
    <p class="text-sm text-mist-500 mt-0.5">Attendance analytics and member statistics</p>
</div>

<?php if ($error): ?>
<div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-xl text-sm text-red-600">
    ✗ <?php echo htmlspecialchars($error); ?>
</div>
<?php endif; ?>

<!-- Date Range Filter -->
<div class="bg-white rounded-2xl border border-mist-200 shadow-sm p-6 mb-5">
    <form method="GET" class="flex flex-col sm:flex-row gap-3 items-end">
        <div class="flex-1">
            <label class="block text-xs font-semibold text-mist-600 uppercase mb-2">Start Date</label>
            <input type="date" name="start_date" value="<?php echo htmlspecialchars($startDate); ?>" 
                   class="w-full px-3 py-2 border border-mist-300 rounded-lg text-sm focus:outline-none focus:border-royal-500">
        </div>
        <div class="flex-1">
            <label class="block text-xs font-semibold text-mist-600 uppercase mb-2">End Date</label>
            <input type="date" name="end_date" value="<?php echo htmlspecialchars($endDate); ?>" 
                   class="w-full px-3 py-2 border border-mist-300 rounded-lg text-sm focus:outline-none focus:border-royal-500">
        </div>
        <button type="submit" class="px-4 py-2 bg-royal-600 text-white rounded-lg text-sm font-semibold hover:bg-royal-700 transition">
            Generate
        </button>
    </form>
</div>

<!-- Overall Summary -->
<div class="grid grid-cols-2 sm:grid-cols-5 gap-3 mb-5">
    <div class="bg-white border border-mist-200 rounded-2xl p-4">
        <p class="text-[10px] font-semibold text-mist-500 uppercase">Total Records</p>
        <p class="text-2xl font-heading font-bold text-royal-800 mt-1"><?php echo $summary['total_records']; ?></p>
    </div>
    <div class="bg-emerald-50 border border-emerald-200 rounded-2xl p-4">
        <p class="text-[10px] font-semibold text-emerald-600 uppercase">Present</p>
        <p class="text-2xl font-heading font-bold text-emerald-700 mt-1"><?php echo $summary['present']; ?></p>
        <p class="text-xs text-emerald-600 mt-1"><?php echo $summary['present_percentage']; ?>%</p>
    </div>
    <div class="bg-red-50 border border-red-200 rounded-2xl p-4">
        <p class="text-[10px] font-semibold text-red-600 uppercase">Absent</p>
        <p class="text-2xl font-heading font-bold text-red-700 mt-1"><?php echo $summary['absent']; ?></p>
        <p class="text-xs text-red-600 mt-1"><?php echo $summary['absent_percentage']; ?>%</p>
    </div>
    <div class="bg-yellow-50 border border-yellow-200 rounded-2xl p-4">
        <p class="text-[10px] font-semibold text-yellow-600 uppercase">Late</p>
        <p class="text-2xl font-heading font-bold text-yellow-700 mt-1"><?php echo $summary['late']; ?></p>
        <p class="text-xs text-yellow-600 mt-1"><?php echo $summary['late_percentage']; ?>%</p>
    </div>
    <div class="bg-blue-50 border border-blue-200 rounded-2xl p-4">
        <p class="text-[10px] font-semibold text-blue-600 uppercase">Excused</p>
        <p class="text-2xl font-heading font-bold text-blue-700 mt-1"><?php echo $summary['excused']; ?></p>
        <p class="text-xs text-blue-600 mt-1"><?php echo $summary['excused_percentage']; ?>%</p>
    </div>
</div>

<!-- Attendance Trends -->
<?php if (!empty($trends)): ?>
<div class="bg-white rounded-2xl border border-mist-200 shadow-sm p-6 mb-5">
    <h3 class="text-lg font-heading font-bold text-royal-800 mb-4">Attendance Trends</h3>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-mist-50 border-b border-mist-200">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-bold text-mist-600 uppercase">Service Date</th>
                    <th class="px-4 py-3 text-center text-xs font-bold text-mist-600 uppercase">Total</th>
                    <th class="px-4 py-3 text-center text-xs font-bold text-emerald-600 uppercase">Present</th>
                    <th class="px-4 py-3 text-center text-xs font-bold text-red-600 uppercase">Absent</th>
                    <th class="px-4 py-3 text-center text-xs font-bold text-yellow-600 uppercase">Late</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($trends as $trend): ?>
                <tr class="border-b border-mist-100 hover:bg-gray-50">
                    <td class="px-4 py-3 font-semibold text-royal-800">
                        <?php echo date('F d, Y', strtotime($trend['service_date'])); ?>
                    </td>
                    <td class="px-4 py-3 text-center font-semibold text-gray-700"><?php echo $trend['attendance_count']; ?></td>
                    <td class="px-4 py-3 text-center font-semibold text-emerald-700"><?php echo $trend['present_count']; ?></td>
                    <td class="px-4 py-3 text-center font-semibold text-red-700"><?php echo $trend['absent_count']; ?></td>
                    <td class="px-4 py-3 text-center font-semibold text-yellow-700"><?php echo $trend['late_count']; ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- Top Attendees -->
<?php if (!empty($topAttendees)): ?>
<div class="bg-white rounded-2xl border border-mist-200 shadow-sm p-6 mb-5">
    <h3 class="text-lg font-heading font-bold text-royal-800 mb-4">Top Attendees</h3>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-mist-50 border-b border-mist-200">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-bold text-mist-600 uppercase">Member</th>
                    <th class="px-4 py-3 text-center text-xs font-bold text-mist-600 uppercase">Total</th>
                    <th class="px-4 py-3 text-center text-xs font-bold text-emerald-600 uppercase">Present</th>
                    <th class="px-4 py-3 text-center text-xs font-bold text-red-600 uppercase">Absent</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($topAttendees as $member): ?>
                <tr class="border-b border-mist-100 hover:bg-gray-50">
                    <td class="px-4 py-3 font-semibold text-royal-800">
                        <?php echo htmlspecialchars($member['first_name'] . ' ' . $member['last_name']); ?>
                    </td>
                    <td class="px-4 py-3 text-center font-semibold text-gray-700"><?php echo $member['total_attendance']; ?></td>
                    <td class="px-4 py-3 text-center font-semibold text-emerald-700"><?php echo $member['present_count']; ?></td>
                    <td class="px-4 py-3 text-center font-semibold text-red-700"><?php echo $member['absent_count']; ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- Poor Attendance -->
<?php if (!empty($poorAttendees)): ?>
<div class="bg-white rounded-2xl border border-mist-200 shadow-sm p-6 mb-5">
    <h3 class="text-lg font-heading font-bold text-royal-800 mb-4">Low Attendance Members</h3>
    <p class="text-xs text-mist-500 mb-4">Members with less than 50% attendance rate</p>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-mist-50 border-b border-mist-200">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-bold text-mist-600 uppercase">Member</th>
                    <th class="px-4 py-3 text-center text-xs font-bold text-mist-600 uppercase">Total</th>
                    <th class="px-4 py-3 text-center text-xs font-bold text-emerald-600 uppercase">Present</th>
                    <th class="px-4 py-3 text-center text-xs font-bold text-red-600 uppercase">Absent</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($poorAttendees as $member): ?>
                <tr class="border-b border-mist-100 hover:bg-gray-50">
                    <td class="px-4 py-3 font-semibold text-royal-800">
                        <?php echo htmlspecialchars($member['first_name'] . ' ' . $member['last_name']); ?>
                    </td>
                    <td class="px-4 py-3 text-center font-semibold text-gray-700"><?php echo $member['total_attendance']; ?></td>
                    <td class="px-4 py-3 text-center font-semibold text-emerald-700"><?php echo $member['present_count']; ?></td>
                    <td class="px-4 py-3 text-center font-semibold text-red-700"><?php echo $member['absent_count']; ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>
