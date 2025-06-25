<?php
// Timezone
date_default_timezone_set('UTC');

// Directories
define('ROOT_DIR', __DIR__);
define('SECURE_DIR', dirname(__DIR__) . '/secure');

// File locations
define('SERVICES_FILE', ROOT_DIR . '/services.json');
define('USERS_FILE', SECURE_DIR . '/users.json');

// Admin config
define('DEFAULT_USER', 'admin');
define('DEFAULT_PASSWORD', 'changeme');

// Ensure required directories exist (optional safety)
if (!is_dir(SECURE_DIR)) {
    mkdir(SECURE_DIR, 0700, true);
}

// Application version (dynamic from Git if available)
define('APP_VERSION', get_app_version());

function get_app_version(): string {
    $base = '1.0';
    $count = 0;
    $commit = 'unknown';

    $gitDir = ROOT_DIR . '/.git';
    $headFile = $gitDir . '/HEAD';

    if (is_dir($gitDir) && file_exists($headFile)) {
        $ref = trim(file_get_contents($headFile));
        if (str_starts_with($ref, 'ref:')) {
            $refPath = $gitDir . '/' . substr($ref, 5);
            if (file_exists($refPath)) {
                $commit = trim(file_get_contents($refPath));
            }
        } else {
            $commit = $ref; // Detached HEAD
        }

        // Shorten commit hash if long
        if (preg_match('/^[a-f0-9]{40}$/', $commit)) {
            $commit = substr($commit, 0, 7);
        }

        // Count commits manually if log exists
        $logPath = $gitDir . '/logs/HEAD';
        if (file_exists($logPath)) {
            $lines = file($logPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            $count = count($lines);
        }
    }

    return "{$base}." . intval($count) . " ({$commit})";
}
