<?php
/**
 * Department Leaders List View
 * 
 * Display all leadership roles assigned in the department.
 */

require_once __DIR__ . '/../includes/auth_check.php';

$pdo = require __DIR__ . '/../includes/db.php';
$departmentId = getCurrentDepartmentId();

$leaders = [];
$formError = '';
$openModal = '';
$savedPost = [];

// Auto-open modal from dashboard Quick Action
if (isset($_GET['action']) && $_GET['action'] === 'add') {
    $openModal = 'addLeaderModal';
}

// Load department members for "select member as leader" dropdown
$deptMembers = [];
try {
    $mStmt = $pdo->prepare('
        SELECT m.id, m.first_name, m.last_name, m.phone, m.email
        FROM department_members dm
        JOIN members m ON dm.member_id = m.id
        WHERE dm.department_id = ?
        ORDER BY m.first_name, m.last_name
    ');
    $mStmt->execute([$departmentId]);
    $deptMembers = $mStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log('Load dept members for leaders: ' . $e->getMessage());
}

// Handle Add Leader modal POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['_modal_add_leader'])) {
    $savedPost  = $_POST;
    $leaderType = trim($_POST['leader_type'] ?? '');
    $memberId   = (int)($_POST['member_id'] ?? 0);
    $email      = trim($_POST['email'] ?? '');
    $phone      = trim($_POST['phone'] ?? '');
    $bio        = trim($_POST['bio'] ?? '');

    // Resolve name from selected member
    $selectedMember = null;
    foreach ($deptMembers as $m) {
        if ((int)$m['id'] === $memberId) { $selectedMember = $m; break; }
    }

    if (empty($leaderType)) {
        $formError = 'Please select a position.';
    } elseif (!$selectedMember) {
        $formError = 'Please select a department member.';
    } elseif (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $formError = 'Invalid email format.';
    } else {
        $leaderName = trim($selectedMember['first_name'] . ' ' . $selectedMember['last_name']);
        $email = $email ?: $selectedMember['email'];
        $phone = $phone ?: $selectedMember['phone'];
        // Check for duplicate before inserting
        $chk = $pdo->prepare('SELECT 1 FROM department_leaders WHERE department_id = ? AND member_id = ? AND leader_type = ?');
        $chk->execute([$departmentId, $memberId, $leaderType]);
        if ($chk->fetch()) {
            $formError = $leaderName . ' is already assigned as ' . $leaderType . ' in this department.';
        } else {
            try {
                $stmt = $pdo->prepare('INSERT INTO department_leaders (department_id,member_id,leader_type,leader_name,email,phone,bio,is_active) VALUES (?,?,?,?,?,?,?,1)');
                $stmt->execute([$departmentId, $memberId, $leaderType, $leaderName, $email?:null, $phone?:null, $bio?:null]);
                $leaderId = $pdo->lastInsertId();

                logDepartmentAction($pdo, 'leader_added', 'leader', $leaderId, "Added leader: $leaderName ($leaderType)");

                header('Location: view.php?success=1');
                exit;
            } catch (Exception $e) {
                error_log('Add leader modal error: ' . $e->getMessage());
                $formError = $e->getCode() == 23000
                    ? $leaderName . ' is already assigned as ' . $leaderType . ' in this department.'
                    : 'Failed to add leader. Please try again.';
            }
        }
    }
    $openModal = 'addLeaderModal';
}

try {
    // Fetch leaders for this department
    $stmt = $pdo->prepare('
        SELECT 
            id,
            leader_type,
            leader_name,
            email,
            phone,
            bio,
            is_active
        FROM department_leaders
        WHERE department_id = ?
        ORDER BY leader_type ASC
    ');
    $stmt->execute([$departmentId]);
    $leaders = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    error_log('Leaders view error: ' . $e->getMessage());
}

$pageTitle = 'Department Leaders';
?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
    <div>
        <h2 class="text-2xl font-heading font-bold text-royal-800">Leaders</h2>
        <p class="text-sm text-mist-500 mt-0.5">Department leadership team</p>
    </div>
    <button onclick="showModal('addLeaderModal')" class="inline-flex items-center gap-2 px-4 py-2.5 bg-royal-600 hover:bg-royal-700 text-white rounded-xl text-sm font-semibold shadow transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
        Add Leader
    </button>
</div>

<?php if (isset($_GET['success'])): ?>
<div class="mb-4 p-3 bg-emerald-50 border border-emerald-200 rounded-xl text-sm text-emerald-700 font-medium">✓ Leader added successfully.</div>
<?php endif; ?>

<?php if (empty($leaders)): ?>
<div class="bg-white rounded-2xl border border-mist-200 shadow-sm p-12 text-center">
    <div class="w-16 h-16 bg-mist-100 rounded-full flex items-center justify-center mx-auto mb-4">
        <svg class="w-8 h-8 text-mist-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.5a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z"/></svg>
    </div>
    <p class="text-mist-500 font-semibold">No leaders assigned yet.</p>
    <button onclick="showModal('addLeaderModal')" class="mt-3 inline-block text-sm text-royal-600 hover:underline font-semibold">Add the first leader →</button>
</div>
<?php else: ?>
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
<?php foreach ($leaders as $leader): ?>
    <div class="bg-white rounded-2xl border border-mist-200 shadow-sm p-5 flex flex-col gap-3">
        <div class="flex items-start justify-between">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-full bg-glory-100 text-glory-700 flex items-center justify-center font-bold text-base shrink-0">
                    <?php echo strtoupper(substr($leader['leader_name'], 0, 1)); ?>
                </div>
                <div>
                    <p class="font-bold text-royal-800 text-sm"><?php echo htmlspecialchars($leader['leader_name']); ?></p>
                    <p class="text-xs text-mist-500 font-medium"><?php echo htmlspecialchars($leader['leader_type']); ?></p>
                </div>
            </div>
            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase <?php echo $leader['is_active'] ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-500'; ?>">
                <?php echo $leader['is_active'] ? 'Active' : 'Inactive'; ?>
            </span>
        </div>
        <?php if ($leader['email'] || $leader['phone']): ?>
        <div class="space-y-1">
            <?php if ($leader['email']): ?>
            <p class="text-xs text-mist-500">📧 <?php echo htmlspecialchars($leader['email']); ?></p>
            <?php endif; ?>
            <?php if ($leader['phone']): ?>
            <p class="text-xs text-mist-500">📞 <?php echo htmlspecialchars($leader['phone']); ?></p>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        <?php if ($leader['bio']): ?>
        <p class="text-xs text-mist-400 line-clamp-2"><?php echo htmlspecialchars($leader['bio']); ?></p>
        <?php endif; ?>
        <div class="pt-2 border-t border-mist-100">
            <a href="delete.php?id=<?php echo $leader['id']; ?>" onclick="return confirm('Remove this leader?')"
               class="text-xs font-semibold text-red-400 hover:text-red-600 transition">Remove</a>
        </div>
    </div>
<?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Add Leader Modal -->
<div id="addLeaderModal" class="fixed inset-0 z-50 hidden items-center justify-center">
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" onclick="hideModal('addLeaderModal')"></div>
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 p-6 z-10 overflow-y-auto max-h-[90vh]">
        <div class="flex items-center justify-between mb-5">
            <h3 class="text-lg font-heading font-bold text-royal-800">Assign Leader</h3>
            <button onclick="hideModal('addLeaderModal')" class="text-mist-400 hover:text-mist-600 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <?php if ($formError): ?>
        <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700"><?php echo htmlspecialchars($formError); ?></div>
        <?php endif; ?>
        <?php if (empty($deptMembers)): ?>
        <div class="p-4 bg-dawn-50 border border-dawn-200 rounded-xl text-sm text-dawn-700">
            No members registered in this department yet. <a href="../members/view.php" class="font-semibold underline">Add members first →</a>
        </div>
        <?php else: ?>
        <!-- Member data for JS autofill -->
        <script>
        const memberData = {
            <?php foreach ($deptMembers as $m): ?>
            <?php echo (int)$m['id']; ?>: {
                email: "<?php echo addslashes($m['email'] ?? ''); ?>",
                phone: "<?php echo addslashes($m['phone'] ?? ''); ?>"
            },
            <?php endforeach; ?>
        };
        function onMemberChange(sel) {
            const d = memberData[sel.value] || {};
            document.getElementById('ldr_email').value = d.email || '';
            document.getElementById('ldr_phone').value = d.phone || '';
        }
        </script>
        <form method="POST" class="space-y-4">
            <input type="hidden" name="_modal_add_leader" value="1">
            <div>
                <label class="block text-sm font-semibold text-mist-700 mb-1.5">Select Member *</label>
                <select name="member_id" required onchange="onMemberChange(this)"
                    class="w-full border border-mist-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-royal-400 focus:ring-2 focus:ring-royal-100">
                    <option value="">Choose member…</option>
                    <?php foreach ($deptMembers as $m): ?>
                    <option value="<?php echo $m['id']; ?>" <?php echo ((int)($savedPost['member_id'] ?? 0)) === (int)$m['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($m['first_name'] . ' ' . $m['last_name']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-mist-700 mb-1.5">Position *</label>
                <select name="leader_type" required class="w-full border border-mist-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-royal-400 focus:ring-2 focus:ring-royal-100">
                    <option value="">Select position…</option>
                    <?php foreach (['Chairperson','Vice Chairperson','Secretary','Treasurer','Coordinator','Deputy Coordinator','Member'] as $pos): ?>
                    <option value="<?php echo $pos; ?>" <?php echo ($savedPost['leader_type'] ?? '') === $pos ? 'selected' : ''; ?>><?php echo $pos; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-semibold text-mist-700 mb-1.5">Email</label>
                    <input type="email" id="ldr_email" name="email" placeholder="auto-filled"
                        value="<?php echo htmlspecialchars($savedPost['email'] ?? ''); ?>"
                        class="w-full border border-mist-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-royal-400 focus:ring-2 focus:ring-royal-100">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-mist-700 mb-1.5">Phone</label>
                    <input type="tel" id="ldr_phone" name="phone" placeholder="auto-filled"
                        value="<?php echo htmlspecialchars($savedPost['phone'] ?? ''); ?>"
                        class="w-full border border-mist-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-royal-400 focus:ring-2 focus:ring-royal-100">
                </div>
            </div>
            <div>
                <label class="block text-sm font-semibold text-mist-700 mb-1.5">Bio / Notes</label>
                <textarea name="bio" rows="2" placeholder="Optional notes…"
                    class="w-full border border-mist-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-royal-400 focus:ring-2 focus:ring-royal-100 resize-none"><?php echo htmlspecialchars($savedPost['bio'] ?? ''); ?></textarea>
            </div>
            <div class="flex gap-3 pt-1">
                <button type="submit" class="flex-1 py-2.5 bg-royal-600 hover:bg-royal-700 text-white rounded-xl text-sm font-semibold shadow transition">Assign Leader</button>
                <button type="button" onclick="hideModal('addLeaderModal')" class="flex-1 py-2.5 bg-mist-100 hover:bg-mist-200 text-mist-700 rounded-xl text-sm font-semibold transition">Cancel</button>
            </div>
        </form>
        <?php endif; ?>
    </div>
</div>

<script>
function showModal(id){const el=document.getElementById(id);el.classList.remove('hidden');el.classList.add('flex');document.body.style.overflow='hidden';}
function hideModal(id){const el=document.getElementById(id);el.classList.add('hidden');el.classList.remove('flex');document.body.style.overflow='';}
<?php if ($openModal): ?>document.addEventListener('DOMContentLoaded',()=>showModal('<?php echo $openModal; ?>'));<?php endif; ?>
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>

<?php
// logDepartmentAction is now centralized in includes/session.php
