<?php
// Force secure session cookie params
$secure = !empty($_SERVER['HTTPS']) || $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https';
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '',
    'secure' => $secure,
    'httponly' => true,
    'samesite' => 'Strict'
]);

session_start();

// Optional: force HTTPS if behind a proxy like nginx
if (!$secure) {
    header("Content-Security-Policy: default-src 'self';");
    header("X-Content-Type-Options: nosniff");
    header("X-Frame-Options: DENY");
    header("X-XSS-Protection: 1; mode=block");
}

// Helper: escape HTML
function h($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

// Helper: generate a CSRF token
function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Helper: validate CSRF token
function validate_csrf($token) {
    return hash_equals($_SESSION['csrf_token'] ?? '', $token ?? '');
}
