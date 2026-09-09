<?php
/**
 * Department Finance Records View
 * 
 * Display all financial transactions (income and expenses) for the department.
 */

require_once __DIR__ . '/../includes/auth_check.php';

$pdo = require __DIR__ . '/../includes/db.php';
$departmentId = getCurrentDepartmentId();

$totalIncome = 0;
$totalExpense = 0;
$balance = 0;
$formError = '';
$openModal = '';
$savedPost = [];

// Auto-open modal from dashboard Quick Action
if (isset($_GET['action'])) {
    if ($_GET['action'] === 'income')  { $openModal = 'incomeModal'; }
    if ($_GET['action'] === 'expense') { $openModal = 'expenseModal'; }
}

// Handle inline modal form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['record_type'])) {
    $rType = in_array($_POST['record_type'] ?? '', ['income', 'expense']) ? $_POST['record_type'] : '';
    $rDate = trim($_POST['date'] ?? '');
    $rCategory = trim($_POST['category'] ?? '');
    $rAmount = trim($_POST['amount'] ?? '');
    $rDescription = trim($_POST['description'] ?? '');
    $savedPost = $_POST;

    if (!$rType || empty($rDate) || empty($rCategory) || empty($rAmount)) {
        $formError = 'Date, category, and amount are required.';
        $openModal = $rType . 'Modal';
    } elseif (!is_numeric($rAmount) || (float)$rAmount <= 0) {
        $formError = 'Amount must be a valid positive number.';
        $openModal = $rType . 'Modal';
    } else {
        try {
            $pdo->beginTransaction();
            
            // Get category_id
            $catStmt = $pdo->prepare('SELECT id FROM finance_categories WHERE name = ? AND category_type = ? LIMIT 1');
            $catStmt->execute([$rCategory, $rType]);
            $catId = $catStmt->fetchColumn();
            
            if (!$catId) {
                // Auto-create category if missing
                $insCat = $pdo->prepare('INSERT INTO finance_categories (name, category_type, description) VALUES (?, ?, ?)');
                $insCat->execute([$rCategory, $rType, "Auto-created from department portal"]);
                $catId = $pdo->lastInsertId();
            }

            $stmt = $pdo->prepare('INSERT INTO finance_entries (department_id, category_id, amount, description, entry_date, created_at) VALUES (?, ?, ?, ?, ?, NOW())');
            $stmt->execute([$departmentId, $catId, (float)$rAmount, $rDescription ?: null, $rDate]);
            $entryId = $pdo->lastInsertId();

            logDepartmentAction($pdo, $rType . '_added', 'finance', $entryId, "Added $rType: $rCategory - Tsh " . number_format($rAmount, 0));
            
            $pdo->commit();
            header('Location: view.php?success=' . $rType);
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            error_log('Finance modal add error: ' . $e->getMessage());
            $formError = 'Failed to record: ' . $e->getMessage();
            $openModal = $rType . 'Modal';
        }
    }
}

$records = [];
$search = trim($_GET['search'] ?? '');
$type = trim($_GET['type'] ?? '');
$fromDate = trim($_GET['from_date'] ?? '');
$toDate = trim($_GET['to_date'] ?? '');

try {
    // Build query
    $query = '
        SELECT 
            fe.id,
            fc.category_type as type,
            fc.name as category,
            fe.amount,
            fe.description,
            fe.entry_date AS created_at
        FROM finance_entries fe
        JOIN finance_categories fc ON fe.category_id = fc.id
        WHERE fe.department_id = ?
    ';
    
    $params = [$departmentId];
    
    if ($type && in_array($type, ['income', 'expense'])) {
        $query .= ' AND fc.category_type = ?';
        $params[] = $type;
    }
    
    if (!empty($search)) {
        $query .= ' AND (fc.name LIKE ? OR fe.description LIKE ?)';
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
    
    if (!empty($fromDate)) {
        $query .= ' AND entry_date >= ?';
        $params[] = $fromDate;
    }
    
    if (!empty($toDate)) {
        $query .= ' AND entry_date <= ?';
        $params[] = $toDate;
    }
    
    $query .= ' ORDER BY entry_date DESC, id DESC';
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calculate totals
    $totalIncome = 0;
    $totalExpense = 0;
    foreach ($records as $record) {
        if ($record['type'] === 'income') {
            $totalIncome += $record['amount'];
        } else {
            $totalExpense += $record['amount'];
        }
    }
    $balance = $totalIncome - $totalExpense;

} catch (Exception $e) {
    error_log('Finance view error: ' . $e->getMessage());
}

$pageTitle = 'Finance Records';
?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
    <div>
        <h2 class="text-2xl font-heading font-bold text-royal-800">Finance Records</h2>
        <p class="text-sm text-mist-500 mt-0.5">Income and expense tracking</p>
    </div>
    <div class="flex gap-2">
        <button onclick="showModal('incomeModal')" class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-semibold shadow transition">+ Income</button>
        <button onclick="showModal('expenseModal')" class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-red-500 hover:bg-red-600 text-white rounded-xl text-sm font-semibold shadow transition">− Expense</button>
    </div>
</div>

<?php if (isset($_GET['success'])): ?>
<div class="mb-5 p-3 bg-emerald-50 border border-emerald-200 rounded-xl text-sm text-emerald-700 font-medium">
    ✓ <?php echo $_GET['success'] === 'income' ? 'Income recorded successfully.' : 'Expense recorded successfully.'; ?>
</div>
<?php endif; ?>
<?php if ($formError): ?>
<div class="mb-5 p-3 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700 font-medium">⚠️ <?php echo htmlspecialchars($formError); ?></div>
<?php endif; ?>

<!-- Summary Cards -->
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    <div class="bg-white rounded-2xl border border-mist-200 shadow-sm p-5">
        <p class="text-xs font-semibold text-mist-400 uppercase tracking-wide">Total Income</p>
        <p class="text-2xl font-bold text-emerald-600 mt-1"><?php echo formatCurrency($totalIncome); ?></p>
    </div>
    <div class="bg-white rounded-2xl border border-mist-200 shadow-sm p-5">
        <p class="text-xs font-semibold text-mist-400 uppercase tracking-wide">Total Expenses</p>
        <p class="text-2xl font-bold text-red-500 mt-1"><?php echo formatCurrency($totalExpense); ?></p>
    </div>
    <div class="bg-white rounded-2xl border border-mist-200 shadow-sm p-5">
        <p class="text-xs font-semibold text-mist-400 uppercase tracking-wide">Balance</p>
        <p class="text-2xl font-bold mt-1 <?php echo $balance >= 0 ? 'text-royal-700' : 'text-red-600'; ?>"><?php echo formatCurrency($balance); ?></p>
    </div>
</div>

<!-- Filters -->
<form method="GET" class="bg-white rounded-2xl border border-mist-200 shadow-sm p-4 mb-5 flex flex-wrap gap-3 items-end">
    <div>
        <label class="block text-xs font-semibold text-mist-600 mb-1">Type</label>
        <select name="type" class="border border-mist-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-royal-400">
            <option value="">All</option>
            <option value="income" <?php echo $type === 'income' ? 'selected' : ''; ?>>Income</option>
            <option value="expense" <?php echo $type === 'expense' ? 'selected' : ''; ?>>Expense</option>
        </select>
    </div>
    <div class="flex-1 min-w-[160px]">
        <label class="block text-xs font-semibold text-mist-600 mb-1">Search</label>
        <input type="text" name="search" placeholder="Category or description…" value="<?php echo htmlspecialchars($search); ?>"
            class="w-full border border-mist-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-royal-400">
    </div>
    <div>
        <label class="block text-xs font-semibold text-mist-600 mb-1">From</label>
        <input type="date" name="from_date" value="<?php echo htmlspecialchars($fromDate); ?>"
            class="border border-mist-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-royal-400">
    </div>
    <div>
        <label class="block text-xs font-semibold text-mist-600 mb-1">To</label>
        <input type="date" name="to_date" value="<?php echo htmlspecialchars($toDate); ?>"
            class="border border-mist-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-royal-400">
    </div>
    <button type="submit" class="px-4 py-2 bg-royal-600 hover:bg-royal-700 text-white rounded-xl text-sm font-semibold shadow transition">Filter</button>
    <?php if ($search || $type || $fromDate || $toDate): ?>
    <a href="view.php" class="px-4 py-2 bg-mist-100 hover:bg-mist-200 text-mist-600 rounded-xl text-sm font-semibold transition">Clear</a>
    <?php endif; ?>
</form>

<!-- Table -->
<?php if (!empty($records)): ?>
<div class="bg-white rounded-2xl border border-mist-200 shadow-sm overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Date</th>
                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Type</th>
                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Category</th>
                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Description</th>
                <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Amount</th>
                <th class="px-5 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-mist-100">
        <?php foreach ($records as $record): ?>
            <tr class="hover:bg-gray-50">
                <td class="px-5 py-3 text-mist-600"><?php echo date('M d, Y', strtotime($record['created_at'])); ?></td>
                <td class="px-5 py-3">
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold <?php echo $record['type'] === 'income' ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-600'; ?>">
                        <?php echo ucfirst($record['type']); ?>
                    </span>
                </td>
                <td class="px-5 py-3 font-medium text-royal-800"><?php echo htmlspecialchars($record['category']); ?></td>
                <td class="px-5 py-3 text-mist-500"><?php echo htmlspecialchars($record['description'] ?? '—'); ?></td>
                <td class="px-5 py-3 text-right font-semibold <?php echo $record['type'] === 'income' ? 'text-emerald-600' : 'text-red-500'; ?>">
                    <?php echo ($record['type'] === 'income' ? '+' : '−') . ' ' . formatCurrency($record['amount']); ?>
                </td>
                <td class="px-5 py-3 text-right">
                    <a href="delete.php?id=<?php echo $record['id']; ?>" onclick="return confirm('Delete this record?')"
                       class="text-xs font-semibold text-red-400 hover:text-red-600 transition">Delete</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <div class="px-5 py-3 border-t border-mist-100 text-xs text-mist-400"><?php echo count($records); ?> record(s)</div>
</div>
<?php else: ?>
<div class="bg-white rounded-2xl border border-mist-200 shadow-sm p-12 text-center">
    <p class="text-mist-400 font-semibold mb-3">No financial records found.</p>
    <div class="flex justify-center gap-3">
        <button onclick="showModal('incomeModal')" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-semibold shadow transition">Record Income</button>
        <button onclick="showModal('expenseModal')" class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-xl text-sm font-semibold shadow transition">Record Expense</button>
    </div>
</div>
<?php endif; ?>

<!-- Income Modal -->
<div id="incomeModal" class="fixed inset-0 z-50 hidden items-center justify-center">
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" onclick="hideModal('incomeModal')"></div>
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 p-6 z-10">
        <div class="flex items-center justify-between mb-5">
            <h3 class="text-lg font-heading font-bold text-royal-800">Record Income</h3>
            <button onclick="hideModal('incomeModal')" class="text-mist-400 hover:text-mist-600 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form method="POST" class="space-y-4">
            <input type="hidden" name="record_type" value="income">
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-semibold text-mist-700 mb-1.5">Date *</label>
                    <input type="date" name="date" required value="<?php echo htmlspecialchars($openModal === 'incomeModal' ? ($savedPost['date'] ?? date('Y-m-d')) : date('Y-m-d')); ?>"
                        class="w-full border border-mist-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-royal-400 focus:ring-2 focus:ring-royal-100">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-mist-700 mb-1.5">Category *</label>
                    <select name="category" required class="w-full border border-mist-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-royal-400 focus:ring-2 focus:ring-royal-100">
                        <option value="">Select…</option>
                        <?php foreach (['Tithe','Offering','Donation','Event Revenue','Grants','Other'] as $c): ?>
                        <option value="<?php echo $c; ?>" <?php echo ($savedPost['category'] ?? '') === $c && $openModal === 'incomeModal' ? 'selected' : ''; ?>><?php echo $c; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div>
                <label class="block text-sm font-semibold text-mist-700 mb-1.5">Amount (Tsh) *</label>
                <input type="number" name="amount" required step="0.01" min="0" placeholder="0"
                    value="<?php echo htmlspecialchars($openModal === 'incomeModal' ? ($savedPost['amount'] ?? '') : ''); ?>"
                    class="w-full border border-mist-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-royal-400 focus:ring-2 focus:ring-royal-100">
            </div>
            <div>
                <label class="block text-sm font-semibold text-mist-700 mb-1.5">Description</label>
                <textarea name="description" rows="2" placeholder="Optional details…"
                    class="w-full border border-mist-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-royal-400 focus:ring-2 focus:ring-royal-100 resize-none"><?php echo htmlspecialchars($openModal === 'incomeModal' ? ($savedPost['description'] ?? '') : ''); ?></textarea>
            </div>
            <div class="flex gap-3 pt-1">
                <button type="submit" class="flex-1 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-semibold shadow transition">Save Income</button>
                <button type="button" onclick="hideModal('incomeModal')" class="flex-1 py-2.5 bg-mist-100 hover:bg-mist-200 text-mist-700 rounded-xl text-sm font-semibold transition">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- Expense Modal -->
<div id="expenseModal" class="fixed inset-0 z-50 hidden items-center justify-center">
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" onclick="hideModal('expenseModal')"></div>
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 p-6 z-10">
        <div class="flex items-center justify-between mb-5">
            <h3 class="text-lg font-heading font-bold text-royal-800">Record Expense</h3>
            <button onclick="hideModal('expenseModal')" class="text-mist-400 hover:text-mist-600 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form method="POST" class="space-y-4">
            <input type="hidden" name="record_type" value="expense">
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-semibold text-mist-700 mb-1.5">Date *</label>
                    <input type="date" name="date" required value="<?php echo htmlspecialchars($openModal === 'expenseModal' ? ($savedPost['date'] ?? date('Y-m-d')) : date('Y-m-d')); ?>"
                        class="w-full border border-mist-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-royal-400 focus:ring-2 focus:ring-royal-100">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-mist-700 mb-1.5">Category *</label>
                    <select name="category" required class="w-full border border-mist-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-royal-400 focus:ring-2 focus:ring-royal-100">
                        <option value="">Select…</option>
                        <?php foreach (['Supplies','Maintenance','Utilities','Event','Procurement','Honorarium','Other'] as $c): ?>
                        <option value="<?php echo $c; ?>" <?php echo ($savedPost['category'] ?? '') === $c && $openModal === 'expenseModal' ? 'selected' : ''; ?>><?php echo $c === 'Event' ? 'Event Expenses' : $c; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div>
                <label class="block text-sm font-semibold text-mist-700 mb-1.5">Amount (Tsh) *</label>
                <input type="number" name="amount" required step="0.01" min="0" placeholder="0"
                    value="<?php echo htmlspecialchars($openModal === 'expenseModal' ? ($savedPost['amount'] ?? '') : ''); ?>"
                    class="w-full border border-mist-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-royal-400 focus:ring-2 focus:ring-royal-100">
            </div>
            <div>
                <label class="block text-sm font-semibold text-mist-700 mb-1.5">Description</label>
                <textarea name="description" rows="2" placeholder="Optional details…"
                    class="w-full border border-mist-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-royal-400 focus:ring-2 focus:ring-royal-100 resize-none"><?php echo htmlspecialchars($openModal === 'expenseModal' ? ($savedPost['description'] ?? '') : ''); ?></textarea>
            </div>
            <div class="flex gap-3 pt-1">
                <button type="submit" class="flex-1 py-2.5 bg-red-500 hover:bg-red-600 text-white rounded-xl text-sm font-semibold shadow transition">Save Expense</button>
                <button type="button" onclick="hideModal('expenseModal')" class="flex-1 py-2.5 bg-mist-100 hover:bg-mist-200 text-mist-700 rounded-xl text-sm font-semibold transition">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
function showModal(id) {
    const el = document.getElementById(id);
    el.classList.remove('hidden');
    el.classList.add('flex');
    document.body.style.overflow = 'hidden';
}
function hideModal(id) {
    const el = document.getElementById(id);
    el.classList.add('hidden');
    el.classList.remove('flex');
    document.body.style.overflow = '';
}
<?php if ($openModal): ?>
document.addEventListener('DOMContentLoaded', () => showModal('<?php echo $openModal; ?>'));
<?php endif; ?>
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>

<?php
// logDepartmentAction is now centralized in includes/session.php
