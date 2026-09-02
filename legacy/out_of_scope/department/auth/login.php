<?php
// Department login is now unified. Use the main login page.
require_once __DIR__ . '/../includes/session.php';
header('Location: ' . appUrl('login'));
exit;
