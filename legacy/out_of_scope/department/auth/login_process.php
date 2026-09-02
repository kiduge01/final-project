<?php
// Redirected to main unified login.
require_once __DIR__ . '/../includes/session.php';
header('Location: ' . appUrl('login'));
exit;
