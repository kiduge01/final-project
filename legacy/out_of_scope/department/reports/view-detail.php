<?php
/**
 * View Report Details
 * 
 * Display full details of a specific report.
 */

require_once __DIR__ . '/../includes/auth_check.php';

$pdo = require __DIR__ . '/../includes/db.php';
$departmentId = getCurrentDepartmentId();
$reportId = $_GET['id'] ?? null;

if (!$reportId || !is_numeric($reportId)) {
    header('Location: view.php');
    exit;
}

$report = null;

try {
    // Fetch report
    $stmt = $pdo->prepare('
        SELECT 
            id,
            title,
            description,
            report_date,
            category,
            status,
            submitted_at,
            reviewed_by,
            reviewed_at,
            review_notes,
            created_at
        FROM department_reports
        WHERE department_id = ? AND id = ?
    ');
    $stmt->execute([$departmentId, $reportId]);
    $report = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$report) {
        header('Location: view.php');
        exit;
    }

} catch (Exception $e) {
    error_log('Report detail error: ' . $e->getMessage());
    header('Location: view.php');
    exit;
}

$success = $_GET['success'] ?? '';
$pageTitle = 'Report Details';
?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<?php
$statusClasses = [
    'draft'     => 'bg-gray-100 text-gray-600',
    'submitted' => 'bg-dawn-100 text-dawn-700',
    'approved'  => 'bg-emerald-100 text-emerald-700',
    'rejected'  => 'bg-red-100 text-red-600',
];
$sc = $statusClasses[$report['status']] ?? 'bg-gray-100 text-gray-600';
?>

<div class="mb-6 flex items-center gap-3">
    <a href="view.php" class="inline-flex items-center gap-1 text-sm text-mist-500 hover:text-royal-700 font-semibold transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
        Back to Reports
    </a>
</div>

<?php if ($success === '1'): ?>
<div class="mb-4 p-3 bg-emerald-50 border border-emerald-200 rounded-xl text-sm text-emerald-700 font-medium">✓ Report saved successfully!</div>
<?php endif; ?>

<!-- Header card -->
<div class="bg-white rounded-2xl border border-mist-200 shadow-sm p-6 mb-5">
    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
        <h2 class="text-xl font-heading font-bold text-royal-800"><?php echo htmlspecialchars($report['title']); ?></h2>
        <span class="px-3 py-1 rounded-full text-xs font-bold uppercase <?php echo $sc; ?> shrink-0"><?php echo ucfirst($report['status']); ?></span>
    </div>
    <div class="mt-4 grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div>
            <p class="text-[10px] font-semibold text-mist-400 uppercase">Report Date</p>
            <p class="text-sm font-semibold text-royal-800 mt-0.5"><?php echo date('F d, Y', strtotime($report['report_date'])); ?></p>
        </div>
        <div>
            <p class="text-[10px] font-semibold text-mist-400 uppercase">Category</p>
            <p class="text-sm font-semibold text-royal-800 mt-0.5"><?php echo htmlspecialchars($report['category']); ?></p>
        </div>
        <div>
            <p class="text-[10px] font-semibold text-mist-400 uppercase">Created</p>
            <p class="text-sm font-semibold text-royal-800 mt-0.5"><?php echo date('M d, Y', strtotime($report['created_at'])); ?></p>
        </div>
        <?php if ($report['submitted_at']): ?>
        <div>
            <p class="text-[10px] font-semibold text-mist-400 uppercase">Submitted</p>
            <p class="text-sm font-semibold text-royal-800 mt-0.5"><?php echo date('M d, Y', strtotime($report['submitted_at'])); ?></p>
        </div>
        <?php endif; ?>
        <?php if ($report['reviewed_at']): ?>
        <div>
            <p class="text-[10px] font-semibold text-mist-400 uppercase">Reviewed</p>
            <p class="text-sm font-semibold text-royal-800 mt-0.5"><?php echo date('M d, Y', strtotime($report['reviewed_at'])); ?></p>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Content -->
<?php if ($report['category'] === 'Attendance'): ?>
<div class="bg-white rounded-2xl border border-mist-200 shadow-sm p-6 mb-5">
    <h3 class="text-sm font-bold text-mist-600 uppercase tracking-wide mb-4">Attendance Analytics</h3>
    <iframe src="attendance.php?report_id=<?php echo $report['id']; ?>&start_date=<?php echo htmlspecialchars($report['report_date']); ?>" 
            style="width: 100%; height: 800px; border: none; border-radius: 0.5rem;"
            title="Attendance Report"></iframe>
</div>
<?php else: ?>
<div class="bg-white rounded-2xl border border-mist-200 shadow-sm p-6 mb-5">
    <h3 class="text-sm font-bold text-mist-600 uppercase tracking-wide mb-3">Report Content</h3>
    <div class="text-sm text-mist-700 leading-relaxed whitespace-pre-line"><?php echo nl2br(htmlspecialchars($report['description'] ?? '')); ?></div>
</div>
<?php endif; ?>

<!-- Review notes -->
<?php if ($report['review_notes']): ?>
<div class="mb-5 p-4 bg-dawn-50 border border-dawn-200 rounded-2xl">
    <p class="text-xs font-bold text-dawn-700 uppercase tracking-wide mb-2">Admin Review Notes</p>
    <p class="text-sm text-mist-700"><?php echo nl2br(htmlspecialchars($report['review_notes'])); ?></p>
</div>
<?php endif; ?>

<!-- Actions -->
<div class="flex gap-3">
    <?php if ($report['status'] === 'draft'): ?>
    <a href="submit.php?id=<?php echo $report['id']; ?>"
       class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-semibold shadow transition">Submit to Admin</a>
    <?php endif; ?>
    <a href="view.php" class="px-6 py-2.5 bg-mist-100 hover:bg-mist-200 text-mist-700 rounded-xl text-sm font-semibold transition">Back to Reports</a>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
