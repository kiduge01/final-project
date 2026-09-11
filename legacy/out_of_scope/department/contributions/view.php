<?php
/**
 * Department Contributions View
 * 
 * Manage contributions received by the department.
 */

require_once __DIR__ . '/../includes/auth_check.php';

$pdo = require __DIR__ . '/../includes/db.php';
$departmentId = getCurrentDepartmentId();
$pageTitle = 'Contributions';

$formError = '';
$openModal = '';
$savedPost = [];

// Handle CSV template download
if (isset($_GET['dl_template'])) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="contributions_import_template.csv"');
    echo "member_code,phone,contributor_name,amount,payment_method,purpose,date\n";
    echo ",+255712345001,,25000,mpesa,Choir support,2026-06-30\n";
    echo "MBR-2026-0001,,,50000,cash,Building fund,2026-06-30\n";
    echo ",,Joseph Kamau,15000,cash,Sunday Offering,2026-06-30\n";
    exit;
}

// --- CSV Import handler ---
$csvImportResult = ['success' => 0, 'skipped' => 0, 'errors' => []];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['_modal_csv_import'])) {
    if (isset($_FILES['csv_file']) && $_FILES['csv_file']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['csv_file']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['csv','txt'], true)) {
            $csvImportResult['errors'][] = 'Only CSV files are accepted.';
            $openModal = 'csvImportModal';
        } else {
            $handle = fopen($_FILES['csv_file']['tmp_name'], 'r');
            $header = fgetcsv($handle); // skip header row
            $rowNum = 1;
            
            while (($row = fgetcsv($handle)) !== false) {
                $rowNum++;
                if (count($row) === 1 && empty($row[0])) {
                    continue; // Skip empty rows
                }
                if (count($row) < 4) { 
                    $csvImportResult['errors'][] = "Row $rowNum: Too few columns."; 
                    $csvImportResult['skipped']++;
                    continue; 
                }
                
                $mCode       = trim($row[0] ?? '');
                $mPhone      = trim($row[1] ?? '');
                $contribName = trim($row[2] ?? '');
                $amount      = trim($row[3] ?? '');
                $payMethod   = strtolower(trim($row[4] ?? 'cash'));
                $purpose     = trim($row[5] ?? '');
                $date        = trim($row[6] ?? '') ?: date('Y-m-d');
                
                if (empty($amount) || !is_numeric($amount) || (float)$amount <= 0) { 
                    $csvImportResult['errors'][] = "Row $rowNum: Invalid or missing amount."; 
                    $csvImportResult['skipped']++;
                    continue; 
                }
                
                if (!in_array($payMethod, ['cash','mpesa','cheque','bank_transfer','other'], true)) {
                    $payMethod = 'cash';
                }
                
                // Match member in department
                $memberId = null;
                if (!empty($mCode)) {
                    $mStmt = $pdo->prepare('
                        SELECT m.id 
                        FROM members m 
                        INNER JOIN department_members dm ON m.id = dm.member_id 
                        WHERE dm.department_id = ? AND m.member_code = ?
                    ');
                    $mStmt->execute([$departmentId, $mCode]);
                    $memberId = $mStmt->fetchColumn() ?: null;
                }
                
                if (!$memberId && !empty($mPhone)) {
                    $mStmt = $pdo->prepare('
                        SELECT m.id 
                        FROM members m 
                        INNER JOIN department_members dm ON m.id = dm.member_id 
                        WHERE dm.department_id = ? AND m.phone = ?
                    ');
                    $mStmt->execute([$departmentId, $mPhone]);
                    $memberId = $mStmt->fetchColumn() ?: null;
                }
                
                if (!$memberId && empty($contribName)) {
                    $csvImportResult['errors'][] = "Row $rowNum: Contributor name required for external contributions.";
                    $csvImportResult['skipped']++;
                    continue;
                }
                
                try {
                    $pdo->beginTransaction();
                    
                    $headName = function_exists('getCurrentHeadName') ? getCurrentHeadName() : '';
                    $stmt = $pdo->prepare('
                        INSERT INTO department_contributions (
                            department_id, member_id, contributor_name, amount, 
                            payment_method, purpose, contribution_date, recorded_by
                        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                    ');
                    $stmt->execute([
                        $departmentId,
                        $memberId,
                        $memberId ? null : $contribName,
                        (float)$amount,
                        $payMethod,
                        $purpose ?: null,
                        $date,
                        $headName
                    ]);
                    
                    $newContribId = $pdo->lastInsertId();
                    
                    logDepartmentAction(
                        $pdo, 'contribution_added', 'contribution', $newContribId,
                        "Imported contribution: Tsh " . number_format($amount) . " from " . ($memberId ? "member" : $contribName)
                    );
                    
                    $pdo->commit();
                    $csvImportResult['success']++;
                } catch (Exception $e) {
                    if ($pdo->inTransaction()) {
                        $pdo->rollBack();
                    }
                    $csvImportResult['errors'][] = "Row $rowNum: Database error - " . $e->getMessage();
                    $csvImportResult['skipped']++;
                }
            }
            fclose($handle);
            
            if ($csvImportResult['success'] > 0 && empty($csvImportResult['errors'])) {
                header('Location: view.php?csv_imported=' . $csvImportResult['success']);
                exit;
            }
            $openModal = 'csvImportModal';
        }
    } else {
        $csvImportResult['errors'][] = 'Please select a valid CSV file to upload.';
        $openModal = 'csvImportModal';
    }
}


// Fetch department members for the contribution dropdown
$membersStmt = $pdo->prepare('
    SELECT m.id, m.first_name, m.last_name, m.phone 
    FROM members m
    INNER JOIN department_members dm ON m.id = dm.member_id
    WHERE dm.department_id = ?
    ORDER BY m.first_name, m.last_name
');
$membersStmt->execute([$departmentId]);
$deptMembers = $membersStmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

// Handle inline modal form submissions (Add / Edit)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $savedPost = $_POST;
    
    $contribId = isset($_POST['contribution_id']) ? (int)$_POST['contribution_id'] : null;
    $contribType = trim($_POST['contributor_type'] ?? 'member');
    $memberId = trim($_POST['member_id'] ?? '');
    $customName = trim($_POST['contributor_name'] ?? '');
    $amount = trim($_POST['amount'] ?? '');
    $paymentMethod = trim($_POST['payment_method'] ?? 'cash');
    $purpose = trim($_POST['purpose'] ?? '');
    $date = trim($_POST['date'] ?? '');
    
    // Validations
    if (empty($date) || empty($amount)) {
        $formError = 'Date and amount are required.';
        $openModal = $action . 'Modal';
    } elseif (!is_numeric($amount) || (float)$amount <= 0) {
        $formError = 'Amount must be a valid positive number.';
        $openModal = $action . 'Modal';
    } elseif ($contribType === 'member' && empty($memberId)) {
        $formError = 'Please select a member.';
        $openModal = $action . 'Modal';
    } elseif ($contribType === 'custom' && empty($customName)) {
        $formError = 'Contributor name is required for other/non-member contributions.';
        $openModal = $action . 'Modal';
    } else {
        $dbMemberId = $contribType === 'member' ? (int)$memberId : null;
        $dbCustomName = $contribType === 'custom' ? $customName : null;
        $headName = function_exists('getCurrentHeadName') ? getCurrentHeadName() : '';
        
        try {
            if ($action === 'add') {
                $stmt = $pdo->prepare('
                    INSERT INTO department_contributions (
                        department_id, member_id, contributor_name, amount, 
                        payment_method, purpose, contribution_date, recorded_by
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ');
                $stmt->execute([
                    $departmentId, $dbMemberId, $dbCustomName, (float)$amount,
                    $paymentMethod, $purpose ?: null, $date, $headName
                ]);
                
                logDepartmentAction(
                    $pdo, 'contribution_added', 'contribution', $pdo->lastInsertId(),
                    "Added contribution of Tsh " . number_format($amount) . " from " . ($contribType === 'member' ? "member" : $customName)
                );
                
                header('Location: view.php?success=add');
                exit;
            } elseif ($action === 'edit' && $contribId) {
                // Verify ownership first
                $chk = $pdo->prepare('SELECT id FROM department_contributions WHERE id = ? AND department_id = ?');
                $chk->execute([$contribId, $departmentId]);
                if ($chk->fetch()) {
                    $stmt = $pdo->prepare('
                        UPDATE department_contributions 
                        SET member_id = ?, contributor_name = ?, amount = ?, 
                            payment_method = ?, purpose = ?, contribution_date = ?
                        WHERE id = ? AND department_id = ?
                    ');
                    $stmt->execute([
                        $dbMemberId, $dbCustomName, (float)$amount,
                        $paymentMethod, $purpose ?: null, $date, $contribId, $departmentId
                    ]);
                    
                    logDepartmentAction(
                        $pdo, 'contribution_updated', 'contribution', $contribId,
                        "Updated contribution ID $contribId"
                    );
                    
                    header('Location: view.php?success=edit');
                    exit;
                } else {
                    $formError = 'Permission denied.';
                }
            }
        } catch (Exception $e) {
            error_log('Contribution save error: ' . $e->getMessage());
            $formError = 'Failed to save contribution. Please try again.';
            $openModal = $action . 'Modal';
        }
    }
}

// Filters and Search
$search = trim($_GET['search'] ?? '');
$fromDate = trim($_GET['from_date'] ?? '');
$toDate = trim($_GET['to_date'] ?? '');

$records = [];
$totalAmount = 0;
$monthTotal = 0;
$uniqueContributors = 0;

try {
    // Build query
    $query = '
        SELECT 
            dc.id,
            dc.member_id,
            dc.contributor_name,
            dc.amount,
            dc.payment_method,
            dc.purpose,
            dc.contribution_date,
            dc.recorded_by,
            m.first_name,
            m.last_name
        FROM department_contributions dc
        LEFT JOIN members m ON m.id = dc.member_id
        WHERE dc.department_id = ?
    ';
    
    $params = [$departmentId];
    
    if (!empty($search)) {
        $query .= ' AND (dc.contributor_name LIKE ? OR m.first_name LIKE ? OR m.last_name LIKE ? OR dc.purpose LIKE ?)';
        $searchVal = "%$search%";
        $params[] = $searchVal;
        $params[] = $searchVal;
        $params[] = $searchVal;
        $params[] = $searchVal;
    }
    
    if (!empty($fromDate)) {
        $query .= ' AND dc.contribution_date >= ?';
        $params[] = $fromDate;
    }
    
    if (!empty($toDate)) {
        $query .= ' AND dc.contribution_date <= ?';
        $params[] = $toDate;
    }
    
    $query .= ' ORDER BY dc.contribution_date DESC, dc.id DESC';
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    
    // Calculate statistics
    foreach ($records as $r) {
        $totalAmount += (float)$r['amount'];
        if (date('Y-m', strtotime($r['contribution_date'])) === date('Y-m')) {
            $monthTotal += (float)$r['amount'];
        }
    }
    
    // Unique contributors calculation
    $uniqueStmt = $pdo->prepare('
        SELECT COUNT(DISTINCT COALESCE(member_id, contributor_name)) as count
        FROM department_contributions
        WHERE department_id = ?
    ');
    $uniqueStmt->execute([$departmentId]);
    $uniqueContributors = $uniqueStmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;
    
} catch (Exception $e) {
    error_log('Contributions list fetch error: ' . $e->getMessage());
}

require_once __DIR__ . '/../includes/header.php';
?>

<!-- Header -->
<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
    <div>
        <h2 class="text-2xl font-heading font-bold text-royal-800">Department Contributions</h2>
        <p class="text-sm text-mist-500 mt-0.5">Track and manage member and external contributions (michango)</p>
    </div>
    <div class="flex gap-2">
        <button onclick="showAddModal()" class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-royal-600 hover:bg-royal-700 text-white rounded-xl text-sm font-semibold shadow transition-all">+ Record Contribution</button>
        <button onclick="showModal('csvImportModal')" class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-glory-600 hover:bg-glory-700 text-white rounded-xl text-sm font-semibold shadow transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
            Import CSV
        </button>
    </div>
</div>

<?php if (isset($_GET['success'])): ?>
<div class="mb-5 p-3 bg-emerald-50 border border-emerald-200 rounded-xl text-sm text-emerald-700 font-medium">
    ✓ <?php echo $_GET['success'] === 'add' ? 'Contribution recorded successfully.' : 'Contribution updated successfully.'; ?>
</div>
<?php endif; ?>

<?php if (isset($_GET['deleted'])): ?>
<div class="mb-5 p-3 bg-amber-50 border border-amber-200 rounded-xl text-sm text-amber-700 font-medium">
    ✓ Contribution deleted successfully.
</div>
<?php endif; ?>

<?php if (isset($_GET['csv_imported'])): ?>
<div class="mb-5 p-3 bg-emerald-50 border border-emerald-200 rounded-xl text-sm text-emerald-700 font-medium">
    ✓ Bulk Import successful: <?php echo htmlspecialchars($_GET['csv_imported']); ?> contribution(s) imported.
</div>
<?php endif; ?>

<?php if (!empty($csvImportResult['errors'])): ?>
<div class="mb-5 p-4 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700">
    <h4 class="font-bold mb-2">⚠️ Import finished with some warnings/errors:</h4>
    <p class="mb-2 text-xs font-semibold">Successful: <?php echo $csvImportResult['success']; ?> | Skipped: <?php echo $csvImportResult['skipped']; ?></p>
    <ul class="list-disc pl-5 space-y-1 text-xs max-h-[150px] overflow-y-auto">
        <?php foreach ($csvImportResult['errors'] as $err): ?>
            <li><?php echo htmlspecialchars($err); ?></li>
        <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

<?php if ($formError): ?>
<div class="mb-5 p-3 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700 font-medium">⚠️ <?php echo htmlspecialchars($formError); ?></div>
<?php endif; ?>

<!-- Summary KPI Cards -->
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    <div class="bg-white rounded-2xl border border-mist-200 shadow-sm p-5 hover:shadow-md transition-all">
        <p class="text-xs font-semibold text-mist-400 uppercase tracking-wide">Total Contributions</p>
        <p class="text-2xl font-bold text-royal-700 mt-1"><?= formatCurrency($totalAmount) ?></p>
    </div>
    <div class="bg-white rounded-2xl border border-mist-200 shadow-sm p-5 hover:shadow-md transition-all">
        <p class="text-xs font-semibold text-mist-400 uppercase tracking-wide">This Month (<?= date('M Y') ?>)</p>
        <p class="text-2xl font-bold text-emerald-600 mt-1"><?= formatCurrency($monthTotal) ?></p>
    </div>
    <div class="bg-white rounded-2xl border border-mist-200 shadow-sm p-5 hover:shadow-md transition-all">
        <p class="text-xs font-semibold text-mist-400 uppercase tracking-wide">Unique Contributors</p>
        <p class="text-2xl font-bold text-mist-800 mt-1"><?= $uniqueContributors ?></p>
    </div>
</div>

<!-- Search & Filters -->
<form method="GET" class="bg-white rounded-2xl border border-mist-200 shadow-sm p-4 mb-5 flex flex-wrap gap-3 items-end">
    <div class="flex-1 min-w-[200px]">
        <label class="block text-xs font-semibold text-mist-600 mb-1">Search</label>
        <input type="text" name="search" placeholder="Contributor name or purpose…" value="<?php echo htmlspecialchars($search); ?>"
            class="w-full border border-mist-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-royal-400">
    </div>
    <div>
        <label class="block text-xs font-semibold text-mist-600 mb-1">From Date</label>
        <input type="date" name="from_date" value="<?php echo htmlspecialchars($fromDate); ?>"
            class="border border-mist-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-royal-400">
    </div>
    <div>
        <label class="block text-xs font-semibold text-mist-600 mb-1">To Date</label>
        <input type="date" name="to_date" value="<?php echo htmlspecialchars($toDate); ?>"
            class="border border-mist-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-royal-400">
    </div>
    <button type="submit" class="px-4 py-2 bg-royal-600 hover:bg-royal-700 text-white rounded-xl text-sm font-semibold shadow transition-all">Filter</button>
    <?php if ($search || $fromDate || $toDate): ?>
    <a href="view.php" class="px-4 py-2 bg-mist-100 hover:bg-mist-200 text-mist-600 rounded-xl text-sm font-semibold transition-all">Clear</a>
    <?php endif; ?>
</form>

<!-- Contributions Table -->
<?php if (!empty($records)): ?>
<div class="bg-white rounded-2xl border border-mist-200 shadow-sm overflow-x-auto">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-mist-100">
                <tr>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Date</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Contributor</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Method</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Purpose</th>
                    <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Amount</th>
                    <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-mist-100">
            <?php foreach ($records as $r): ?>
                <?php 
                    $contributorName = $r['member_id'] 
                        ? htmlspecialchars($r['first_name'] . ' ' . $r['last_name']) 
                        : htmlspecialchars($r['contributor_name']);
                    $isMember = $r['member_id'] ? true : false;
                ?>
                <tr class="hover:bg-gray-50/80 transition-colors">
                    <td class="px-5 py-3.5 text-mist-600 whitespace-nowrap"><?php echo date('M d, Y', strtotime($r['contribution_date'])); ?></td>
                    <td class="px-5 py-3.5">
                        <div class="flex items-center gap-2">
                            <span class="font-medium text-mist-900"><?php echo $contributorName; ?></span>
                            <?php if ($isMember): ?>
                                <span class="px-1.5 py-0.5 rounded bg-royal-50 text-[10px] text-royal-700 font-bold uppercase tracking-wider">Member</span>
                            <?php else: ?>
                                <span class="px-1.5 py-0.5 rounded bg-mist-100 text-[10px] text-mist-600 font-bold uppercase tracking-wider">External</span>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td class="px-5 py-3.5 text-mist-600 whitespace-nowrap uppercase text-xs"><?php echo htmlspecialchars($r['payment_method']); ?></td>
                    <td class="px-5 py-3.5 text-mist-500"><?php echo htmlspecialchars($r['purpose'] ?: '—'); ?></td>
                    <td class="px-5 py-3.5 text-right font-bold text-royal-900 whitespace-nowrap">
                        <?php echo formatCurrency($r['amount']); ?>
                    </td>
                    <td class="px-5 py-3.5 text-right whitespace-nowrap">
                        <div class="flex items-center justify-end gap-2">
                            <button onclick='showEditModal(<?php echo json_encode($r); ?>)' 
                                class="text-xs font-semibold text-royal-600 hover:text-royal-800 transition-colors">Edit</button>
                            <span class="text-mist-300">|</span>
                            <a href="delete.php?id=<?php echo $r['id']; ?>" onclick="return confirm('Are you sure you want to delete this contribution?')"
                               class="text-xs font-semibold text-red-500 hover:text-red-700 transition-colors">Delete</a>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="px-5 py-3 border-t border-mist-100 text-xs text-mist-400"><?php echo count($records); ?> transaction(s) found</div>
</div>
<?php else: ?>
<div class="bg-white rounded-2xl border border-mist-200 shadow-sm p-12 text-center">
    <svg class="w-16 h-16 text-mist-300 mx-auto mb-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.879m4.02-4.024a3 3 0 11-4.243-4.243m4.242 4.242L9.88 9.88" />
    </svg>
    <p class="text-mist-500 font-semibold mb-3">No contributions found.</p>
    <button onclick="showAddModal()" class="px-4 py-2 bg-royal-600 hover:bg-royal-700 text-white rounded-xl text-sm font-semibold shadow transition-all">+ Record Contribution</button>
</div>
<?php endif; ?>

<!-- ═════════════ MODALS ═════════════ -->

<!-- Record Contribution Modal (Add) -->
<div id="addModal" class="fixed inset-0 z-50 hidden items-center justify-center">
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" onclick="hideModal('addModal')"></div>
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 p-6 z-10">
        <div class="flex items-center justify-between mb-5">
            <h3 class="text-lg font-heading font-bold text-royal-800">Record Contribution</h3>
            <button onclick="hideModal('addModal')" class="text-mist-400 hover:text-mist-600 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form method="POST" class="space-y-4">
            <input type="hidden" name="action" value="add">
            
            <!-- Contributor Type selection -->
            <div>
                <label class="block text-sm font-semibold text-mist-700 mb-1.5">Contributor Type</label>
                <div class="flex gap-4">
                    <label class="inline-flex items-center">
                        <input type="radio" name="contributor_type" value="member" checked class="form-radio text-royal-600 focus:ring-royal-500" onchange="toggleContributorType(this.value, 'add')">
                        <span class="ml-2 text-sm text-mist-700">Department Member</span>
                    </label>
                    <label class="inline-flex items-center">
                        <input type="radio" name="contributor_type" value="custom" class="form-radio text-royal-600 focus:ring-royal-500" onchange="toggleContributorType(this.value, 'add')">
                        <span class="ml-2 text-sm text-mist-700">Other / Non-Member</span>
                    </label>
                </div>
            </div>

            <!-- Member Dropdown -->
            <div id="add-member-select-group">
                <label class="block text-sm font-semibold text-mist-700 mb-1.5">Select Member *</label>
                <select name="member_id" class="w-full border border-mist-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-royal-400 focus:ring-2 focus:ring-royal-100">
                    <option value="">Choose a member...</option>
                    <?php foreach ($deptMembers as $m): ?>
                    <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['first_name'] . ' ' . $m['last_name']) ?> (<?= htmlspecialchars($m['phone']) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Custom Contributor Name -->
            <div id="add-custom-name-group" class="hidden">
                <label class="block text-sm font-semibold text-mist-700 mb-1.5">Contributor Name *</label>
                <input type="text" name="contributor_name" placeholder="Enter full name..."
                    class="w-full border border-mist-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-royal-400 focus:ring-2 focus:ring-royal-100">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-semibold text-mist-700 mb-1.5">Amount (Tsh) *</label>
                    <input type="number" name="amount" required min="0.01" step="0.01" placeholder="0.00"
                        class="w-full border border-mist-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-royal-400 focus:ring-2 focus:ring-royal-100">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-mist-700 mb-1.5">Date *</label>
                    <input type="date" name="date" required value="<?php echo date('Y-m-d'); ?>"
                        class="w-full border border-mist-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-royal-400 focus:ring-2 focus:ring-royal-100">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-semibold text-mist-700 mb-1.5">Payment Method</label>
                    <select name="payment_method" class="w-full border border-mist-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-royal-400 focus:ring-2 focus:ring-royal-100">
                        <option value="cash">Cash</option>
                        <option value="mpesa">M-Pesa</option>
                        <option value="cheque">Cheque</option>
                        <option value="bank_transfer">Bank Transfer</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-mist-700 mb-1.5">Purpose / Description</label>
                    <input type="text" name="purpose" placeholder="e.g. Building fund, Choir"
                        class="w-full border border-mist-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-royal-400 focus:ring-2 focus:ring-royal-100">
                </div>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit" class="flex-1 py-2.5 bg-royal-600 hover:bg-royal-700 text-white rounded-xl text-sm font-semibold shadow transition">Save</button>
                <button type="button" onclick="hideModal('addModal')" class="flex-1 py-2.5 bg-mist-100 hover:bg-mist-200 text-mist-700 rounded-xl text-sm font-semibold transition">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Contribution Modal -->
<div id="editModal" class="fixed inset-0 z-50 hidden items-center justify-center">
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" onclick="hideModal('editModal')"></div>
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 p-6 z-10">
        <div class="flex items-center justify-between mb-5">
            <h3 class="text-lg font-heading font-bold text-royal-800">Edit Contribution</h3>
            <button onclick="hideModal('editModal')" class="text-mist-400 hover:text-mist-600 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form method="POST" class="space-y-4">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="contribution_id" id="edit-id">
            
            <!-- Contributor Type selection -->
            <div>
                <label class="block text-sm font-semibold text-mist-700 mb-1.5">Contributor Type</label>
                <div class="flex gap-4">
                    <label class="inline-flex items-center">
                        <input type="radio" name="contributor_type" id="edit-type-member" value="member" class="form-radio text-royal-600 focus:ring-royal-500" onchange="toggleContributorType(this.value, 'edit')">
                        <span class="ml-2 text-sm text-mist-700">Department Member</span>
                    </label>
                    <label class="inline-flex items-center">
                        <input type="radio" name="contributor_type" id="edit-type-custom" value="custom" class="form-radio text-royal-600 focus:ring-royal-500" onchange="toggleContributorType(this.value, 'edit')">
                        <span class="ml-2 text-sm text-mist-700">Other / Non-Member</span>
                    </label>
                </div>
            </div>

            <!-- Member Dropdown -->
            <div id="edit-member-select-group">
                <label class="block text-sm font-semibold text-mist-700 mb-1.5">Select Member *</label>
                <select name="member_id" id="edit-member-id" class="w-full border border-mist-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-royal-400 focus:ring-2 focus:ring-royal-100">
                    <option value="">Choose a member...</option>
                    <?php foreach ($deptMembers as $m): ?>
                    <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['first_name'] . ' ' . $m['last_name']) ?> (<?= htmlspecialchars($m['phone']) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Custom Contributor Name -->
            <div id="edit-custom-name-group" class="hidden">
                <label class="block text-sm font-semibold text-mist-700 mb-1.5">Contributor Name *</label>
                <input type="text" name="contributor_name" id="edit-contributor-name" placeholder="Enter full name..."
                    class="w-full border border-mist-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-royal-400 focus:ring-2 focus:ring-royal-100">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-semibold text-mist-700 mb-1.5">Amount (Tsh) *</label>
                    <input type="number" name="amount" id="edit-amount" required min="0.01" step="0.01" placeholder="0.00"
                        class="w-full border border-mist-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-royal-400 focus:ring-2 focus:ring-royal-100">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-mist-700 mb-1.5">Date *</label>
                    <input type="date" name="date" id="edit-date" required
                        class="w-full border border-mist-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-royal-400 focus:ring-2 focus:ring-royal-100">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-semibold text-mist-700 mb-1.5">Payment Method</label>
                    <select name="payment_method" id="edit-payment-method" class="w-full border border-mist-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-royal-400 focus:ring-2 focus:ring-royal-100">
                        <option value="cash">Cash</option>
                        <option value="mpesa">M-Pesa</option>
                        <option value="cheque">Cheque</option>
                        <option value="bank_transfer">Bank Transfer</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-mist-700 mb-1.5">Purpose / Description</label>
                    <input type="text" name="purpose" id="edit-purpose" placeholder="e.g. Building fund, Choir"
                        class="w-full border border-mist-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-royal-400 focus:ring-2 focus:ring-royal-100">
                </div>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit" class="flex-1 py-2.5 bg-royal-600 hover:bg-royal-700 text-white rounded-xl text-sm font-semibold shadow transition">Save Changes</button>
                <button type="button" onclick="hideModal('editModal')" class="flex-1 py-2.5 bg-mist-100 hover:bg-mist-200 text-mist-700 rounded-xl text-sm font-semibold transition">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- CSV Import Modal -->
<div id="csvImportModal" class="fixed inset-0 z-50 hidden items-center justify-center">
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" onclick="hideModal('csvImportModal')"></div>
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 p-6 z-10">
        <div class="flex items-center justify-between mb-5">
            <h3 class="text-lg font-heading font-bold text-royal-800">Import Contributions via CSV</h3>
            <button onclick="hideModal('csvImportModal')" class="text-mist-400 hover:text-mist-600 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form method="POST" enctype="multipart/form-data" class="space-y-4">
            <input type="hidden" name="_modal_csv_import" value="1">
            
            <div class="border-2 border-dashed border-mist-200 rounded-2xl p-6 text-center cursor-pointer hover:border-royal-400 hover:bg-royal-50/30 transition-all"
                 onclick="document.getElementById('csv-file-input').click()">
                <svg class="w-10 h-10 text-mist-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                </svg>
                <p class="text-sm font-semibold text-mist-700">Click to select CSV file</p>
                <p class="text-xs text-mist-400 mt-1">Accepts .csv and .txt files</p>
                <input type="file" id="csv-file-input" name="csv_file" class="hidden" required onchange="updateFileNameDisplay(this)">
                <p id="csv-file-name" class="text-xs text-royal-600 font-semibold mt-2 hidden"></p>
            </div>

            <div class="p-3 bg-mist-50 rounded-xl text-xs text-mist-500 space-y-1">
                <p class="font-bold text-mist-700">CSV Column Structure:</p>
                <ol class="list-decimal pl-4 space-y-0.5">
                    <li><strong>member_code</strong>: Optional (matches dept member)</li>
                    <li><strong>phone</strong>: Optional (matches dept member)</li>
                    <li><strong>contributor_name</strong>: Required if member not found</li>
                    <li><strong>amount</strong>: Required positive number</li>
                    <li><strong>payment_method</strong>: cash/mpesa/cheque/bank_transfer/other</li>
                    <li><strong>purpose</strong>: Optional contribution detail</li>
                    <li><strong>date</strong>: Optional (defaults to today)</li>
                </ol>
            </div>

            <div class="flex items-center justify-between text-xs border-t border-mist-100 pt-3">
                <a href="?dl_template=1" class="text-royal-600 hover:text-royal-800 font-bold flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    Download Template
                </a>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit" class="flex-1 py-2.5 bg-royal-600 hover:bg-royal-700 text-white rounded-xl text-sm font-semibold shadow transition">Upload and Import</button>
                <button type="button" onclick="hideModal('csvImportModal')" class="flex-1 py-2.5 bg-mist-100 hover:bg-mist-200 text-mist-700 rounded-xl text-sm font-semibold transition">Cancel</button>
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

function toggleContributorType(val, prefix) {
    const memberGroup = document.getElementById(prefix + '-member-select-group');
    const customGroup = document.getElementById(prefix + '-custom-name-group');
    
    if (val === 'member') {
        memberGroup.classList.remove('hidden');
        customGroup.classList.add('hidden');
        // Require member select, unrequire custom input
        memberGroup.querySelector('select').setAttribute('required', 'required');
        customGroup.querySelector('input').removeAttribute('required');
    } else {
        memberGroup.classList.add('hidden');
        customGroup.classList.remove('hidden');
        // Require custom input, unrequire member select
        customGroup.querySelector('input').setAttribute('required', 'required');
        memberGroup.querySelector('select').removeAttribute('required');
    }
}

function showAddModal() {
    // Reset form fields
    const form = document.querySelector('#addModal form');
    form.reset();
    toggleContributorType('member', 'add');
    showModal('addModal');
}

function showEditModal(r) {
    document.getElementById('edit-id').value = r.id;
    document.getElementById('edit-amount').value = r.amount;
    document.getElementById('edit-date').value = r.contribution_date;
    document.getElementById('edit-payment-method').value = r.payment_method;
    document.getElementById('edit-purpose').value = r.purpose || '';
    
    if (r.member_id) {
        document.getElementById('edit-type-member').checked = true;
        document.getElementById('edit-member-id').value = r.member_id;
        toggleContributorType('member', 'edit');
    } else {
        document.getElementById('edit-type-custom').checked = true;
        document.getElementById('edit-contributor-name').value = r.contributor_name;
        toggleContributorType('custom', 'edit');
    }
    
    showModal('editModal');
}

function updateFileNameDisplay(input) {
    const el = document.getElementById('csv-file-name');
    if (input.files && input.files[0]) {
        el.textContent = 'Selected: ' + input.files[0].name;
        el.classList.remove('hidden');
    } else {
        el.classList.add('hidden');
    }
}

<?php if ($openModal): ?>
document.addEventListener('DOMContentLoaded', () => {
    <?php if ($openModal === 'addModal'): ?>
        showAddModal();
    <?php elseif ($openModal === 'editModal'): ?>
        showEditModal(<?= json_encode($savedPost) ?>);
    <?php elseif ($openModal === 'csvImportModal'): ?>
        showModal('csvImportModal');
    <?php endif; ?>
});
<?php endif; ?>
</script>

<?php 
require_once __DIR__ . '/../includes/footer.php'; 

// logDepartmentAction is now centralized in includes/session.php
?>
