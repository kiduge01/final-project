<?php
/**
 * Admin: Manage Department Head Credentials
 * 
 * Allows admin to set email/password for department heads
 * to login to the department subsystem
 * 
 * This is separate from the main admin login system.
 * Used via the main admin interface.
 */

// This file is meant to be included in the main admin's API or modal
// Example endpoint: /api/v1/admin/departments/set-credentials

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get database connection from main system
    require_once __DIR__ . '/../../app/config.php';
    require_once __DIR__ . '/../../app/core/Database.php';
    require_once __DIR__ . '/../../app/core/Response.php';
    require_once __DIR__ . '/../../app/core/Auth.php';
    
    $pdo = \App\Core\Database::connection($config);
    
    // Verify admin is authenticated
    if (!Auth::check()) {
        http_response_code(401);
        Response::json(['success' => false, 'message' => 'Unauthorized']);
        return;
    }
    
    // Get input
    $departmentId = intval($_POST['department_id'] ?? 0);
    $headEmail = trim($_POST['head_email'] ?? '');
    $headPassword = trim($_POST['head_password'] ?? '');
    $confirmPassword = trim($_POST['confirm_password'] ?? '');
    
    // Validation
    if (!$departmentId) {
        http_response_code(400);
        Response::json(['success' => false, 'message' => 'Department ID required']);
        return;
    }
    
    if (empty($headEmail) || !filter_var($headEmail, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        Response::json(['success' => false, 'message' => 'Valid email required']);
        return;
    }
    
    if (empty($headPassword) || strlen($headPassword) < 6) {
        http_response_code(400);
        Response::json(['success' => false, 'message' => 'Password must be at least 6 characters']);
        return;
    }
    
    if ($headPassword !== $confirmPassword) {
        http_response_code(400);
        Response::json(['success' => false, 'message' => 'Passwords do not match']);
        return;
    }
    
    try {
        // Verify department exists
        $stmt = $pdo->prepare('SELECT id FROM departments WHERE id = ?');
        $stmt->execute([$departmentId]);
        if (!$stmt->fetch()) {
            http_response_code(404);
            Response::json(['success' => false, 'message' => 'Department not found']);
            return;
        }
        
        // Check if email is already used by another department
        $stmt = $pdo->prepare('SELECT id FROM departments WHERE head_email = ? AND id != ?');
        $stmt->execute([$headEmail, $departmentId]);
        if ($stmt->fetch()) {
            http_response_code(400);
            Response::json(['success' => false, 'message' => 'Email already used by another department']);
            return;
        }
        
        // Hash password
        $hashedPassword = password_hash($headPassword, PASSWORD_DEFAULT);
        
        // Update department with credentials
        $stmt = $pdo->prepare('
            UPDATE departments 
            SET head_email = ?, head_password_hash = ?
            WHERE id = ?
        ');
        $stmt->execute([$headEmail, $hashedPassword, $departmentId]);
        
        // Log action
        $stmt = $pdo->prepare('
            INSERT INTO audit_logs (actor_id, module, action, entity, entity_id, summary, ip_address, user_agent)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ');
        $stmt->execute([
            Auth::user()['id'],
            'departments',
            'set_head_credentials',
            'department',
            $departmentId,
            'Set department head login credentials: ' . $headEmail,
            $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ]);
        
        http_response_code(200);
        Response::json([
            'success' => true,
            'message' => 'Department head credentials updated successfully',
            'department_id' => $departmentId,
            'head_email' => $headEmail
        ]);
        
    } catch (Exception $e) {
        error_log('Set department credentials error: ' . $e->getMessage());
        http_response_code(500);
        Response::json(['success' => false, 'message' => 'Failed to update credentials']);
    }
}

// If GET request, return the form (for modal display)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    require_once __DIR__ . '/../../app/config.php';
    require_once __DIR__ . '/../../app/core/Database.php';
    require_once __DIR__ . '/../../app/core/Auth.php';
    
    $pdo = \App\Core\Database::connection($config);
    
    if (!Auth::check()) {
        http_response_code(401);
        return;
    }
    
    $departmentId = intval($_GET['id'] ?? 0);
    
    if (!$departmentId) {
        http_response_code(400);
        return;
    }
    
    try {
        $stmt = $pdo->prepare('SELECT id, name, head_email FROM departments WHERE id = ?');
        $stmt->execute([$departmentId]);
        $department = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if (!$department) {
            http_response_code(404);
            return;
        }
        
        // Return form HTML for modal
        ?>
        <div class="modal-header">
            <h3>Set Department Head Login Credentials</h3>
            <p class="text-muted"><?php echo htmlspecialchars($department['name']); ?></p>
        </div>
        
        <form id="deptHeadCredentialsForm" class="form">
            <input type="hidden" name="department_id" value="<?php echo $departmentId; ?>">
            
            <div class="form-group">
                <label for="head_email">Department Head Email *</label>
                <input 
                    type="email" 
                    id="head_email" 
                    name="head_email" 
                    class="form-control"
                    value="<?php echo htmlspecialchars($department['head_email'] ?? ''); ?>"
                    placeholder="head@department.local"
                    required
                >
                <small class="text-muted">Used for department login</small>
            </div>
            
            <div class="form-group">
                <label for="head_password">Password *</label>
                <input 
                    type="password" 
                    id="head_password" 
                    name="head_password" 
                    class="form-control"
                    placeholder="Enter secure password"
                    minlength="6"
                    required
                >
                <small class="text-muted">Minimum 6 characters</small>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Confirm Password *</label>
                <input 
                    type="password" 
                    id="confirm_password" 
                    name="confirm_password" 
                    class="form-control"
                    placeholder="Repeat password"
                    minlength="6"
                    required
                >
            </div>
            
            <div class="alert alert-info">
                <strong>Note:</strong> This is separate from the main admin login.
                The department head will use this email/password to login to the department subsystem.
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Save Credentials</button>
                <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
            </div>
        </form>
        
        <script>
        document.getElementById('deptHeadCredentialsForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            try {
                const response = await fetch(window.location.pathname, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': getCsrfToken()
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showNotification('Department head credentials updated successfully!', 'success');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showNotification(data.message || 'Error saving credentials', 'danger');
                }
            } catch (error) {
                console.error('Error:', error);
                showNotification('Failed to save credentials', 'danger');
            }
        });
        
        function getCsrfToken() {
            return document.querySelector('meta[name="csrf-token"]')?.content || '';
        }
        
        function showNotification(message, type) {
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type}`;
            alertDiv.textContent = message;
            document.body.insertBefore(alertDiv, document.body.firstChild);
            setTimeout(() => alertDiv.remove(), 3000);
        }
        </script>
        <?php
        
    } catch (Exception $e) {
        http_response_code(500);
        echo 'Error loading form';
    }
}
