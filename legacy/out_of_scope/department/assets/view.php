<?php
/**
 * Department Assets View
 * 
 * Manage assets owned by the department
 */

require_once __DIR__ . '/../includes/auth_check.php';

$pdo = require __DIR__ . '/../includes/db.php';
$departmentId = getCurrentDepartmentId();
$pageTitle = 'Assets';

$formError = '';
$openModal = '';
$savedPost = [];

// Handle inline modal form submissions (Add / Edit)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $savedPost = $_POST;
    
    $assetId = isset($_POST['asset_id']) ? (int)$_POST['asset_id'] : null;
    $name = trim($_POST['name'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $location = trim($_POST['current_location'] ?? '');
    $purchaseDate = trim($_POST['purchase_date'] ?? '');
    $purchaseValue = trim($_POST['purchase_value'] ?? '');
    $condition = trim($_POST['condition_status'] ?? 'good');
    $notes = trim($_POST['notes'] ?? '');
    
    // Validations
    if (empty($name) || empty($category) || empty($location)) {
        $formError = 'Asset name, category, and location are required.';
        $openModal = $action . 'Modal';
    } elseif (!empty($purchaseValue) && (!is_numeric($purchaseValue) || (float)$purchaseValue < 0)) {
        $formError = 'Purchase value must be a valid positive number.';
        $openModal = $action . 'Modal';
    } else {
        try {
            $pdo->beginTransaction();
            
            if ($action === 'add') {
                // 1. Generate unique asset tag (AST-YYYY-XXXX)
                $tagStmt = $pdo->query("SELECT CONCAT('AST-', DATE_FORMAT(NOW(), '%Y'), '-', LPAD(COALESCE(MAX(id), 0) + 1, 4, '0')) FROM assets");
                $assetTag = $tagStmt->fetchColumn() ?: ('AST-' . date('Y') . '-' . str_pad((string)rand(1, 9999), 4, '0', STR_PAD_LEFT));
                
                // 2. Insert into assets table
                $stmt = $pdo->prepare('
                    INSERT INTO assets (
                        asset_tag, name, category, purchase_date, purchase_value, 
                        condition_status, current_location, is_active, notes
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, 1, ?)
                ');
                $stmt->execute([
                    $assetTag,
                    $name,
                    $category,
                    $purchaseDate ?: null,
                    $purchaseValue !== '' ? (float)$purchaseValue : null,
                    $condition,
                    $location,
                    $notes ?: null
                ]);
                $newAssetId = $pdo->lastInsertId();
                
                // 3. Find a user ID for assigned_by constraint
                $assignedBy = null;
                $chkHead = $pdo->prepare('SELECT head_user_id FROM departments WHERE id = ?');
                $chkHead->execute([$departmentId]);
                $assignedBy = $chkHead->fetchColumn();
                
                if (!$assignedBy) {
                    $fallbackUser = $pdo->query('SELECT id FROM users WHERE is_active = 1 ORDER BY role_id ASC LIMIT 1')->fetchColumn();
                    $assignedBy = $fallbackUser ?: 1;
                }
                
                // 4. Insert into asset_assignments
                $stmt2 = $pdo->prepare('
                    INSERT INTO asset_assignments (
                        asset_id, assigned_type, assigned_department_id, assigned_from, assigned_by, notes
                    ) VALUES (?, "department", ?, NOW(), ?, "Registered by department")
                ');
                $stmt2->execute([$newAssetId, $departmentId, $assignedBy]);
                
                $pdo->commit();
                
                logDepartmentAction(
                    $pdo, 'asset_created', 'asset', $newAssetId,
                    "Registered and assigned asset: $name ($assetTag)"
                );
                
                header('Location: view.php?success=add');
                exit;
                
            } elseif ($action === 'edit' && $assetId) {
                // Verify the asset is assigned to this department
                $chk = $pdo->prepare('
                    SELECT a.id 
                    FROM assets a
                    INNER JOIN asset_assignments aa ON a.id = aa.asset_id
                    WHERE aa.assigned_type = "department" 
                      AND aa.assigned_department_id = ? 
                      AND aa.assigned_to IS NULL 
                      AND a.id = ?
                ');
                $chk->execute([$departmentId, $assetId]);
                if ($chk->fetch()) {
                    // Update asset fields
                    $stmt = $pdo->prepare('
                        UPDATE assets 
                        SET name = ?, category = ?, current_location = ?, 
                            purchase_date = ?, purchase_value = ?, condition_status = ?, notes = ?
                        WHERE id = ?
                    ');
                    $stmt->execute([
                        $name,
                        $category,
                        $location,
                        $purchaseDate ?: null,
                        $purchaseValue !== '' ? (float)$purchaseValue : null,
                        $condition,
                        $notes ?: null,
                        $assetId
                    ]);
                    
                    $pdo->commit();
                    
                    logDepartmentAction(
                        $pdo, 'asset_updated', 'asset', $assetId,
                        "Updated asset details: $name"
                    );
                    
                    header('Location: view.php?success=edit');
                    exit;
                } else {
                    $pdo->rollBack();
                    $formError = 'Permission denied.';
                }
            }
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log('Asset save error: ' . $e->getMessage());
            $formError = 'Failed to save asset: ' . $e->getMessage();
            $openModal = $action . 'Modal';
        }
    }
}

// Filters & Search
$search = trim($_GET['search'] ?? '');
$conditionFilter = trim($_GET['condition'] ?? '');

$records = [];
$totalCount = 0;
$goodCount = 0;
$totalValue = 0;

try {
    $query = '
        SELECT 
            a.id,
            a.asset_tag,
            a.name,
            a.category,
            a.purchase_date,
            a.purchase_value,
            a.condition_status,
            a.current_location,
            a.notes,
            aa.assigned_from
        FROM assets a
        INNER JOIN asset_assignments aa ON a.id = aa.asset_id
        WHERE aa.assigned_type = "department" 
          AND aa.assigned_department_id = ?
          AND aa.assigned_to IS NULL
          AND a.is_active = 1
    ';
    
    $params = [$departmentId];
    
    if (!empty($search)) {
        $query .= ' AND (a.name LIKE ? OR a.asset_tag LIKE ? OR a.current_location LIKE ?)';
        $searchVal = "%$search%";
        $params[] = $searchVal;
        $params[] = $searchVal;
        $params[] = $searchVal;
    }
    
    if (!empty($conditionFilter)) {
        $query .= ' AND a.condition_status = ?';
        $params[] = $conditionFilter;
    }
    
    $query .= ' ORDER BY aa.assigned_from DESC, a.id DESC';
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    
    // Calculate statistics
    $totalCount = count($records);
    foreach ($records as $r) {
        $totalValue += (float)$r['purchase_value'];
        if (in_array($r['condition_status'], ['excellent', 'good'])) {
            $goodCount++;
        }
    }
} catch (Exception $e) {
    error_log('Fetch assets error: ' . $e->getMessage());
}

require_once __DIR__ . '/../includes/header.php';
?>

<!-- Header -->
<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
    <div>
        <h2 class="text-2xl font-heading font-bold text-royal-800">Department Assets</h2>
        <p class="text-sm text-mist-500 mt-0.5">Manage assets registered and assigned to this department</p>
    </div>
    <div>
        <button onclick="showAddModal()" class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-royal-600 hover:bg-royal-700 text-white rounded-xl text-sm font-semibold shadow transition-all">+ Register Asset</button>
    </div>
</div>

<?php if (isset($_GET['success'])): ?>
<div class="mb-5 p-3 bg-emerald-50 border border-emerald-200 rounded-xl text-sm text-emerald-700 font-medium">
    ✓ <?php echo $_GET['success'] === 'add' ? 'Asset registered and assigned successfully.' : 'Asset details updated successfully.'; ?>
</div>
<?php endif; ?>

<?php if (isset($_GET['unassigned'])): ?>
<div class="mb-5 p-3 bg-amber-50 border border-amber-200 rounded-xl text-sm text-amber-700 font-medium">
    ✓ Asset unassigned from department successfully.
</div>
<?php endif; ?>

<?php if ($formError): ?>
<div class="mb-5 p-3 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700 font-medium">⚠️ <?php echo htmlspecialchars($formError); ?></div>
<?php endif; ?>

<!-- Stats KPI Cards -->
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    <div class="bg-white rounded-2xl border border-mist-200 shadow-sm p-5 hover:shadow-md transition-all">
        <p class="text-xs font-semibold text-mist-400 uppercase tracking-wide">Total Assets</p>
        <p class="text-2xl font-bold text-royal-700 mt-1"><?= $totalCount ?></p>
    </div>
    <div class="bg-white rounded-2xl border border-mist-200 shadow-sm p-5 hover:shadow-md transition-all">
        <p class="text-xs font-semibold text-mist-400 uppercase tracking-wide">Good / Excellent Condition</p>
        <p class="text-2xl font-bold text-emerald-600 mt-1"><?= $goodCount ?> <span class="text-xs text-mist-400 font-normal">/ <?= $totalCount ?></span></p>
    </div>
    <div class="bg-white rounded-2xl border border-mist-200 shadow-sm p-5 hover:shadow-md transition-all">
        <p class="text-xs font-semibold text-mist-400 uppercase tracking-wide">Total Purchase Value</p>
        <p class="text-2xl font-bold text-mist-800 mt-1"><?= formatCurrency($totalValue) ?></p>
    </div>
</div>

<!-- Search & Filters -->
<form method="GET" class="bg-white rounded-2xl border border-mist-200 shadow-sm p-4 mb-5 flex flex-wrap gap-3 items-end">
    <div class="flex-1 min-w-[200px]">
        <label class="block text-xs font-semibold text-mist-600 mb-1">Search</label>
        <input type="text" name="search" placeholder="Name, tag, or location…" value="<?php echo htmlspecialchars($search); ?>"
            class="w-full border border-mist-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-royal-400">
    </div>
    <div>
        <label class="block text-xs font-semibold text-mist-600 mb-1">Condition</label>
        <select name="condition" class="border border-mist-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-royal-400">
            <option value="">All conditions</option>
            <option value="excellent" <?php echo $conditionFilter === 'excellent' ? 'selected' : ''; ?>>Excellent</option>
            <option value="good" <?php echo $conditionFilter === 'good' ? 'selected' : ''; ?>>Good</option>
            <option value="fair" <?php echo $conditionFilter === 'fair' ? 'selected' : ''; ?>>Fair</option>
            <option value="poor" <?php echo $conditionFilter === 'poor' ? 'selected' : ''; ?>>Poor</option>
            <option value="retired" <?php echo $conditionFilter === 'retired' ? 'selected' : ''; ?>>Retired</option>
        </select>
    </div>
    <button type="submit" class="px-4 py-2 bg-royal-600 hover:bg-royal-700 text-white rounded-xl text-sm font-semibold shadow transition-all">Filter</button>
    <?php if ($search || $conditionFilter): ?>
    <a href="view.php" class="px-4 py-2 bg-mist-100 hover:bg-mist-200 text-mist-600 rounded-xl text-sm font-semibold transition-all">Clear</a>
    <?php endif; ?>
</form>

<!-- Assets Grid -->
<?php if (!empty($records)): ?>
<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
    <?php foreach ($records as $r): ?>
        <?php 
            $condColors = [
                'excellent' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                'good' => 'bg-green-50 text-green-700 border-green-100',
                'fair' => 'bg-amber-50 text-amber-700 border-amber-200',
                'poor' => 'bg-rose-50 text-rose-700 border-rose-200',
                'retired' => 'bg-mist-100 text-mist-600 border-mist-200',
            ];
            $condClass = $condColors[$r['condition_status']] ?? 'bg-mist-50 text-mist-600 border-mist-200';
        ?>
        <div class="bg-white rounded-2xl border border-mist-200 shadow-sm overflow-hidden flex flex-col hover:shadow-md transition-all">
            <!-- Card Header -->
            <div class="px-5 py-4 border-b border-mist-100 flex items-start justify-between gap-2">
                <div class="min-w-0">
                    <span class="text-[10px] bg-mist-100 text-mist-600 px-2 py-0.5 rounded-full font-bold uppercase tracking-wider"><?= htmlspecialchars($r['asset_tag']) ?></span>
                    <h3 class="font-bold text-mist-900 mt-1 truncate" title="<?= htmlspecialchars($r['name']) ?>"><?= htmlspecialchars($r['name']) ?></h3>
                </div>
                <span class="px-2 py-0.5 rounded-full text-xs font-semibold border capitalize <?= $condClass ?>"><?= htmlspecialchars($r['condition_status']) ?></span>
            </div>
            
            <!-- Card Body -->
            <div class="px-5 py-4 flex-1 space-y-2 text-sm text-mist-600">
                <div class="flex justify-between">
                    <span class="text-mist-400">Category:</span>
                    <span class="font-medium text-mist-800"><?= htmlspecialchars($r['category']) ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-mist-400">Location:</span>
                    <span class="font-medium text-mist-800"><?= htmlspecialchars($r['current_location']) ?></span>
                </div>
                <?php if ($r['purchase_date']): ?>
                <div class="flex justify-between">
                    <span class="text-mist-400">Purchase Date:</span>
                    <span class="font-medium text-mist-800"><?= date('M d, Y', strtotime($r['purchase_date'])) ?></span>
                </div>
                <?php endif; ?>
                <?php if ($r['purchase_value']): ?>
                <div class="flex justify-between">
                    <span class="text-mist-400">Value:</span>
                    <span class="font-bold text-royal-700"><?= formatCurrency($r['purchase_value']) ?></span>
                </div>
                <?php endif; ?>
                <?php if ($r['notes']): ?>
                <div class="pt-2 border-t border-mist-50">
                    <p class="text-xs text-mist-400">Notes:</p>
                    <p class="text-xs text-mist-500 italic mt-0.5 line-clamp-2"><?= htmlspecialchars($r['notes']) ?></p>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Card Actions -->
            <div class="px-5 py-3.5 bg-mist-50/50 border-t border-mist-100 flex items-center justify-between">
                <span class="text-[11px] text-mist-400">Assigned: <?= date('d M y', strtotime($r['assigned_from'])) ?></span>
                <div class="flex items-center gap-3">
                    <button onclick='showEditModal(<?php echo json_encode($r); ?>)' 
                        class="text-xs font-semibold text-royal-600 hover:text-royal-800 transition-colors">Edit Details</button>
                    <span class="text-mist-200">|</span>
                    <a href="delete.php?id=<?= $r['id'] ?>" onclick="return confirm('Remove this asset from department assignments?')"
                        class="text-xs font-semibold text-red-500 hover:text-red-700 transition-colors">Remove</a>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<?php else: ?>
<div class="bg-white rounded-2xl border border-mist-200 shadow-sm p-12 text-center">
    <svg class="w-16 h-16 text-mist-300 mx-auto mb-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
    </svg>
    <p class="text-mist-500 font-semibold mb-3">No assets registered to this department.</p>
    <button onclick="showAddModal()" class="px-4 py-2 bg-royal-600 hover:bg-royal-700 text-white rounded-xl text-sm font-semibold shadow transition-all">+ Register Asset</button>
</div>
<?php endif; ?>

<!-- ═════════════ MODALS ═════════════ -->

<!-- Register Asset Modal (Add) -->
<div id="addModal" class="fixed inset-0 z-50 hidden items-center justify-center">
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" onclick="hideModal('addModal')"></div>
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg mx-4 p-6 z-10">
        <div class="flex items-center justify-between mb-5">
            <h3 class="text-lg font-heading font-bold text-royal-800">Register Department Asset</h3>
            <button onclick="hideModal('addModal')" class="text-mist-400 hover:text-mist-600 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form method="POST" class="space-y-4">
            <input type="hidden" name="action" value="add">
            
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-semibold text-mist-700 mb-1.5">Asset Name *</label>
                    <input type="text" name="name" required placeholder="e.g. Yamaha Mixer, Chairs"
                        class="w-full border border-mist-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-royal-400 focus:ring-2 focus:ring-royal-100">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-mist-700 mb-1.5">Category *</label>
                    <input type="text" list="add-cat-list" name="category" required placeholder="Sound, Furniture, etc..."
                        class="w-full border border-mist-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-royal-400 focus:ring-2 focus:ring-royal-100">
                    <datalist id="add-cat-list">
                        <option value="Sound Equipment">
                        <option value="Furniture">
                        <option value="Musical Instrument">
                        <option value="Electrical">
                        <option value="IT Equipment">
                        <option value="Kitchen">
                    </datalist>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-semibold text-mist-700 mb-1.5">Current Location *</label>
                    <input type="text" name="current_location" required placeholder="e.g. Media Room, Store"
                        class="w-full border border-mist-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-royal-400 focus:ring-2 focus:ring-royal-100">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-mist-700 mb-1.5">Condition</label>
                    <select name="condition_status" class="w-full border border-mist-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-royal-400 focus:ring-2 focus:ring-royal-100">
                        <option value="excellent">Excellent</option>
                        <option value="good" selected>Good</option>
                        <option value="fair">Fair</option>
                        <option value="poor">Poor</option>
                        <option value="retired">Retired</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-semibold text-mist-700 mb-1.5">Purchase Date</label>
                    <input type="date" name="purchase_date"
                        class="w-full border border-mist-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-royal-400 focus:ring-2 focus:ring-royal-100">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-mist-700 mb-1.5">Purchase Value (Tsh)</label>
                    <input type="number" name="purchase_value" placeholder="0.00" min="0" step="0.01"
                        class="w-full border border-mist-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-royal-400 focus:ring-2 focus:ring-royal-100">
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-mist-700 mb-1.5">Notes</label>
                <textarea name="notes" rows="2" placeholder="Optional notes/details..."
                    class="w-full border border-mist-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-royal-400 focus:ring-2 focus:ring-royal-100 resize-none"></textarea>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit" class="flex-1 py-2.5 bg-royal-600 hover:bg-royal-700 text-white rounded-xl text-sm font-semibold shadow transition">Register Asset</button>
                <button type="button" onclick="hideModal('addModal')" class="flex-1 py-2.5 bg-mist-100 hover:bg-mist-200 text-mist-700 rounded-xl text-sm font-semibold transition">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Asset Modal -->
<div id="editModal" class="fixed inset-0 z-50 hidden items-center justify-center">
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" onclick="hideModal('editModal')"></div>
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg mx-4 p-6 z-10">
        <div class="flex items-center justify-between mb-5">
            <h3 class="text-lg font-heading font-bold text-royal-800">Edit Asset Details</h3>
            <button onclick="hideModal('editModal')" class="text-mist-400 hover:text-mist-600 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form method="POST" class="space-y-4">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="asset_id" id="edit-id">
            
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-semibold text-mist-700 mb-1.5">Asset Name *</label>
                    <input type="text" name="name" id="edit-name" required
                        class="w-full border border-mist-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-royal-400 focus:ring-2 focus:ring-royal-100">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-mist-700 mb-1.5">Category *</label>
                    <input type="text" list="edit-cat-list" name="category" id="edit-category" required
                        class="w-full border border-mist-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-royal-400 focus:ring-2 focus:ring-royal-100">
                    <datalist id="edit-cat-list">
                        <option value="Sound Equipment">
                        <option value="Furniture">
                        <option value="Musical Instrument">
                        <option value="Electrical">
                        <option value="IT Equipment">
                    </datalist>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-semibold text-mist-700 mb-1.5">Current Location *</label>
                    <input type="text" name="current_location" id="edit-location" required
                        class="w-full border border-mist-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-royal-400 focus:ring-2 focus:ring-royal-100">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-mist-700 mb-1.5">Condition</label>
                    <select name="condition_status" id="edit-condition" class="w-full border border-mist-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-royal-400 focus:ring-2 focus:ring-royal-100">
                        <option value="excellent">Excellent</option>
                        <option value="good">Good</option>
                        <option value="fair">Fair</option>
                        <option value="poor">Poor</option>
                        <option value="retired">Retired</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-semibold text-mist-700 mb-1.5">Purchase Date</label>
                    <input type="date" name="purchase_date" id="edit-purchase-date"
                        class="w-full border border-mist-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-royal-400 focus:ring-2 focus:ring-royal-100">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-mist-700 mb-1.5">Purchase Value (Tsh)</label>
                    <input type="number" name="purchase_value" id="edit-purchase-value" placeholder="0.00" min="0" step="0.01"
                        class="w-full border border-mist-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-royal-400 focus:ring-2 focus:ring-royal-100">
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-mist-700 mb-1.5">Notes</label>
                <textarea name="notes" id="edit-notes" rows="2"
                    class="w-full border border-mist-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-royal-400 focus:ring-2 focus:ring-royal-100 resize-none"></textarea>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit" class="flex-1 py-2.5 bg-royal-600 hover:bg-royal-700 text-white rounded-xl text-sm font-semibold shadow transition">Save Changes</button>
                <button type="button" onclick="hideModal('editModal')" class="flex-1 py-2.5 bg-mist-100 hover:bg-mist-200 text-mist-700 rounded-xl text-sm font-semibold transition">Cancel</button>
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

function showAddModal() {
    const form = document.querySelector('#addModal form');
    form.reset();
    showModal('addModal');
}

function showEditModal(r) {
    document.getElementById('edit-id').value = r.id;
    document.getElementById('edit-name').value = r.name;
    document.getElementById('edit-category').value = r.category;
    document.getElementById('edit-location').value = r.current_location;
    document.getElementById('edit-condition').value = r.condition_status;
    document.getElementById('edit-purchase-date').value = r.purchase_date || '';
    document.getElementById('edit-purchase-value').value = r.purchase_value || '';
    document.getElementById('edit-notes').value = r.notes || '';
    
    showModal('editModal');
}

<?php if ($openModal): ?>
document.addEventListener('DOMContentLoaded', () => {
    <?php if ($openModal === 'addModal'): ?>
        showAddModal();
    <?php elseif ($openModal === 'editModal'): ?>
        showEditModal(<?= json_encode($savedPost) ?>);
    <?php endif; ?>
});
<?php endif; ?>
</script>

<?php 
require_once __DIR__ . '/../includes/footer.php'; 

// logDepartmentAction is now centralized in includes/session.php
?>
