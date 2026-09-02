<?php
/**
 * Add Member to Department
 * 
 * Form to add a new member to the department.
 */

require_once __DIR__ . '/../includes/auth_check.php';

$pdo = require __DIR__ . '/../includes/db.php';
$departmentId = getCurrentDepartmentId();

$error = '';
$success = '';

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
    } elseif (empty($phone)) {
        $error = 'Phone number is required.';
    } elseif (empty($gender)) {
        $error = 'Gender is required.';
    } elseif (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format.';
    } else {
        try {
            // Start transaction
            $pdo->beginTransaction();

            // Validate required fields for schema
            if (empty($phone)) {
                throw new Exception('Phone number is required.');
            }
            if (empty($gender)) {
                throw new Exception('Gender is required.');
            }

            // Insert member
            $stmt = $pdo->prepare('
                INSERT INTO members (
                    member_code,
                    first_name,
                    last_name,
                    phone,
                    email,
                    gender,
                    date_of_birth,
                    physical_address,
                    member_status,
                    join_date
                ) VALUES (
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    "active",
                    NOW()
                )
            ');

            // Generate member code
            $memberCode = generateMemberCode($pdo);

            $stmt->execute([
                $memberCode,
                $firstName,
                $lastName,
                $phone,
                $email ?: null,
                $gender,
                $dateOfBirth ?: null,
                $address ?: null
            ]);

            $memberId = $pdo->lastInsertId();

            // Link member to department
            $stmt = $pdo->prepare('
                INSERT INTO department_members (
                    department_id,
                    member_id,
                    assigned_date
                ) VALUES (?, ?, NOW())
            ');
            $stmt->execute([$departmentId, $memberId]);

            // Commit transaction
            $pdo->commit();

            // Log action
            logDepartmentAction($pdo, 'member_added', 'member', $memberId, "Added member: $firstName $lastName (Department: $departmentId)");

            // Redirect with success message
            header('Location: view.php?success=1');
            exit;

        } catch (Exception $e) {
            $pdo->rollBack();
            error_log('Add member error: ' . $e->getMessage());
            $error = 'Failed to add member. Please try again.';
        }
    }
}

$pageTitle = 'Add New Member';
?>
<?php include __DIR__ . '/../includes/header.php'; ?>
<?php include __DIR__ . '/../includes/sidebar.php'; ?>

            <div class="page-container">
                <div class="page-header">
                    <h2>Add New Member</h2>
                    <p>Register a new member in your department</p>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <strong>Error:</strong> <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" class="form-container">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="first_name">First Name <span class="required">*</span></label>
                            <input 
                                type="text" 
                                id="first_name" 
                                name="first_name" 
                                required
                                placeholder="John"
                                value="<?php echo htmlspecialchars($_POST['first_name'] ?? ''); ?>"
                            >
                        </div>
                        <div class="form-group">
                            <label for="last_name">Last Name <span class="required">*</span></label>
                            <input 
                                type="text" 
                                id="last_name" 
                                name="last_name" 
                                required
                                placeholder="Doe"
                                value="<?php echo htmlspecialchars($_POST['last_name'] ?? ''); ?>"
                            >
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="phone">Phone Number <span class="required">*</span></label>
                            <input 
                                type="tel" 
                                id="phone" 
                                name="phone" 
                                required
                                placeholder="+255 700 000000"
                                value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>"
                            >
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input 
                                type="email" 
                                id="email" 
                                name="email" 
                                placeholder="john@example.com"
                                value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                            >
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="gender">Gender <span class="required">*</span></label>
                            <select id="gender" name="gender" required>
                                <option value="">Select...</option>
                                <option value="male" <?php echo ($_POST['gender'] ?? '') === 'male' ? 'selected' : ''; ?>>Male</option>
                                <option value="female" <?php echo ($_POST['gender'] ?? '') === 'female' ? 'selected' : ''; ?>>Female</option>
                                <option value="other" <?php echo ($_POST['gender'] ?? '') === 'other' ? 'selected' : ''; ?>>Other</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="date_of_birth">Date of Birth</label>
                            <input 
                                type="date" 
                                id="date_of_birth" 
                                name="date_of_birth"
                                value="<?php echo htmlspecialchars($_POST['date_of_birth'] ?? ''); ?>"
                            >
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="address">Home Address</label>
                        <textarea 
                            id="address" 
                            name="address" 
                            rows="3"
                            placeholder="Enter home address..."
                        ><?php echo htmlspecialchars($_POST['address'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Add Member
                        </button>
                        <a href="view.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

<?php

/**
 * Generate unique member code
 */
function generateMemberCode($pdo) {
    $year = date('Y');
    $stmt = $pdo->prepare('
        SELECT MAX(CAST(SUBSTRING(member_code, -4) AS UNSIGNED)) as max_code
        FROM members
        WHERE member_code LIKE ?
    ');
    $stmt->execute(["MBR-$year-%"]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $nextNum = ($result['max_code'] ?? 0) + 1;
    return 'MBR-' . $year . '-' . str_pad($nextNum, 4, '0', STR_PAD_LEFT);
}

// logDepartmentAction is now centralized in includes/session.php
