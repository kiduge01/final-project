<?php
$config = require_once __DIR__ . '/app/config.php';
$db = $config['db'];

try {
    $pdo = new PDO(
        'mysql:host=' . $db['host'] . ';dbname=' . $db['name'] . ';charset=' . $db['charset'],
        $db['user'],
        $db['pass'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    echo "🔧 Applying procurement schema fixes...\n\n";

    // Step 1: Create missing tables first
    echo "Step 1: Creating department_budgets table...\n";
    $pdo->exec("CREATE TABLE IF NOT EXISTS department_budgets (
      id             BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
      department     VARCHAR(120) NOT NULL,
      category_id    BIGINT UNSIGNED NULL,
      fiscal_month   VARCHAR(7)   NOT NULL COMMENT 'YYYY-MM',
      planned_amount DECIMAL(14,2) NOT NULL DEFAULT 0.00,
      actual_amount  DECIMAL(14,2) NOT NULL DEFAULT 0.00,
      reserved_amount DECIMAL(14,2) NOT NULL DEFAULT 0.00,
      status         ENUM('draft','submitted','approved','rejected','expenses_added','closed') NOT NULL DEFAULT 'draft',
      submitted_by   BIGINT UNSIGNED NULL,
      approved_by    BIGINT UNSIGNED NULL,
      approved_at    DATETIME NULL,
      event_id       BIGINT UNSIGNED NULL,
      description    TEXT NULL,
      notes          TEXT NULL,
      created_at     TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
      updated_at     TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      INDEX idx_db_dept (department),
      INDEX idx_db_month (fiscal_month),
      INDEX idx_db_status (status),
      INDEX idx_db_event (event_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    echo "  ✓ OK\n";

    echo "Step 2: Creating budget_expenses table...\n";
    $pdo->exec("CREATE TABLE IF NOT EXISTS budget_expenses (
      id           BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
      budget_id    BIGINT UNSIGNED NOT NULL,
      item_name    VARCHAR(220) NOT NULL,
      amount       DECIMAL(14,2) NOT NULL DEFAULT 0.00,
      expense_date DATE NOT NULL,
      notes        TEXT NULL,
      source_type  VARCHAR(40) NULL COMMENT 'manual, procurement',
      source_id    BIGINT UNSIGNED NULL,
      recorded_by  BIGINT UNSIGNED NULL,
      created_at   TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
      updated_at   TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      INDEX idx_be_budget (budget_id),
      INDEX idx_be_date (expense_date),
      CONSTRAINT fk_be_budget FOREIGN KEY (budget_id) REFERENCES department_budgets(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    echo "  ✓ OK\n";

    echo "Step 3: Creating purchase_request_items table...\n";
    $pdo->exec("CREATE TABLE IF NOT EXISTS purchase_request_items (
      id                    BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
      purchase_request_id   BIGINT UNSIGNED NOT NULL,
      item_name             VARCHAR(220) NOT NULL,
      quantity              DECIMAL(12,2) NOT NULL DEFAULT 1.00,
      estimated_unit_cost   DECIMAL(14,2) NOT NULL DEFAULT 0.00,
      notes                 VARCHAR(255) NULL,
      created_at            TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
      INDEX idx_pri_pr (purchase_request_id),
      CONSTRAINT fk_pri_pr FOREIGN KEY (purchase_request_id) REFERENCES purchase_requests(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    echo "  ✓ OK\n";

    echo "Step 4: Creating approval_logs table...\n";
    $pdo->exec("CREATE TABLE IF NOT EXISTS approval_logs (
      id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
      entity_type VARCHAR(60)  NOT NULL COMMENT 'e.g. procurement, budget',
      entity_id   BIGINT UNSIGNED NOT NULL,
      level_no    TINYINT UNSIGNED NOT NULL DEFAULT 1,
      action      VARCHAR(40)  NOT NULL COMMENT 'submitted, approved, rejected',
      actor_id    BIGINT UNSIGNED NULL,
      notes       VARCHAR(500) NULL,
      acted_at    TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
      INDEX idx_al_entity (entity_type, entity_id),
      INDEX idx_al_actor (actor_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    echo "  ✓ OK\n";

    echo "Step 5: Altering purchase_requests table...\n";
    
    // Add columns one by one with IF NOT EXISTS check
    $columnsToAdd = [
        'budget_id' => 'ALTER TABLE purchase_requests ADD COLUMN budget_id BIGINT UNSIGNED NULL AFTER event_id',
        'vendor_name' => 'ALTER TABLE purchase_requests ADD COLUMN vendor_name VARCHAR(180) NULL AFTER budget_id',
        'approved_by' => 'ALTER TABLE purchase_requests ADD COLUMN approved_by BIGINT UNSIGNED NULL AFTER vendor_name',
        'approved_at' => 'ALTER TABLE purchase_requests ADD COLUMN approved_at DATETIME NULL AFTER approved_by',
        'rejection_reason' => 'ALTER TABLE purchase_requests ADD COLUMN rejection_reason TEXT NULL AFTER approved_at',
        'completed_at' => 'ALTER TABLE purchase_requests ADD COLUMN completed_at DATETIME NULL AFTER rejection_reason'
    ];

    foreach ($columnsToAdd as $colName => $sql) {
        try {
            $pdo->exec($sql);
            echo "  ✓ Added column: $colName\n";
        } catch (Exception $e) {
            // Column might already exist
            if (strpos($e->getMessage(), 'Duplicate') === false) {
                echo "  ⚠ $colName: " . $e->getMessage() . "\n";
            } else {
                echo "  ✓ $colName already exists\n";
            }
        }
    }

    echo "Step 6: Updating purchase_requests status enum...\n";
    try {
        $pdo->exec("ALTER TABLE purchase_requests MODIFY COLUMN status ENUM('draft','submitted','approved','rejected','purchased','completed','cancelled','ordered','closed') NOT NULL DEFAULT 'draft'");
        echo "  ✓ Status enum updated\n";
    } catch (Exception $e) {
        echo "  ⚠ " . $e->getMessage() . "\n";
    }

    echo "\n✅ Migration completed!\n\n";
    
    // Verify
    echo "📋 Verifying columns...\n";
    $stmt = $pdo->query("SHOW COLUMNS FROM purchase_requests WHERE Field IN ('budget_id', 'vendor_name', 'approved_by', 'approved_at', 'rejection_reason', 'completed_at')");
    $found = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
    echo "Found " . count($found) . " of 6 required columns\n";
    
    if (count($found) === 6) {
        echo "\n✅ All procurement columns are now in place! Ready to go.\n";
    } else {
        echo "\n⚠ Some columns still missing. Check output above.\n";
    }

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
