<?php
/**
 * Database Connection for Department Subsystem
 * 
 * Reuses the existing PDO connection from the main application.
 * This ensures both the main system and department system use the same database.
 */

// Load the main app configuration
$config = require __DIR__ . '/../../app/config.php';
require_once __DIR__ . '/../../app/core/Database.php';

// Get PDO connection from existing Database class
$pdo = \App\Core\Database::connection($config);

// Verify connection is working
if (!$pdo) {
    die('Database connection failed');
}

// Return PDO connection for use in department pages
return $pdo;
