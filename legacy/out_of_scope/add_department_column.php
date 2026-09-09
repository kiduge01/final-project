<?php
$pdo = new PDO('mysql:host=localhost;dbname=church_cms2', 'root', '');
try {
    $pdo->exec('ALTER TABLE finance_entries ADD COLUMN department_id BIGINT UNSIGNED DEFAULT NULL AFTER updated_at');
    echo 'Column added successfully';
} catch (Exception $e) {
    if (strpos($e->getMessage(), 'Duplicate') !== false || strpos($e->getMessage(), 'already exists') !== false) {
        echo 'Column already exists';
    } else {
        echo 'Error: ' . $e->getMessage();
    }
}
