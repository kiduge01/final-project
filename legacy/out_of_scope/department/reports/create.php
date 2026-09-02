<?php
/**
 * Create Department Report
 * 
 * Create a new report (saved as draft).
 */

require_once __DIR__ . '/../includes/auth_check.php';

$pdo = require __DIR__ . '/../includes/db.php';
$departmentId = getCurrentDepartmentId();

$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $reportDate = trim($_POST['report_date'] ?? '');
    $category = trim($_POST['category'] ?? '');

    // Validation
    if (empty($title) || empty($description) || empty($reportDate) || empty($category)) {
        $error = 'Title, description, report date, and category are required.';
    } else {
        try {
            // Insert report as draft
            $stmt = $pdo->prepare('
                INSERT INTO department_reports (
                    department_id,
                    title,
                    description,
                    report_date,
                    category,
                    status,
                    created_at
                ) VALUES (?, ?, ?, ?, ?, ?, NOW())
            ');

            $stmt->execute([
                $departmentId,
                $title,
                $description,
                $reportDate,
                $category,
                'draft'
            ]);

            $reportId = $pdo->lastInsertId();

            // Log action
            logDepartmentAction($pdo, 'report_created', 'report', $reportId, "Created report: $title");

            // Redirect to view page
            header('Location: view-detail.php?id=' . $reportId . '&success=1');
            exit;

        } catch (Exception $e) {
            error_log('Create report error: ' . $e->getMessage());
            $error = 'Failed to create report. Please try again.';
        }
    }
}

$pageTitle = 'Create New Report';
?>
<?php include __DIR__ . '/../includes/header.php'; ?>

            <div class="page-container">
                <div class="page-header">
                    <h2>Create New Report</h2>
                    <p>Write a detailed report for the department</p>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <strong>Error:</strong> <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" class="form-container">
                    <div class="form-group">
                        <label for="title">Report Title <span class="required">*</span></label>
                        <input 
                            type="text" 
                            id="title" 
                            name="title" 
                            required
                            placeholder="e.g., Monthly Activity Report"
                            value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>"
                        >
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="report_date">Report Date <span class="required">*</span></label>
                            <input 
                                type="date" 
                                id="report_date" 
                                name="report_date" 
                                required
                                value="<?php echo htmlspecialchars($_POST['report_date'] ?? date('Y-m-d')); ?>"
                            >
                        </div>
                        <div class="form-group">
                            <label for="category">Report Category <span class="required">*</span></label>
                            <select id="category" name="category" required>
                                <option value="">Select category...</option>
                                <option value="Weekly" <?php echo ($_POST['category'] ?? '') === 'Weekly' ? 'selected' : ''; ?>>Weekly Report</option>
                                <option value="Monthly" <?php echo ($_POST['category'] ?? '') === 'Monthly' ? 'selected' : ''; ?>>Monthly Report</option>
                                <option value="Activity" <?php echo ($_POST['category'] ?? '') === 'Activity' ? 'selected' : ''; ?>>Activity Report</option>
                                <option value="Finance" <?php echo ($_POST['category'] ?? '') === 'Finance' ? 'selected' : ''; ?>>Finance Report</option>
                                <option value="Attendance" <?php echo ($_POST['category'] ?? '') === 'Attendance' ? 'selected' : ''; ?>>Attendance Report</option>
                                <option value="Quarterly" <?php echo ($_POST['category'] ?? '') === 'Quarterly' ? 'selected' : ''; ?>>Quarterly Report</option>
                                <option value="Annual" <?php echo ($_POST['category'] ?? '') === 'Annual' ? 'selected' : ''; ?>>Annual Report</option>
                                <option value="Event" <?php echo ($_POST['category'] ?? '') === 'Event' ? 'selected' : ''; ?>>Event Report</option>
                                <option value="Other" <?php echo ($_POST['category'] ?? '') === 'Other' ? 'selected' : ''; ?>>Other</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="description">Report Content <span class="required">*</span></label>
                        <textarea 
                            id="description" 
                            name="description" 
                            required
                            rows="10"
                            placeholder="Write your detailed report here. Include activities, achievements, challenges, and recommendations..."
                        ><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Save as Draft
                        </button>
                        <a href="view.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>

                <div class="info-box">
                    <strong>Tip:</strong> Reports are saved as drafts. You can edit them later before submitting to the administrator.
                </div>
            </div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

<?php
// logDepartmentAction is now centralized in includes/session.php
