<?php
// Site Identity
define('SITE_NAME', 'Homelab Dashboard');
define('DEFAULT_TITLE', 'Dashboard');

// File paths
define('DATA_FILE', __DIR__ . '/data.json');
define('VERSION_FILE', __DIR__ . '/version.txt');

// Feature toggles
define('ENABLE_DARK_MODE', true);
define('ENABLE_DRAG_DROP', true);
define('ENABLE_LOGIN', false); // for future use

// CSP and security headers
define('CSP_HEADER', "default-src 'self'; script-src 'self' https://cdn.jsdelivr.net; style-src 'self' https://cdn.jsdelivr.net; connect-src 'self';");

header("Content-Security-Policy: " . CSP_HEADER);
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
