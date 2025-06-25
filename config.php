<?php
// Application version
define('APP_VERSION', '1.0.4');

// File locations
define('SERVICES_FILE', __DIR__ . '/services.json');
define('USERS_FILE', __DIR__ . '/../secure/users.json');

// Admin config
define('DEFAULT_USER', 'admin');
define('DEFAULT_PASSWORD', 'changeme');

// Timezone
date_default_timezone_set('UTC');
