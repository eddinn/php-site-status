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

// Application version (dynamic from Git)
define('APP_VERSION', get_app_version());

function get_app_version(): string {
    $base = '1.0';
    $count = 0;
    $commit = 'unknown';

    // Only attempt Git versioning if inside a Git repo
    if (is_dir(dirname(ROOT_DIR) . '/.git')) {
        $count = trim(@shell_exec('git rev-list --count HEAD')) ?: 0;
        $commit = trim(@shell_exec('git rev-parse --short HEAD')) ?: 'unknown';
    }

    return "{$base}." . intval($count) . " ({$commit})";
}
