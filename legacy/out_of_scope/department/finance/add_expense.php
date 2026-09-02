<?php
/**
 * Add Expense Record
 * 
 * Record an expense transaction for the department.
 */

require_once __DIR__ . '/../includes/auth_check.php';

$pdo = require __DIR__ . '/../includes/db.php';
$departmentId = getCurrentDepartmentId();

$error = '';

    // Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date = trim($_POST['date'] ?? '');
    $categoryName = trim($_POST['category'] ?? '');
    $amount = trim($_POST['amount'] ?? '');
    $description = trim($_POST['description'] ?? '');

    // Validation
    if (empty($date) || empty($categoryName) || empty($amount)) {
        $error = 'Date, category, and amount are required.';
    } elseif (!is_numeric($amount) || $amount <= 0) {
        $error = 'Amount must be a valid positive number.';
    } else {
        try {
            // 1. Get or create category ID
            $stmtCat = $pdo->prepare('SELECT id FROM finance_categories WHERE name = ? AND category_type = "expense" LIMIT 1');
            $stmtCat->execute([$categoryName]);
            $categoryId = $stmtCat->fetchColumn();

            if (!$categoryId) {
                $stmtInsCat = $pdo->prepare('INSERT INTO finance_categories (name, category_type) VALUES (?, "expense")');
                $stmtInsCat->execute([$categoryName]);
                $categoryId = $pdo->lastInsertId();
            }

            // 2. Insert expense record
            $stmt = $pdo->prepare('
                INSERT INTO finance_entries (
                    department_id,
                    category_id,
                    amount,
                    description,
                    entry_date,
                    created_at
                ) VALUES (?, ?, ?, ?, ?, NOW())
            ');

            $stmt->execute([
                $departmentId,
                $categoryId,
                (float)$amount,
                $description ?: null,
                $date
            ]);

            $newId = $pdo->lastInsertId();

            // 3. Log action
            logDepartmentAction(
                $pdo,
                'expense_added',
                'finance',
                $newId,
                "Added expense: $categoryName - Tsh " . number_format($amount, 2)
            );

            // Redirect with success message
            header('Location: view.php?success=1');
            exit;

        } catch (Exception $e) {
            error_log('Add expense error: ' . $e->getMessage());
            $error = 'Failed to record expense: ' . $e->getMessage();
        }
    }
}

$pageTitle = 'Record Expense';
?>
<?php include __DIR__ . '/../includes/header.php'; ?>
<?php include __DIR__ . '/../includes/sidebar.php'; ?>

            <div class="page-container">
                <div class="page-header">
                    <h2>Record Expense</h2>
                    <p>Add a new expense transaction</p>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <strong>Error:</strong> <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" class="form-container">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="date">Date <span class="required">*</span></label>
                            <input 
                                type="date" 
                                id="date" 
                                name="date" 
                                required
                                value="<?php echo htmlspecialchars($_POST['date'] ?? date('Y-m-d')); ?>"
                            >
                        </div>
                        <div class="form-group">
                            <label for="category">Expense Category <span class="required">*</span></label>
                            <select id="category" name="category" required>
                                <option value="">Select category...</option>
                                <option value="Supplies" <?php echo ($_POST['category'] ?? '') === 'Supplies' ? 'selected' : ''; ?>>Supplies</option>
                                <option value="Maintenance" <?php echo ($_POST['category'] ?? '') === 'Maintenance' ? 'selected' : ''; ?>>Maintenance</option>
                                <option value="Utilities" <?php echo ($_POST['category'] ?? '') === 'Utilities' ? 'selected' : ''; ?>>Utilities</option>
                                <option value="Event" <?php echo ($_POST['category'] ?? '') === 'Event' ? 'selected' : ''; ?>>Event Expenses</option>
                                <option value="Procurement" <?php echo ($_POST['category'] ?? '') === 'Procurement' ? 'selected' : ''; ?>>Procurement</option>
                                <option value="Honorarium" <?php echo ($_POST['category'] ?? '') === 'Honorarium' ? 'selected' : ''; ?>>Honorarium</option>
                                <option value="Other" <?php echo ($_POST['category'] ?? '') === 'Other' ? 'selected' : ''; ?>>Other</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="amount">Amount (Tsh) <span class="required">*</span></label>
                            <input 
                                type="number" 
                                id="amount" 
                                name="amount" 
                                required
                                step="0.01"
                                min="0"
                                placeholder="0.00"
                                value="<?php echo htmlspecialchars($_POST['amount'] ?? ''); ?>"
                            >
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea 
                            id="description" 
                            name="description" 
                            rows="4"
                            placeholder="Additional details about this expense..."
                        ><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-save"></i> Record Expense
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
