<?php
/**
 * Department Reports View
 */
require_once __DIR__ . '/../includes/auth_check.php';

$pdo = require __DIR__ . '/../includes/db.php';
$departmentId = getCurrentDepartmentId();

$formError = ''; $openModal = ''; $savedPost = [];

// Auto-open modal from dashboard Quick Action
if (isset($_GET['action']) && $_GET['action'] === 'add') {
    $openModal = 'createReportModal';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['_modal_create_report'])) {
    $savedPost   = $_POST;
    $title       = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $reportDate  = trim($_POST['report_date'] ?? '');
    $category    = trim($_POST['category'] ?? '');
    $validCats   = ['Weekly','Monthly','Activity','Finance','Quarterly','Annual','Event','Attendance','Other'];

    if (empty($title)) {
        $formError = 'Report title is required.';
    } elseif (empty($reportDate)) {
        $formError = 'Report date is required.';
    } elseif (!in_array($category, $validCats, true)) {
        $formError = 'Please select a valid category.';
    } else {
        try {
            $ins = $pdo->prepare('
                INSERT INTO department_reports (department_id, title, description, report_date, category, status, created_at)
                VALUES (?, ?, ?, ?, ?, \'draft\', NOW())
            ');
            $ins->execute([$departmentId, $title, $description ?: null, $reportDate, $category]);
            $reportId = $pdo->lastInsertId();

            logDepartmentAction($pdo, 'report_created', 'report', $reportId, "Created report: $title");

            header('Location: view.php?success=1');
            exit;
        } catch (Exception $e) {
            error_log('Create report error: ' . $e->getMessage());
            $formError = 'Failed to save report. Please try again.';
        }
    }
    if ($formError) { $openModal = 'createReportModal'; }
}

$reports = [];
$statusFilter = trim($_GET['status'] ?? '');

try {
    // Fetch reports for this department
    $query = '
        SELECT 
            id,
            title,
            report_date,
            category,
            status,
            submitted_at,
            reviewed_at,
            created_at
        FROM department_reports
        WHERE department_id = ?
    ';
    
    $params = [$departmentId];
    
    if (!empty($statusFilter)) {
        $query .= ' AND status = ?';
        $params[] = $statusFilter;
    }
    
    $query .= ' ORDER BY created_at DESC';
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $reports = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    error_log('Reports view error: ' . $e->getMessage());
}

$pageTitle = 'Department Reports';
?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<?php
$statusClasses = [
    'draft'     => 'bg-gray-100 text-gray-600',
    'submitted' => 'bg-dawn-100 text-dawn-700',
    'approved'  => 'bg-emerald-100 text-emerald-700',
    'rejected'  => 'bg-red-100 text-red-600',
];
?>

<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
    <div>
        <h2 class="text-2xl font-heading font-bold text-royal-800">Reports</h2>
        <p class="text-sm text-mist-500 mt-0.5">Department activity and finance reports</p>
    </div>
    <button onclick="showModal('createReportModal')" class="inline-flex items-center gap-2 px-4 py-2.5 bg-royal-600 hover:bg-royal-700 text-white rounded-xl text-sm font-semibold shadow transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
        New Report
    </button>
</div>

<?php if (isset($_GET['success'])): ?>
<div class="mb-4 p-3 bg-emerald-50 border border-emerald-200 rounded-xl text-sm text-emerald-700 font-medium">✓ Report created and saved as draft.</div>
<?php endif; ?>

<!-- Status filter -->
<form method="GET" class="flex flex-wrap gap-2 mb-5">
    <?php $statuses = ['' => 'All', 'draft' => 'Draft', 'submitted' => 'Submitted', 'approved' => 'Approved', 'rejected' => 'Rejected']; ?>
    <?php foreach ($statuses as $val => $label): ?>
    <button type="submit" name="status" value="<?php echo $val; ?>"
        class="px-3 py-1.5 rounded-xl text-xs font-semibold transition border <?php echo $statusFilter === $val ? 'bg-royal-600 text-white border-royal-600' : 'bg-white text-mist-600 border-mist-200 hover:border-royal-400'; ?>">
        <?php echo $label; ?>
    </button>
    <?php endforeach; ?>
</form>

<?php if (empty($reports)): ?>
<div class="bg-white rounded-2xl border border-mist-200 shadow-sm p-12 text-center">
    <p class="text-mist-400 font-semibold mb-3">No reports found.</p>
    <button onclick="showModal('createReportModal')" class="inline-block text-sm text-royal-600 hover:underline font-semibold">Create your first report →</button>
</div>
<?php else: ?>
<div class="space-y-3">
<?php foreach ($reports as $report): ?>
    <?php $sc = $statusClasses[$report['status']] ?? 'bg-gray-100 text-gray-600'; ?>
    <div class="bg-white rounded-2xl border border-mist-200 shadow-sm p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2 mb-1">
                <h3 class="font-bold text-royal-800 text-sm truncate"><?php echo htmlspecialchars($report['title']); ?></h3>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase <?php echo $sc; ?> shrink-0"><?php echo ucfirst($report['status']); ?></span>
            </div>
            <p class="text-xs text-mist-400">
                <?php echo htmlspecialchars($report['category']); ?>
                &bull;
                <?php echo date('M d, Y', strtotime($report['report_date'])); ?>
                <?php if ($report['submitted_at']): ?>
                &bull; Submitted <?php echo date('M d, Y', strtotime($report['submitted_at'])); ?>
                <?php endif; ?>
            </p>
        </div>
        <div class="flex gap-2 shrink-0">
            <a href="view-detail.php?id=<?php echo $report['id']; ?>"
               class="px-3 py-1.5 bg-royal-50 hover:bg-royal-100 text-royal-700 rounded-xl text-xs font-semibold transition">View</a>
            <?php if ($report['status'] === 'draft'): ?>
            <a href="submit.php?id=<?php echo $report['id']; ?>"
               class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-semibold shadow transition">Submit</a>
            <?php endif; ?>
        </div>
    </div>
<?php endforeach; ?>
</div>
<p class="text-xs text-mist-400 mt-3"><?php echo count($reports); ?> report(s)</p>
<?php endif; ?>

<!-- Create Report Modal -->
<div id="createReportModal" class="fixed inset-0 z-50 hidden items-center justify-center">
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" onclick="hideModal('createReportModal')"></div>
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg mx-4 p-6 z-10 overflow-y-auto max-h-[90vh]">
        <div class="flex items-center justify-between mb-5">
            <h3 class="text-lg font-heading font-bold text-royal-800">Create New Report</h3>
            <button onclick="hideModal('createReportModal')" class="text-mist-400 hover:text-mist-600 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <?php if ($formError): ?>
        <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700"><?php echo htmlspecialchars($formError); ?></div>
        <?php endif; ?>
        <form method="POST" class="space-y-4">
            <input type="hidden" name="_modal_create_report" value="1">
            <div>
                <label class="block text-sm font-semibold text-mist-700 mb-1.5">Title *</label>
                <input type="text" name="title" required placeholder="Report title"
                    value="<?php echo htmlspecialchars($savedPost['title'] ?? ''); ?>"
                    class="w-full border border-mist-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-royal-400 focus:ring-2 focus:ring-royal-100">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-semibold text-mist-700 mb-1.5">Date *</label>
                    <input type="date" name="report_date" required
                        value="<?php echo htmlspecialchars($savedPost['report_date'] ?? date('Y-m-d')); ?>"
                        class="w-full border border-mist-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-royal-400 focus:ring-2 focus:ring-royal-100">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-mist-700 mb-1.5">Category *</label>
                    <select name="category" required class="w-full border border-mist-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-royal-400 focus:ring-2 focus:ring-royal-100">
                        <option value="">Select…</option>
                        <?php foreach (['Weekly','Monthly','Activity','Finance','Attendance','Quarterly','Annual','Event','Other'] as $cat): ?>
                        <option value="<?php echo $cat; ?>" <?php echo ($savedPost['category'] ?? '') === $cat ? 'selected' : ''; ?>><?php echo $cat; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div>
                <label class="block text-sm font-semibold text-mist-700 mb-1.5">Description</label>
                <textarea name="description" rows="5" placeholder="Report details, summary, observations…"
                    class="w-full border border-mist-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-royal-400 focus:ring-2 focus:ring-royal-100 resize-none"><?php echo htmlspecialchars($savedPost['description'] ?? ''); ?></textarea>
            </div>
            <p class="text-xs text-mist-400">Report will be saved as <span class="font-semibold">Draft</span>. You can submit it for review from the reports list.</p>
            <div class="flex gap-3 pt-1">
                <button type="submit" class="flex-1 py-2.5 bg-royal-600 hover:bg-royal-700 text-white rounded-xl text-sm font-semibold shadow transition">Create Report</button>
                <button type="button" onclick="hideModal('createReportModal')" class="flex-1 py-2.5 bg-mist-100 hover:bg-mist-200 text-mist-700 rounded-xl text-sm font-semibold transition">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
function showModal(id){const el=document.getElementById(id);el.classList.remove('hidden');el.classList.add('flex');document.body.style.overflow='hidden';}
function hideModal(id){const el=document.getElementById(id);el.classList.add('hidden');el.classList.remove('flex');document.body.style.overflow='';}
<?php if ($openModal): ?>document.addEventListener('DOMContentLoaded',()=>showModal('<?php echo $openModal; ?>'));<?php endif; ?>
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
