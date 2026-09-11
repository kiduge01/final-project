<?php

declare(strict_types=1);

/**
 * Minimal .env loader — no Composer/dependencies required.
 * Works on shared hosting (InfinityFree, cPanel) where you can't set
 * real server environment variables. Just edit the .env file directly.
 *
 * Loads KEY=VALUE pairs from the given file into getenv()/$_ENV,
 * without overwriting variables that are already set.
 */
function load_env(string $path): void
{
    if (!is_file($path) || !is_readable($path)) {
        return;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        return;
    }

    foreach ($lines as $line) {
        $line = trim($line);

        // Skip comments
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }

        if (!str_contains($line, '=')) {
            continue;
        }

        [$name, $value] = explode('=', $line, 2);
        $name  = trim($name);
        $value = trim($value);

        // Strip matching surrounding quotes
        if (strlen($value) >= 2) {
            $first = $value[0];
            $last  = $value[strlen($value) - 1];
            if (($first === '"' && $last === '"') || ($first === "'" && $last === "'")) {
                $value = substr($value, 1, -1);
            }
        }

        if ($name === '') {
            continue;
        }

        // Don't clobber real env vars if they happen to be set
        if (getenv($name) === false) {
            putenv("{$name}={$value}");
            $_ENV[$name] = $value;
        }
    }
}

/**
 * Detect the URL path that maps to $appRoot (the app's filesystem root
 * folder), by comparing the currently-running script's filesystem path
 * to its URL path. Works no matter which script triggers it (front
 * controller under public/, or a department/*.php page run directly),
 * as long as the URL structure mirrors the folder structure — true for
 * normal shared hosting (InfinityFree, cPanel) and most Apache setups.
 *
 * Returns the URL path with no trailing slash (e.g. '/htdocs', or ''
 * if the app is deployed at the domain root), or null if it can't be
 * determined (falls back to a caller-supplied default).
 */
function detect_app_root_url_path(string $appRoot): ?string
{
    $scriptFilename = $_SERVER['SCRIPT_FILENAME'] ?? null;
    $scriptName     = $_SERVER['SCRIPT_NAME'] ?? null;

    if (!$scriptFilename || !$scriptName) {
        return null;
    }

    $realAppRoot = realpath($appRoot);
    $realScript  = realpath($scriptFilename);

    if ($realAppRoot === false || $realScript === false) {
        return null;
    }

    $realAppRoot = str_replace('\\', '/', $realAppRoot);
    $realScript  = str_replace('\\', '/', $realScript);
    $scriptName  = str_replace('\\', '/', $scriptName);

    if (!str_starts_with($realScript, $realAppRoot)) {
        return null;
    }

    // Path of the current script relative to the app root,
    // e.g. '/department/dashboard.php' or '/public/index.php'
    $relative = substr($realScript, strlen($realAppRoot));

    if (!str_ends_with($scriptName, $relative)) {
        return null;
    }

    $rootUrlPath = substr($scriptName, 0, strlen($scriptName) - strlen($relative));

    return rtrim($rootUrlPath, '/');
}

/**
 * Read an env var with a default fallback, cast to a sensible type.
 */
function env(string $name, mixed $default = null): mixed
{
    $value = getenv($name);
    if ($value === false) {
        return $default;
    }

    return match (strtolower($value)) {
        'true', '(true)'   => true,
        'false', '(false)' => false,
        'null', '(null)'   => null,
        'empty', '(empty)' => '',
        default            => $value,
    };
}
