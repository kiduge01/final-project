<?php
/**
 * Add Department Leader
 * 
 * Add a new leadership role to the department.
 */

require_once __DIR__ . '/../includes/auth_check.php';

$pdo = require __DIR__ . '/../includes/db.php';
$departmentId = getCurrentDepartmentId();

$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $leaderType = trim($_POST['leader_type'] ?? '');
    $leaderName = trim($_POST['leader_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $bio = trim($_POST['bio'] ?? '');

    // Validation
    if (empty($leaderType) || empty($leaderName)) {
        $error = 'Leader position and name are required.';
    } elseif (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format.';
    } else {
        try {
            // Insert leader
            $stmt = $pdo->prepare('
                INSERT INTO department_leaders (
                    department_id,
                    leader_type,
                    leader_name,
                    email,
                    phone,
                    bio,
                    is_active
                ) VALUES (?, ?, ?, ?, ?, ?, 1)
            ');

            $stmt->execute([
                $departmentId,
                $leaderType,
                $leaderName,
                $email ?: null,
                $phone ?: null,
                $bio ?: null
            ]);

            // Log action
            logDepartmentAction(
                $pdo,
                'leader_added',
                'leader',
                $pdo->lastInsertId(),
                "Added leader: $leaderName ($leaderType)"
            );

            // Redirect with success message
            header('Location: view.php?success=1');
            exit;

        } catch (Exception $e) {
            error_log('Add leader error: ' . $e->getMessage());
            $error = 'Failed to add leader. Please try again.';
        }
    }
}

$pageTitle = 'Add New Leader';
?>
<?php include __DIR__ . '/../includes/header.php'; ?>
<?php include __DIR__ . '/../includes/sidebar.php'; ?>

            <div class="page-container">
                <div class="page-header">
                    <h2>Add New Leader</h2>
                    <p>Assign a leadership role in the department</p>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <strong>Error:</strong> <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" class="form-container">
                    <div class="form-group">
                        <label for="leader_type">Position/Role <span class="required">*</span></label>
                        <select id="leader_type" name="leader_type" required>
                            <option value="">Select a position...</option>
                            <option value="Chairperson" <?php echo ($_POST['leader_type'] ?? '') === 'Chairperson' ? 'selected' : ''; ?>>Chairperson</option>
                            <option value="Vice Chairperson" <?php echo ($_POST['leader_type'] ?? '') === 'Vice Chairperson' ? 'selected' : ''; ?>>Vice Chairperson</option>
                            <option value="Secretary" <?php echo ($_POST['leader_type'] ?? '') === 'Secretary' ? 'selected' : ''; ?>>Secretary</option>
                            <option value="Treasurer" <?php echo ($_POST['leader_type'] ?? '') === 'Treasurer' ? 'selected' : ''; ?>>Treasurer</option>
                            <option value="Coordinator" <?php echo ($_POST['leader_type'] ?? '') === 'Coordinator' ? 'selected' : ''; ?>>Coordinator</option>
                            <option value="Deputy Coordinator" <?php echo ($_POST['leader_type'] ?? '') === 'Deputy Coordinator' ? 'selected' : ''; ?>>Deputy Coordinator</option>
                            <option value="Member" <?php echo ($_POST['leader_type'] ?? '') === 'Member' ? 'selected' : ''; ?>>Member</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="leader_name">Full Name <span class="required">*</span></label>
                        <input 
                            type="text" 
                            id="leader_name" 
                            name="leader_name" 
                            required
                            placeholder="John Doe"
                            value="<?php echo htmlspecialchars($_POST['leader_name'] ?? ''); ?>"
                        >
                    </div>

                    <div class="form-row">
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
                        <div class="form-group">
                            <label for="phone">Phone Number</label>
                            <input 
                                type="tel" 
                                id="phone" 
                                name="phone" 
                                placeholder="+255 700 000000"
                                value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>"
                            >
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="bio">Bio/Description</label>
                        <textarea 
                            id="bio" 
                            name="bio" 
                            rows="4"
                            placeholder="Brief bio or credentials..."
                        ><?php echo htmlspecialchars($_POST['bio'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Add Leader
                        </button>
                        <a href="view.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

<?php

// logDepartmentAction is now centralized in includes/session.php
