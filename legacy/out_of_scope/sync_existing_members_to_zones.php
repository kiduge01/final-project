<?php
/**
 * Sync Existing Members to Zone Members Table
 * Date: 2026-05-16
 * Purpose: Add all existing members with zone_id to the zone_members table
 * This script migrates members that were assigned zones before the auto-assignment feature was implemented
 */

declare(strict_types=1);

$config = require __DIR__ . '/app/config.php';

require_once __DIR__ . '/app/core/Database.php';

use App\Core\Database;

$pdo = Database::connection($config);

echo "🔄 Starting member-to-zone sync...\n\n";

try {
    // Get all members that have a zone_id but are not in zone_members table
    $stmt = $pdo->prepare('
        SELECT m.id, m.member_code, m.first_name, m.last_name, m.zone_id
        FROM members m
        WHERE m.zone_id IS NOT NULL 
        AND m.zone_id > 0
        AND NOT EXISTS (
            SELECT 1 FROM zone_members zm 
            WHERE zm.member_id = m.id AND zm.zone_id = m.zone_id
        )
        ORDER BY m.zone_id, m.id
    ');
    $stmt->execute();
    $membersToAdd = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Found " . count($membersToAdd) . " members to add to zones.\n\n";
    
    if (count($membersToAdd) === 0) {
        echo "✅ All members are already synced!\n";
        exit(0);
    }
    
    // Add members to zone_members table
    $addStmt = $pdo->prepare('
        INSERT INTO zone_members (zone_id, member_id, is_active, assigned_date, created_at, updated_at)
        VALUES (?, ?, 1, NOW(), NOW(), NOW())
    ');
    
    $added = 0;
    $errors = [];
    
    foreach ($membersToAdd as $member) {
        try {
            $addStmt->execute([$member['zone_id'], $member['id']]);
            $added++;
            echo "✅ Added {$member['member_code']} - {$member['first_name']} {$member['last_name']} (Zone ID: {$member['zone_id']})\n";
        } catch (PDOException $e) {
            $errors[] = "❌ {$member['member_code']}: " . $e->getMessage();
            echo "❌ Failed to add {$member['member_code']}: " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n" . str_repeat("=", 60) . "\n";
    echo "✅ Sync Complete!\n";
    echo "Total added: $added\n";
    
    if (!empty($errors)) {
        echo "Errors: " . count($errors) . "\n\n";
        foreach ($errors as $error) {
            echo "$error\n";
        }
    }
    
    // Show summary by zone
    $summaryStmt = $pdo->prepare('
        SELECT z.id, z.name, z.location, COUNT(zm.id) as member_count
        FROM zones z
        LEFT JOIN zone_members zm ON z.id = zm.zone_id AND zm.is_active = 1
        GROUP BY z.id
        ORDER BY z.name
    ');
    $summaryStmt->execute();
    $summary = $summaryStmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "\n📊 Zone Member Counts After Sync:\n";
    echo str_repeat("-", 60) . "\n";
    foreach ($summary as $zone) {
        echo "Zone: {$zone['name']} ({$zone['location']}) - Members: {$zone['member_count']}\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error during sync: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n✅ Done!\n";
?>
