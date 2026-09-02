<?php
// Find and display PHP error log
$errorLogPath = ini_get('error_log');
if (!$errorLogPath || $errorLogPath === 'syslog') {
    // Try default paths
    $possiblePaths = [
        'c:\\wamp64\\logs\\php_error.log',
        'c:\\php\\logs\\php_error.log',
        sys_get_temp_dir() . '\\php_errors.log',
        getenv('TEMP') . '\\php_errors.log',
    ];
    
    foreach ($possiblePaths as $path) {
        if (file_exists($path)) {
            $errorLogPath = $path;
            break;
        }
    }
}

echo "<h2>PHP Error Log</h2>";
if ($errorLogPath && file_exists($errorLogPath)) {
    echo "<p><strong>Error log path:</strong> " . htmlspecialchars($errorLogPath) . "</p>";
    echo "<p><a href='#bottom'>Jump to bottom</a></p>";
    echo "<pre style='background:#f0f0f0; padding:10px; border:1px solid #ccc; max-height:600px; overflow:auto; font-size:11px;'>";
    
    // Read last 100 lines
    $lines = file($errorLogPath);
    if ($lines) {
        $lastLines = array_slice($lines, -100);
        echo htmlspecialchars(implode('', $lastLines));
    }
    echo "</pre>";
    echo "<p id='bottom'><a href='#top'>Back to top</a></p>";
} else {
    echo "<p>Error log not found. Checked paths:</p>";
    echo "<pre>";
    echo $errorLogPath ?? "ini_get('error_log') returned: " . var_export(ini_get('error_log'), true);
    echo "\n";
    echo "Possible paths:\n";
    foreach ($possiblePaths ?? [] as $p) {
        echo "- " . $p . " (exists: " . (file_exists($p) ? "YES" : "NO") . ")\n";
    }
    echo "</pre>";
}
?>
