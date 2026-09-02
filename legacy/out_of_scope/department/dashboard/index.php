<?php
/**
 * Department Dashboard
 * 
 * Main dashboard for department heads showing overview of members, finance, reports, and activities.
 */

require_once __DIR__ . '/../includes/auth_check.php';

$pdo = require __DIR__ . '/../includes/db.php';
$departmentId = getCurrentDepartmentId();
$pageTitle = 'Dashboard';

// ── Fetch Dashboard Stats ──

// Members count
$stmt = $pdo->prepare('SELECT COUNT(*) as count FROM department_members WHERE department_id = ?');
$stmt->execute([$departmentId]);
$memberCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;

// Leaders count
$stmt = $pdo->prepare('SELECT COUNT(*) as count FROM department_leaders WHERE department_id = ?');
$stmt->execute([$departmentId]);
$leaderCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;

// Finance - Total Income
$stmt = $pdo->prepare('
    SELECT SUM(fe.amount) as total 
    FROM finance_entries fe
    JOIN finance_categories fc ON fe.category_id = fc.id
    WHERE fe.department_id = ? AND fc.category_type = \'income\'
');
$stmt->execute([$departmentId]);
$totalIncome = (float) ($stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);

// Finance - Total Expenses
$stmt = $pdo->prepare('
    SELECT SUM(fe.amount) as total 
    FROM finance_entries fe
    JOIN finance_categories fc ON fe.category_id = fc.id
    WHERE fe.department_id = ? AND fc.category_type = \'expense\'
');
$stmt->execute([$departmentId]);
$totalExpenses = (float) ($stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);

// Finance - Balance
$balance = $totalIncome - $totalExpenses;

// Recent Reports
$stmt = $pdo->prepare('
    SELECT id, title, submitted_at as submission_date, status 
    FROM department_reports 
    WHERE department_id = ? 
    ORDER BY submitted_at DESC 
    LIMIT 5
');
$stmt->execute([$departmentId]);
$recentReports = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

// Recent Finance Entries
$stmt = $pdo->prepare('
    SELECT fe.id, fe.description, fc.category_type as entry_type, fe.amount, fe.entry_date
    FROM finance_entries fe
    JOIN finance_categories fc ON fe.category_id = fc.id
    WHERE fe.department_id = ?
    ORDER BY fe.entry_date DESC
    LIMIT 5
');
$stmt->execute([$departmentId]);
$recentFinance = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

// Fetch department name for personalization
$stmt = $pdo->prepare('SELECT name FROM departments WHERE id = ?');
$stmt->execute([$departmentId]);
$departmentData = $stmt->fetch(PDO::FETCH_ASSOC);
$departmentName = $departmentData['name'] ?? 'Department';

require_once __DIR__ . '/../includes/header.php';
?>

<style>
    .stat-card { transition: transform 0.2s, box-shadow 0.2s; }
    .stat-card:hover { transform: translateY(-4px); box-shadow: 0 12px 24px rgba(79,54,216,0.12); }
    .chart-container { min-height: 300px; }
</style>

<!-- Welcome Banner -->
<div class="mb-6 bg-gradient-to-r from-royal-600 to-royal-700 rounded-2xl p-6 text-white shadow-lg">
    <h1 class="text-3xl font-bold font-heading mb-2">Welcome, <?= htmlspecialchars($_SESSION['head_name'] ?? 'Department Head', ENT_QUOTES, 'UTF-8') ?>!</h1>
    <p class="text-royal-100 text-sm">Here's what's happening with the <?= htmlspecialchars($departmentName, ENT_QUOTES, 'UTF-8') ?> department</p>
</div>

<!-- Stats Grid -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">

    <!-- Members Card -->
    <div class="stat-card bg-white rounded-2xl border border-mist-200 p-6 shadow-sm hover:shadow-md">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-mist-600 text-sm font-semibold mb-1">Active Members</p>
                <p class="text-3xl font-bold text-mist-900"><?= $memberCount ?></p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-dawn-50 flex items-center justify-center">
                <svg class="w-6 h-6 text-dawn-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
        </div>
        <a href="<?= htmlspecialchars(departmentUrl('members/view.php'), ENT_QUOTES, 'UTF-8') ?>" class="mt-4 inline-flex items-center gap-1 text-sm text-dawn-600 hover:text-dawn-700 font-semibold">
            View All
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
            </svg>
        </a>
    </div>

    <!-- Leaders Card -->
    <div class="stat-card bg-white rounded-2xl border border-mist-200 p-6 shadow-sm hover:shadow-md">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-mist-600 text-sm font-semibold mb-1">Leaders</p>
                <p class="text-3xl font-bold text-mist-900"><?= $leaderCount ?></p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-glory-50 flex items-center justify-center">
                <svg class="w-6 h-6 text-glory-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z"/>
                </svg>
            </div>
        </div>
        <a href="<?= htmlspecialchars(departmentUrl('leaders/view.php'), ENT_QUOTES, 'UTF-8') ?>" class="mt-4 inline-flex items-center gap-1 text-sm text-glory-600 hover:text-glory-700 font-semibold">
            View All
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
            </svg>
        </a>
    </div>

    <!-- Total Income Card -->
    <div class="stat-card bg-white rounded-2xl border border-mist-200 p-6 shadow-sm hover:shadow-md">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-mist-600 text-sm font-semibold mb-1">Total Income</p>
                <p class="text-3xl font-bold text-mist-900">Tsh <?= number_format($totalIncome, 0, '.', ',') ?></p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-emerald-50 flex items-center justify-center">
                <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
            </div>
        </div>
        <a href="<?= htmlspecialchars(departmentUrl('finance/view.php'), ENT_QUOTES, 'UTF-8') ?>" class="mt-4 inline-flex items-center gap-1 text-sm text-emerald-600 hover:text-emerald-700 font-semibold">
            View Finance
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
            </svg>
        </a>
    </div>

    <!-- Balance Card -->
    <div class="stat-card bg-white rounded-2xl border border-mist-200 p-6 shadow-sm hover:shadow-md">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-mist-600 text-sm font-semibold mb-1">Balance</p>
                <p class="text-3xl font-bold <?= $balance >= 0 ? 'text-emerald-600' : 'text-rose-600' ?>">
                    Tsh <?= number_format(abs($balance), 0, '.', ',') ?>
                </p>
            </div>
            <div class="w-12 h-12 rounded-lg <?= $balance >= 0 ? 'bg-emerald-50' : 'bg-rose-50' ?> flex items-center justify-center">
                <svg class="w-6 h-6 <?= $balance >= 0 ? 'text-emerald-600' : 'text-rose-600' ?>" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>
        <a href="<?= htmlspecialchars(departmentUrl('finance/view.php'), ENT_QUOTES, 'UTF-8') ?>" class="mt-4 inline-flex items-center gap-1 text-sm <?= $balance >= 0 ? 'text-emerald-600 hover:text-emerald-700' : 'text-rose-600 hover:text-rose-700' ?> font-semibold">
            Details
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
            </svg>
        </a>
    </div>

</div>

<!-- Two Column Layout: Recent Reports + Recent Finance -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">

    <!-- Recent Reports -->
    <div class="bg-white rounded-2xl border border-mist-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-mist-200 flex items-center justify-between">
            <h2 class="text-lg font-bold font-heading text-mist-900">Recent Reports</h2>
            <a href="<?= htmlspecialchars(departmentUrl('reports/view.php'), ENT_QUOTES, 'UTF-8') ?>" class="text-sm text-royal-600 hover:text-royal-700 font-semibold">View All →</a>
        </div>
        <div class="divide-y divide-mist-100">
            <?php if (count($recentReports) > 0): ?>
                <?php foreach ($recentReports as $report): ?>
                    <div class="px-6 py-4 hover:bg-mist-50 transition-colors">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex-1 min-w-0">
                                <h3 class="font-semibold text-mist-900 truncate"><?= htmlspecialchars($report['title'], ENT_QUOTES, 'UTF-8') ?></h3>
                                <p class="text-xs text-mist-500 mt-1"><?= date('d M Y', strtotime($report['submission_date'])) ?></p>
                            </div>
                            <span class="px-2.5 py-1 rounded-lg text-xs font-semibold <?= 
                                $report['status'] === 'submitted' ? 'bg-emerald-50 text-emerald-700' :
                                ($report['status'] === 'draft' ? 'bg-amber-50 text-amber-700' : 'bg-mist-50 text-mist-600')
                            ?>">
                                <?= ucfirst($report['status']) ?>
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="px-6 py-8 text-center text-mist-500 text-sm">
                    <svg class="w-12 h-12 mx-auto mb-3 text-mist-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.566.034-1.08.16-1.539.342m-5.801 0C2.904 3.487 1.5 5.317 1.5 7.5v9.75c0 2.183 1.404 4.012 3.541 4.743M9 15h3.75M9 12h3.75M9 18h3.75"/>
                    </svg>
                    No reports yet
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Recent Finance Entries -->
    <div class="bg-white rounded-2xl border border-mist-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-mist-200 flex items-center justify-between">
            <h2 class="text-lg font-bold font-heading text-mist-900">Recent Transactions</h2>
            <a href="<?= htmlspecialchars(departmentUrl('finance/view.php'), ENT_QUOTES, 'UTF-8') ?>" class="text-sm text-royal-600 hover:text-royal-700 font-semibold">View All →</a>
        </div>
        <div class="divide-y divide-mist-100">
            <?php if (count($recentFinance) > 0): ?>
                <?php foreach ($recentFinance as $entry): ?>
                    <div class="px-6 py-4 hover:bg-mist-50 transition-colors">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex-1 min-w-0">
                                <h3 class="font-semibold text-mist-900 truncate"><?= htmlspecialchars($entry['description'] ?: 'Transaction', ENT_QUOTES, 'UTF-8') ?></h3>
                                <p class="text-xs text-mist-500 mt-1"><?= date('d M Y', strtotime($entry['entry_date'])) ?></p>
                            </div>
                            <span class="px-2.5 py-1 rounded-lg text-sm font-semibold whitespace-nowrap <?= 
                                $entry['entry_type'] === 'income' ? 'text-emerald-700 bg-emerald-50' : 'text-rose-700 bg-rose-50'
                            ?>">
                                <?= $entry['entry_type'] === 'income' ? '+' : '-' ?> Tsh <?= number_format($entry['amount'], 0, '.', ',') ?>
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="px-6 py-8 text-center text-mist-500 text-sm">
                    <svg class="w-12 h-12 mx-auto mb-3 text-mist-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    No transactions yet
                </div>
            <?php endif; ?>
        </div>
    </div>

</div>

<!-- Quick Actions -->
<div class="bg-white rounded-2xl border border-mist-200 shadow-sm p-6">
    <h2 class="text-lg font-bold font-heading text-mist-900 mb-4">Quick Actions</h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
        <a href="<?= htmlspecialchars(departmentUrl('members/add.php'), ENT_QUOTES, 'UTF-8') ?>" class="p-4 rounded-xl border border-mist-200 hover:border-royal-300 hover:bg-royal-50 transition-all text-center group">
            <svg class="w-6 h-6 mx-auto mb-2 text-mist-400 group-hover:text-royal-600 transition-colors" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
            </svg>
            <p class="text-sm font-semibold text-mist-700 group-hover:text-royal-700">Add Member</p>
        </a>

        <a href="<?= htmlspecialchars(departmentUrl('leaders/add.php'), ENT_QUOTES, 'UTF-8') ?>" class="p-4 rounded-xl border border-mist-200 hover:border-royal-300 hover:bg-royal-50 transition-all text-center group">
            <svg class="w-6 h-6 mx-auto mb-2 text-mist-400 group-hover:text-royal-600 transition-colors" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
            </svg>
            <p class="text-sm font-semibold text-mist-700 group-hover:text-royal-700">Add Leader</p>
        </a>

        <a href="<?= htmlspecialchars(departmentUrl('finance/add_income.php'), ENT_QUOTES, 'UTF-8') ?>" class="p-4 rounded-xl border border-mist-200 hover:border-royal-300 hover:bg-royal-50 transition-all text-center group">
            <svg class="w-6 h-6 mx-auto mb-2 text-mist-400 group-hover:text-royal-600 transition-colors" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
            <p class="text-sm font-semibold text-mist-700 group-hover:text-royal-700">Record Income</p>
        </a>

        <a href="<?= htmlspecialchars(departmentUrl('reports/create.php'), ENT_QUOTES, 'UTF-8') ?>" class="p-4 rounded-xl border border-mist-200 hover:border-royal-300 hover:bg-royal-50 transition-all text-center group">
            <svg class="w-6 h-6 mx-auto mb-2 text-mist-400 group-hover:text-royal-600 transition-colors" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.566.034-1.08.16-1.539.342m-5.801 0C2.904 3.487 1.5 5.317 1.5 7.5v9.75c0 2.183 1.404 4.012 3.541 4.743M9 15h3.75M9 12h3.75M9 18h3.75"/>
            </svg>
            <p class="text-sm font-semibold text-mist-700 group-hover:text-royal-700">Create Report</p>
        </a>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
