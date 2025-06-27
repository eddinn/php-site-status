<?php
session_start();
require_once __DIR__ . '/../config.php';  // Fixed path

/**
 * Load JSON data from configured file
 */
function load_data($path = DATA_FILE) {
    return file_exists($path) ? json_decode(file_get_contents($path), true) : [];
}

/**
 * Save JSON data to configured file
 */
function save_data($data, $path = DATA_FILE) {
    file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
}

/**
 * Generate and store CSRF token
 */
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validate CSRF token
 */
function verify_csrf_token($token) {
    return hash_equals($_SESSION['csrf_token'] ?? '', $token);
}

/**
 * Safely output HTML-escaped text
 */
function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Validate a service URL
 */
function is_valid_url($url) {
    return filter_var($url, FILTER_VALIDATE_URL) &&
           preg_match('#^https?://#i', $url);
}

/**
 * Check service URL status and fetch title
 */
function check_url($url) {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 5,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_HEADER => false,
        CURLOPT_NOBODY => false,
    ]);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $title = "No Title Found";

    if ($http_code === 200 && preg_match("/<title>(.*?)<\/title>/i", $response, $matches)) {
        $title = $matches[1];
    }

    curl_close($ch);
    return [$http_code === 200, $title];
}

/**
 * Set flash message
 */
function set_flash($msg, $type = 'success') {
    $_SESSION['flash'] = ['msg' => $msg, 'type' => $type];
}

/**
 * Display and clear flash message
 */
function show_flash() {
    if (!empty($_SESSION['flash'])) {
        $msg = $_SESSION['flash']['msg'];
        $type = $_SESSION['flash']['type'];
        unset($_SESSION['flash']);
        return "<div class=\"alert alert-$type alert-dismissible fade show\" role=\"alert\">"
             . e($msg)
             . "<button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"alert\" aria-label=\"Close\"></button>"
             . "</div>";
    }
    return '';
}

/**
 * Return version info: version string, short Git hash, and today's date
 */
function get_version_info() {
    $version = file_exists(VERSION_FILE) ? trim(file_get_contents(VERSION_FILE)) : '0.0.1';
    $git_hash = trim(shell_exec('git rev-parse --short HEAD') ?? 'unknown');
    $date_str = date('d/m/Y');
    return "Version: " . e($version) . " " . e($git_hash) . " — " . e($date_str);
}
