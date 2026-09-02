<?php
/**
 * Migration Runner Script
 * Helps apply all pending database migrations in sequence
 */

echo "<h1>Church CMS - Database Migration Runner</h1>";
echo "<hr>";

$config = require __DIR__ . '/app/config.php';
$db = $config['db'];

// Connection
try {
    $pdo = new PDO(
        sprintf('mysql:host=%s;port=%d;dbname=%s;charset=%s', $db['host'], $db['port'], $db['name'], $db['charset']),
        $db['user'],
        $db['pass'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "<p><strong style='color:green'>✓ Connected to database</strong></p>";
} catch (PDOException $e) {
    echo "<p><strong style='color:red'>✗ Connection failed:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    exit;
}

// List of migrations to run (in order)
$migrations = [
    'database/migrations/2026_03_29_001_create_theme_verses.sql',
    'database/migrations/2026_03_30_001_add_budget_approval_tracking.sql',
    'database/migrations/2026_03_30_001_finance_module_upgrade.sql',
    'database/migrations/2026_04_01_001_create_departments_and_budget_workflow.sql',
    'database/migrations/2026_04_02_001_create_messages_table.sql',
    'database/migrations/2026_04_02_001_finance_procurement_integration.sql',
    'database/migrations/2026_04_02_002_create_password_reset_tokens.sql',
    'database/migrations/2026_04_02_003_add_church_logo_setting.sql',
    'database/migrations/2026_04_02_004_create_login_attempts_table.sql',
    'database/migrations/2026_04_03_001_create_church_settings.sql',
    'database/migrations/2026_04_07_001_add_member_details_fields.sql',
    'database/migrations/2026_04_09_001_modify_departments_for_separate_login.sql',
    'database/migrations/2026_04_09_002_create_department_members_junction.sql',
    'database/migrations/2026_04_10_001_add_head_name_to_departments.sql',
    'database/migrations/2026_04_13_001_create_guests_table.sql',
    'database/migrations/2026_04_13_002_add_pastor_usher_to_events.sql',
    'database/migrations/2026_04_14_001_add_budget_workflow_columns.sql',
    'database/migrations/2026_04_14_001_department_contributions_assets.sql',
    'database/migrations/2026_04_14_002_create_department_finance_records.sql',
    'database/migrations/2026_04_14_003_create_dept_leaders_and_reports.sql',
    'database/migrations/2026_04_14_004_create_permissions_tables.sql',
    'database/migrations/2026_04_14_005_procurement_full_schema.sql',
    'database/migrations/2026_04_15_001_create_church_accounts.sql',
    'database/migrations/2026_04_15_002_approval_workflow_improvements.sql',
    'database/migrations/2026_05_14_001_create_zones_management.sql',
    'database/migrations/2026_05_14_002_add_zone_to_members.sql',
];

echo "<h2>Running Migrations</h2>";
echo "<ol>";

foreach ($migrations as $migrationFile) {
    $filePath = __DIR__ . '/' . $migrationFile;
    
    if (!file_exists($filePath)) {
        echo "<li><span style='color:orange'>⊘ SKIP</span> - File not found: <code>" . htmlspecialchars($migrationFile) . "</code></li>";
        continue;
    }
    
    $sql = file_get_contents($filePath);
    
    try {
        // Split multiple statements and execute them
        $statements = array_filter(
            array_map('trim', preg_split('/;[\s\n]+/', $sql)),
            fn($s) => !empty($s) && !str_starts_with(trim($s), '--')
        );
        
        foreach ($statements as $statement) {
            if (trim($statement)) {
                $pdo->exec($statement);
            }
        }
        
        echo "<li><span style='color:green'>✓ OK</span> - <code>" . htmlspecialchars($migrationFile) . "</code></li>";
    } catch (PDOException $e) {
        // If migration failed, check if it's because it's idempotent (IF NOT EXISTS)
        $errorMsg = $e->getMessage();
        if (str_contains($errorMsg, 'already exists') || str_contains($errorMsg, 'Duplicate') || str_contains($errorMsg, 'already') || str_contains($errorMsg, 'UNIQUE constraint failed')) {
            echo "<li><span style='color:blue'>~ SKIP</span> - Already applied: <code>" . htmlspecialchars($migrationFile) . "</code></li>";
        } else {
            echo "<li><span style='color:red'>✗ ERROR</span> - <code>" . htmlspecialchars($migrationFile) . "</code><br>";
            echo "  Error: " . htmlspecialchars($errorMsg) . "</li>";
        }
    }
}

echo "</ol>";
echo "<hr>";
echo "<p><strong style='color:green'>✓ Migration process completed!</strong></p>";
echo "<p><a href='public/'>Go to dashboard</a></p>";
?>
