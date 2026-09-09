<?php
// This file tests if .htaccess rewriting is working
echo "<h1>Rewrite Test</h1>";
echo "<p>If you accessed this via http://localhost/htdocs/test-rewrite (without .php), then .htaccess rewriting IS working.</p>";
echo "<p>If you had to add .php to access this, then .htaccess rewriting is NOT working.</p>";
echo "<hr>";
echo "<pre>";
echo "REQUEST_URI: " . $_SERVER['REQUEST_URI'] . "\n";
echo "SCRIPT_NAME: " . $_SERVER['SCRIPT_NAME'] . "\n";
echo "SCRIPT_FILENAME: " . $_SERVER['SCRIPT_FILENAME'] . "\n";
echo "SERVER_NAME: " . $_SERVER['SERVER_NAME'] . "\n";
echo "HTTP_HOST: " . $_SERVER['HTTP_HOST'] . "\n";
echo "</pre>";
