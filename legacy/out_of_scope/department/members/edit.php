<?php
/**
 * Edit Department Member
 * 
 * Edit member details within a department.
 */

require_once __DIR__ . '/../includes/auth_check.php';

$pdo = require __DIR__ . '/../includes/db.php';
$departmentId = getCurrentDepartmentId();
$memberId = $_GET['id'] ?? null;

if (!$memberId || !is_numeric($memberId)) {
    header('Location: view.php');
    exit;
}

$error = '';
$member = null;

try {
    // Verify member belongs to this department
    $stmt = $pdo->prepare('
        SELECT m.* 
        FROM department_members dm
        JOIN members m ON dm.member_id = m.id
        WHERE dm.department_id = ? AND m.id = ?
    ');
    $stmt->execute([$departmentId, $memberId]);
    $member = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$member) {
        header('Location: view.php');
        exit;
    }

} catch (Exception $e) {
    error_log('Edit member fetch error: ' . $e->getMessage());
    header('Location: view.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $gender = trim($_POST['gender'] ?? '');
    $dateOfBirth = trim($_POST['date_of_birth'] ?? '');
    $address = trim($_POST['address'] ?? '');

    // Validation
    if (empty($firstName) || empty($lastName)) {
        $error = 'First name and last name are required.';
    } elseif (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format.';
    } else {
        try {
            // Store old values for audit
            $oldValues = [
                'first_name' => $member['first_name'],
                'last_name' => $member['last_name'],
                'phone' => $member['phone'],
                'email' => $member['email'],
                'gender' => $member['gender'],
                'date_of_birth' => $member['date_of_birth'],
                'physical_address' => $member['physical_address']
            ];

            // Update member
            $stmt = $pdo->prepare('
                UPDATE members SET
                    first_name = ?,
                    last_name = ?,
                    phone = ?,
                    email = ?,
                    gender = ?,
                    date_of_birth = ?,
                    physical_address = ?
                WHERE id = ?
            ');

            $stmt->execute([
                $firstName,
                $lastName,
                $phone ?: null,
                $email ?: null,
                $gender ?: null,
                $dateOfBirth ?: null,
                $address ?: null,
                $memberId
            ]);

            // Log action
            $newValues = [
                'first_name' => $firstName,
                'last_name' => $lastName,
                'phone' => $phone,
                'email' => $email,
                'gender' => $gender,
                'date_of_birth' => $dateOfBirth,
                'physical_address' => $address
            ];

            logDepartmentAction(
                $pdo,
                'member_updated',
                'member',
                $memberId,
                "Updated member: $firstName $lastName (Department: $departmentId)",
                $oldValues,
                $newValues
            );

            // Redirect with success message
            header('Location: view.php?success=1');
            exit;

        } catch (Exception $e) {
            error_log('Update member error: ' . $e->getMessage());
            $error = 'Failed to update member. Please try again.';
        }
    }
}

$pageTitle = 'Edit Member';
?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<div class="mb-6 flex items-center gap-3">
    <a href="view.php" class="inline-flex items-center gap-1 text-sm text-mist-500 hover:text-royal-700 font-semibold transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
        Back
    </a>
    <span class="text-mist-300">/</span>
    <h2 class="text-2xl font-heading font-bold text-royal-800">Edit Member</h2>
</div>

<?php if ($error): ?>
<div class="mb-5 p-3 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700 font-medium">⚠️ <?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<form method="POST" class="max-w-2xl">
    <div class="bg-white rounded-2xl border border-mist-200 shadow-sm p-6 space-y-5">

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="first_name" class="block text-sm font-semibold text-mist-700 mb-1.5">First Name <span class="text-red-400">*</span></label>
                <input type="text" id="first_name" name="first_name" required
                    value="<?php echo htmlspecialchars($_POST['first_name'] ?? $member['first_name']); ?>"
                    class="w-full border border-mist-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-royal-400 focus:ring-2 focus:ring-royal-100">
            </div>
            <div>
                <label for="last_name" class="block text-sm font-semibold text-mist-700 mb-1.5">Last Name <span class="text-red-400">*</span></label>
                <input type="text" id="last_name" name="last_name" required
                    value="<?php echo htmlspecialchars($_POST['last_name'] ?? $member['last_name']); ?>"
                    class="w-full border border-mist-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-royal-400 focus:ring-2 focus:ring-royal-100">
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="phone" class="block text-sm font-semibold text-mist-700 mb-1.5">Phone</label>
                <input type="tel" id="phone" name="phone"
                    value="<?php echo htmlspecialchars($_POST['phone'] ?? $member['phone'] ?? ''); ?>"
                    class="w-full border border-mist-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-royal-400 focus:ring-2 focus:ring-royal-100">
            </div>
            <div>
                <label for="email" class="block text-sm font-semibold text-mist-700 mb-1.5">Email</label>
                <input type="email" id="email" name="email"
                    value="<?php echo htmlspecialchars($_POST['email'] ?? $member['email'] ?? ''); ?>"
                    class="w-full border border-mist-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-royal-400 focus:ring-2 focus:ring-royal-100">
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="gender" class="block text-sm font-semibold text-mist-700 mb-1.5">Gender</label>
                <select id="gender" name="gender" class="w-full border border-mist-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-royal-400 focus:ring-2 focus:ring-royal-100">
                    <option value="">Select…</option>
                    <?php foreach (['male'=>'Male','female'=>'Female','other'=>'Other'] as $v=>$l): ?>
                    <option value="<?php echo $v; ?>" <?php echo strtolower($_POST['gender'] ?? $member['gender'] ?? '') === $v ? 'selected' : ''; ?>><?php echo $l; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="date_of_birth" class="block text-sm font-semibold text-mist-700 mb-1.5">Date of Birth</label>
                <input type="date" id="date_of_birth" name="date_of_birth"
                    value="<?php echo htmlspecialchars($_POST['date_of_birth'] ?? $member['date_of_birth'] ?? ''); ?>"
                    class="w-full border border-mist-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-royal-400 focus:ring-2 focus:ring-royal-100">
            </div>
        </div>

        <div>
            <label for="address" class="block text-sm font-semibold text-mist-700 mb-1.5">Home Address</label>
            <textarea id="address" name="address" rows="3"
                class="w-full border border-mist-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-royal-400 focus:ring-2 focus:ring-royal-100 resize-none"><?php echo htmlspecialchars($_POST['address'] ?? $member['physical_address'] ?? ''); ?></textarea>
        </div>

        <div class="flex gap-3 pt-2">
            <button type="submit" class="px-6 py-2.5 bg-royal-600 hover:bg-royal-700 text-white rounded-xl text-sm font-semibold shadow transition">Update Member</button>
            <a href="view.php" class="px-6 py-2.5 bg-mist-100 hover:bg-mist-200 text-mist-700 rounded-xl text-sm font-semibold transition">Cancel</a>
        </div>
    </div>
</form>

<?php include __DIR__ . '/../includes/footer.php'; ?>

<?php

/**
 * Log department action with old/new values
 */
// logDepartmentAction is now centralized in includes/session.php
