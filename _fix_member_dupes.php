<?php
/**
 * Fix: Deduplicate members table and add UNIQUE constraint on phone.
 * Keeps the lowest ID per phone, removes duplicates from department_members, then deletes duplicate member records.
 */
$config = require __DIR__ . '/app/config.php';
require_once __DIR__ . '/app/core/Database.php';
$pdo = \App\Core\Database::connection($config);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// 1. Find canonical (lowest) member ID per phone
$keepers = $pdo->query('SELECT MIN(id) as keep_id, phone FROM members WHERE phone IS NOT NULL AND phone != "" GROUP BY phone')->fetchAll(PDO::FETCH_ASSOC);
$keepIds = array_column($keepers, 'keep_id');

// 2. For duplicate members (same phone, higher ID), delete their department_members rows
$deleted_dm = 0;
$deleted_m  = 0;
if (!empty($keepIds)) {
    $placeholders = implode(',', array_fill(0, count($keepIds), '?'));
    // Delete department_members that point to duplicate (non-keeper) members with a phone
    $stmt = $pdo->prepare("DELETE FROM department_members WHERE member_id NOT IN ($placeholders) AND member_id IN (SELECT id FROM members WHERE phone IS NOT NULL AND phone != '')");
    $stmt->execute($keepIds);
    $deleted_dm = $stmt->rowCount();

    // Delete the duplicate member records
    $stmt2 = $pdo->prepare("DELETE FROM members WHERE id NOT IN ($placeholders) AND phone IS NOT NULL AND phone != ''");
    $stmt2->execute($keepIds);
    $deleted_m = $stmt2->rowCount();
}

echo "Deleted $deleted_dm duplicate department_member rows.\n";
echo "Deleted $deleted_m duplicate member records.\n";

// 3. Add UNIQUE constraint on phone (nullable phones are allowed as duplicates in MySQL, so UNIQUE on NULL is safe)
try {
    $pdo->exec('ALTER TABLE members ADD UNIQUE KEY uq_members_phone (phone)');
    echo "UNIQUE KEY added on members.phone\n";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate') !== false) {
        echo "Still duplicates exist (possibly NULL phones). Key not added: " . $e->getMessage() . "\n";
    } elseif (strpos($e->getMessage(), 'already exists') !== false || $e->getCode() == '42S21') {
        echo "UNIQUE KEY already exists on members.phone\n";
    } else {
        echo "Error adding UNIQUE key: " . $e->getMessage() . "\n";
    }
}

echo "Done.\n";
