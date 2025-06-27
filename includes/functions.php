<?php
session_start();

/**
 * Load JSON data from file
 */
function load_data($path = 'data.json') {
    return file_exists($path) ? json_decode(file_get_contents($path), true) : [];
}

/**
 * Save JSON data to file
 */
function save_data($data, $path = 'data.json') {
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
 * Validate a service URL (must be http/https)
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
