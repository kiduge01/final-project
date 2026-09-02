<?php
// Check if column exists and add if it doesn't
$dsn = 'mysql:host=localhost;dbname=church_cms';
$username = 'root';
$password = '';

try {
    $pdo = new PDO($dsn, $username, $password);
    
    // Check if request_title column exists
    $stmt = $pdo->query("SHOW COLUMNS FROM department_budgets WHERE Field = 'request_title'");
    $exists = $stmt->rowCount() > 0;
    
    if (!$exists) {
        echo "Adding request_title column to department_budgets table...\n";
        
        // Add the column
        $pdo->exec("ALTER TABLE department_budgets ADD COLUMN request_title VARCHAR(255) NULL AFTER description");
        
        // Update existing records with a default title
        $pdo->exec("UPDATE department_budgets SET request_title = CONCAT(department, ' - ', COALESCE(description, 'Budget Request')) WHERE request_title IS NULL");
        
        echo "✓ Migration completed successfully!\n";
    } else {
        echo "✓ request_title column already exists.\n";
    }
    
    // Show the table structure
    echo "\nCurrent department_budgets columns:\n";
    $stmt = $pdo->query("SHOW COLUMNS FROM department_budgets");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($columns as $col) {
        if (in_array($col['Field'], ['request_title', 'description', 'department'])) {
            echo "  - {$col['Field']}: {$col['Type']}\n";
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
