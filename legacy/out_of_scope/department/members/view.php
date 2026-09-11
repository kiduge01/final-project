<?php
/**
 * Department Members List View
 * 
 * Display all members assigned to the department with options to edit/delete.
 */

require_once __DIR__ . '/../includes/auth_check.php';

$pdo = require __DIR__ . '/../includes/db.php';
$departmentId = getCurrentDepartmentId();

$formError = '';
$openModal = '';
$savedPost = [];

// Auto-open modal from dashboard Quick Action
if (isset($_GET['action']) && $_GET['action'] === 'add') {
    $openModal = 'addMemberModal';
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
                if (count($row) < 4) { $csvImportResult['errors'][] = "Row $rowNum: too few columns."; continue; }
                $fn    = trim($row[0] ?? '');
                $ln    = trim($row[1] ?? '');
                $phone = trim($row[2] ?? '');
                $gend  = strtolower(trim($row[3] ?? ''));
                $email = trim($row[4] ?? '') ?: null;
                $dob   = trim($row[5] ?? '') ?: null;
                $addr  = trim($row[6] ?? '') ?: null;
                if (empty($fn) || empty($ln)) { $csvImportResult['errors'][] = "Row $rowNum: first/last name required."; continue; }
                if (empty($phone)) { $csvImportResult['errors'][] = "Row $rowNum: phone required."; continue; }
                if (!in_array($gend, ['male','female','m','f'], true)) { $csvImportResult['errors'][] = "Row $rowNum: gender must be male/female."; continue; }
                $gend = in_array($gend, ['m','male'], true) ? 'male' : 'female';
                if ($dob && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dob)) { $dob = null; }
                try {
                    $pdo->beginTransaction();
                    $code = generateMemberCode($pdo);
                    $ins = $pdo->prepare('INSERT INTO members (member_code,first_name,last_name,phone,email,gender,date_of_birth,physical_address,member_status,join_date) VALUES (?,?,?,?,?,?,?,?,"active",NOW())');
                    $ins->execute([$code,$fn,$ln,$phone,$email,$gend,$dob,$addr]);
                    $mid = $pdo->lastInsertId();
                    $pdo->prepare('INSERT IGNORE INTO department_members (department_id,member_id,assigned_date) VALUES (?,?,NOW())')->execute([$departmentId,$mid]);
                    
                    logDepartmentAction($pdo, 'member_imported', 'member', $mid, "Imported member via CSV: $fn $ln");

                    $pdo->commit();
                    $csvImportResult['success']++;
                } catch (Exception $e) {
                    $pdo->rollBack();
                    $csvImportResult['errors'][] = "Row $rowNum: " . ($e->getCode() == 23000 ? "duplicate phone/email, skipped." : $e->getMessage());
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
        $csvImportResult['errors'][] = 'Please select a CSV file to upload.';
        $openModal = 'csvImportModal';
    }
}

// Handle CSV template download
if (isset($_GET['dl_template'])) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="members_import_template.csv"');
    echo "first_name,last_name,phone,gender,email,date_of_birth,physical_address\n";
    echo "John,Doe,+255712345678,male,john@example.com,1990-01-15,Dar es Salaam\n";
    echo "Jane,Smith,+255787654321,female,,,\n";
    exit;
}

// Handle Add Member modal POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['_modal_add_member'])) {
    $savedPost = $_POST;
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName  = trim($_POST['last_name']  ?? '');
    $phone     = trim($_POST['phone']      ?? '');
    $email     = trim($_POST['email']      ?? '');
    $gender    = trim($_POST['gender']     ?? '');
    $dateOfBirth = trim($_POST['date_of_birth'] ?? '');
    $address   = trim($_POST['address']    ?? '');

    if (empty($firstName) || empty($lastName)) {
        $formError = 'First name and last name are required.';
    } elseif (empty($phone)) {
        $formError = 'Phone number is required.';
    } elseif (empty($gender)) {
        $formError = 'Gender is required.';
    } elseif (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $formError = 'Invalid email format.';
    } else {
        // Check for duplicate phone before inserting
        $chkPhone = $pdo->prepare('SELECT id FROM members WHERE phone = ?');
        $chkPhone->execute([$phone]);
        if ($chkPhone->fetch()) {
            $formError = 'A member with phone number ' . htmlspecialchars($phone) . ' already exists.';
        } else {
            try {
                $pdo->beginTransaction();
                $memberCode = generateMemberCode($pdo);
                $stmt = $pdo->prepare('INSERT INTO members (member_code,first_name,last_name,phone,email,gender,date_of_birth,physical_address,member_status,join_date) VALUES (?,?,?,?,?,?,?,?,"active",NOW())');
                $stmt->execute([$memberCode,$firstName,$lastName,$phone,$email?:null,$gender,$dateOfBirth?:null,$address?:null]);
                $memberId = $pdo->lastInsertId();
                $stmt2 = $pdo->prepare('INSERT INTO department_members (department_id,member_id,assigned_date) VALUES (?,?,NOW())');
                $stmt2->execute([$departmentId, $memberId]);

                logDepartmentAction($pdo, 'member_added', 'member', $memberId, "Added member: $firstName $lastName");

                $pdo->commit();
                header('Location: view.php?success=1');
                exit;
            } catch (Exception $e) {
                $pdo->rollBack();
                error_log('Add member modal error: ' . $e->getMessage());
                $formError = $e->getCode() == 23000
                    ? 'A member with that phone or email already exists.'
                    : 'Failed to add member. Please try again.';
            }
        }
    }
    $openModal = 'addMemberModal';
}

$members = [];
$search = trim($_GET['search'] ?? '');
$searchParam = "%$search%";

try {
    // Fetch members for this department
    $query = '
        SELECT 
            m.id,
            m.member_code,
            m.first_name,
            m.last_name,
            m.phone,
            m.email,
            m.gender,
            dm.assigned_date,
            dm.notes
        FROM department_members dm
        JOIN members m ON dm.member_id = m.id
        WHERE dm.department_id = ?
    ';
    
    $params = [$departmentId];
    
    if (!empty($search)) {
        $query .= ' AND (m.first_name LIKE ? OR m.last_name LIKE ? OR m.phone LIKE ?)';
        $params = array_merge([$departmentId], [$searchParam, $searchParam, $searchParam]);
    }
    
    $query .= ' ORDER BY m.first_name ASC';
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $members = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    error_log('Members view error: ' . $e->getMessage());
}

$pageTitle = 'Department Members';
?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<!-- Header -->
<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
    <div>
        <h2 class="text-2xl font-heading font-bold text-royal-800">Members</h2>
        <p class="text-sm text-mist-500 mt-0.5">All members assigned to this department</p>
    </div>
    <div class="flex gap-2">
        <button onclick="showModal('csvImportModal')" class="inline-flex items-center gap-2 px-4 py-2.5 bg-mist-100 hover:bg-mist-200 text-mist-700 rounded-xl text-sm font-semibold transition border border-mist-200">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/></svg>
            Import CSV
        </button>
        <button onclick="showModal('addMemberModal')" class="inline-flex items-center gap-2 px-4 py-2.5 bg-royal-600 hover:bg-royal-700 text-white rounded-xl text-sm font-semibold shadow transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            Add Member
        </button>
    </div>
</div>

<?php if (isset($_GET['success'])): ?>
<div class="mb-4 p-3 bg-emerald-50 border border-emerald-200 rounded-xl text-sm text-emerald-700 font-medium">✓ Member added successfully.</div>
<?php endif; ?>
<?php if (isset($_GET['csv_imported'])): ?>
<div class="mb-4 p-3 bg-emerald-50 border border-emerald-200 rounded-xl text-sm text-emerald-700 font-medium">✓ CSV imported: <?php echo (int)$_GET['csv_imported']; ?> member(s) added successfully.</div>
<?php endif; ?>
<?php if ($formError): ?>
<div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700 font-medium">⚠️ <?php echo htmlspecialchars($formError); ?></div>
<?php endif; ?>

<!-- Search -->
<form method="GET" class="bg-white rounded-2xl border border-mist-200 shadow-sm p-4 mb-5 flex flex-col sm:flex-row gap-3">
    <input type="text" name="search" placeholder="Search by name or phone…" value="<?php echo htmlspecialchars($search); ?>"
        class="flex-1 border border-mist-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-royal-400 focus:ring-2 focus:ring-royal-100">
    <button type="submit" class="px-5 py-2.5 bg-royal-600 hover:bg-royal-700 text-white rounded-xl text-sm font-semibold transition">Search</button>
    <?php if ($search): ?><a href="view.php" class="px-5 py-2.5 bg-mist-100 hover:bg-mist-200 text-mist-700 rounded-xl text-sm font-semibold transition">Clear</a><?php endif; ?>
</form>

<?php if (empty($members)): ?>
<div class="bg-white rounded-2xl border border-mist-200 shadow-sm p-12 text-center">
    <div class="w-16 h-16 bg-mist-100 rounded-full flex items-center justify-center mx-auto mb-4">
        <svg class="w-8 h-8 text-mist-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
    </div>
    <p class="text-mist-500 font-semibold"><?php echo $search ? 'No members found matching your search.' : 'No members assigned to this department yet.'; ?></p>
    <button onclick="showModal('addMemberModal')" class="mt-3 inline-block text-sm text-royal-600 hover:underline font-semibold">Add the first member →</button>
</div>
<?php else: ?>
<div class="bg-white rounded-2xl border border-mist-200 shadow-sm overflow-x-auto">
    <div class="px-5 py-3 border-b border-mist-100 text-sm font-semibold text-mist-600"><?php echo count($members); ?> member(s)</div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">#</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Name</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Phone</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Gender</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Role/Notes</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Joined</th>
                    <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php foreach ($members as $i => $member): ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3 text-mist-400 text-xs"><?php echo $i + 1; ?></td>
                    <td class="px-5 py-3">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-royal-100 text-royal-700 flex items-center justify-center text-xs font-bold shrink-0">
                                <?php echo strtoupper(substr($member['first_name'], 0, 1)); ?>
                            </div>
                            <div>
                                <p class="font-semibold text-royal-800"><?php echo htmlspecialchars($member['first_name'] . ' ' . $member['last_name']); ?></p>
                                <p class="text-xs text-mist-400"><?php echo htmlspecialchars($member['member_code']); ?></p>
                            </div>
                        </div>
                    </td>
                    <td class="px-5 py-3 text-mist-600"><?php echo htmlspecialchars($member['phone'] ?? '—'); ?></td>
                    <td class="px-5 py-3 text-mist-500 capitalize"><?php echo htmlspecialchars($member['gender'] ?? '—'); ?></td>
                    <td class="px-5 py-3 text-mist-400 text-xs max-w-[160px] truncate"><?php echo htmlspecialchars($member['notes'] ?? '—'); ?></td>
                    <td class="px-5 py-3 text-mist-400 text-xs"><?php echo $member['assigned_date'] ? date('d M Y', strtotime($member['assigned_date'])) : '—'; ?></td>
                    <td class="px-5 py-3 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <a href="edit.php?id=<?php echo $member['id']; ?>" class="px-3 py-1 text-xs font-semibold bg-dawn-50 text-dawn-700 hover:bg-dawn-100 rounded-lg transition">Edit</a>
                            <a href="delete.php?id=<?php echo $member['id']; ?>" onclick="return confirm('Remove <?php echo htmlspecialchars($member['first_name']); ?> from department?')"
                               class="px-3 py-1 text-xs font-semibold bg-red-50 text-red-600 hover:bg-red-100 rounded-lg transition">Remove</a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- Add Member Modal -->
<div id="addMemberModal" class="fixed inset-0 z-50 hidden items-center justify-center">
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" onclick="hideModal('addMemberModal')"></div>
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg mx-4 p-6 z-10 overflow-y-auto max-h-[90vh]">
        <div class="flex items-center justify-between mb-5">
            <h3 class="text-lg font-heading font-bold text-royal-800">Add New Member</h3>
            <button onclick="hideModal('addMemberModal')" class="text-mist-400 hover:text-mist-600 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form method="POST" class="space-y-4">
            <input type="hidden" name="_modal_add_member" value="1">
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-semibold text-mist-700 mb-1.5">First Name *</label>
                    <input type="text" name="first_name" required placeholder="First name"
                        value="<?php echo htmlspecialchars($savedPost['first_name'] ?? ''); ?>"
                        class="w-full border border-mist-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-royal-400 focus:ring-2 focus:ring-royal-100">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-mist-700 mb-1.5">Last Name *</label>
                    <input type="text" name="last_name" required placeholder="Last name"
                        value="<?php echo htmlspecialchars($savedPost['last_name'] ?? ''); ?>"
                        class="w-full border border-mist-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-royal-400 focus:ring-2 focus:ring-royal-100">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-semibold text-mist-700 mb-1.5">Phone *</label>
                    <input type="tel" name="phone" required placeholder="+255 700 000000"
                        value="<?php echo htmlspecialchars($savedPost['phone'] ?? ''); ?>"
                        class="w-full border border-mist-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-royal-400 focus:ring-2 focus:ring-royal-100">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-mist-700 mb-1.5">Gender *</label>
                    <select name="gender" required class="w-full border border-mist-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-royal-400 focus:ring-2 focus:ring-royal-100">
                        <option value="">Select…</option>
                        <option value="male" <?php echo ($savedPost['gender'] ?? '') === 'male' ? 'selected' : ''; ?>>Male</option>
                        <option value="female" <?php echo ($savedPost['gender'] ?? '') === 'female' ? 'selected' : ''; ?>>Female</option>
                    </select>
                </div>
            </div>
            <div>
                <label class="block text-sm font-semibold text-mist-700 mb-1.5">Email</label>
                <input type="email" name="email" placeholder="email@example.com"
                    value="<?php echo htmlspecialchars($savedPost['email'] ?? ''); ?>"
                    class="w-full border border-mist-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-royal-400 focus:ring-2 focus:ring-royal-100">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-semibold text-mist-700 mb-1.5">Date of Birth</label>
                    <input type="date" name="date_of_birth"
                        value="<?php echo htmlspecialchars($savedPost['date_of_birth'] ?? ''); ?>"
                        class="w-full border border-mist-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-royal-400 focus:ring-2 focus:ring-royal-100">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-mist-700 mb-1.5">Address</label>
                    <input type="text" name="address" placeholder="Physical address"
                        value="<?php echo htmlspecialchars($savedPost['address'] ?? ''); ?>"
                        class="w-full border border-mist-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-royal-400 focus:ring-2 focus:ring-royal-100">
                </div>
            </div>
            <div class="flex gap-3 pt-1">
                <button type="submit" class="flex-1 py-2.5 bg-royal-600 hover:bg-royal-700 text-white rounded-xl text-sm font-semibold shadow transition">Add Member</button>
                <button type="button" onclick="hideModal('addMemberModal')" class="flex-1 py-2.5 bg-mist-100 hover:bg-mist-200 text-mist-700 rounded-xl text-sm font-semibold transition">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- CSV Import Modal -->
<div id="csvImportModal" class="fixed inset-0 z-50 hidden items-center justify-center">
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" onclick="hideModal('csvImportModal')"></div>
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg mx-4 p-6 z-10 overflow-y-auto max-h-[90vh]">
        <div class="flex items-center justify-between mb-5">
            <h3 class="text-lg font-heading font-bold text-royal-800">Import Members from CSV</h3>
            <button onclick="hideModal('csvImportModal')" class="text-mist-400 hover:text-mist-600 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <!-- Error/partial result display -->
        <?php if (!empty($csvImportResult['errors'])): ?>
        <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700 space-y-1">
            <?php if ($csvImportResult['success'] > 0): ?>
            <p class="font-semibold text-emerald-700">✓ <?php echo $csvImportResult['success']; ?> member(s) imported.</p>
            <?php endif; ?>
            <p class="font-semibold">Errors:</p>
            <?php foreach (array_slice($csvImportResult['errors'], 0, 10) as $err): ?>
            <p>• <?php echo htmlspecialchars($err); ?></p>
            <?php endforeach; ?>
            <?php if (count($csvImportResult['errors']) > 10): ?>
            <p class="text-mist-500">… and <?php echo count($csvImportResult['errors']) - 10; ?> more.</p>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        <!-- Instructions -->
        <div class="mb-4 p-3 bg-mist-50 border border-mist-200 rounded-xl text-sm text-mist-600">
            <p class="font-semibold text-mist-700 mb-1">CSV Format (columns in order):</p>
            <code class="text-xs bg-white border border-mist-200 rounded px-2 py-1 block font-mono">
                first_name, last_name, phone, gender, email, date_of_birth, address
            </code>
            <p class="mt-2 text-xs text-mist-500">First 4 columns are required. Gender: <strong>male</strong> or <strong>female</strong>. Date format: <strong>YYYY-MM-DD</strong>.</p>
            <a href="view.php?dl_template=1" class="mt-2 inline-flex items-center gap-1 text-xs text-royal-600 hover:underline font-semibold">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                Download template CSV
            </a>
        </div>
        <form method="POST" enctype="multipart/form-data" class="space-y-4">
            <input type="hidden" name="_modal_csv_import" value="1">
            <div>
                <label class="block text-sm font-semibold text-mist-700 mb-1.5">Select CSV File *</label>
                <input type="file" name="csv_file" accept=".csv,.txt" required
                    class="w-full border border-mist-200 rounded-xl px-3 py-2.5 text-sm text-mist-600 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:bg-royal-50 file:text-royal-700 file:font-semibold file:text-xs hover:file:bg-royal-100 focus:outline-none focus:border-royal-400">
            </div>
            <div class="flex gap-3 pt-1">
                <button type="submit" class="flex-1 py-2.5 bg-royal-600 hover:bg-royal-700 text-white rounded-xl text-sm font-semibold shadow transition">Import</button>
                <button type="button" onclick="hideModal('csvImportModal')" class="flex-1 py-2.5 bg-mist-100 hover:bg-mist-200 text-mist-700 rounded-xl text-sm font-semibold transition">Cancel</button>
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

<?php
function generateMemberCode($pdo) {
    $year = date('Y');
    $stmt = $pdo->prepare('SELECT MAX(CAST(SUBSTRING(member_code, -4) AS UNSIGNED)) as max_code FROM members WHERE member_code LIKE ?');
    $stmt->execute(["MBR-$year-%"]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return 'MBR-' . $year . '-' . str_pad((string) (($result['max_code'] ?? 0) + 1), 4, '0', STR_PAD_LEFT);
}
