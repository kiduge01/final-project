<?php
/**
 * Department Head Logout
 * 
 * Clears the department session and redirects to login page
 */

require_once __DIR__ . '/../includes/session.php';

destroyDepartmentSession();

// Redirect to login
header('Location: ' . appUrl('login'));
exit;
