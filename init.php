<?php
session_start();

function is_logged_in(): bool {
    return isset($_SESSION['user']) && $_SESSION['user'] === 'admin';
}

function require_login() {
    if (!is_logged_in()) {
        $requested_url = $_SERVER['REQUEST_URI'] ?? '/index.php';
        $_SESSION['redirect_to'] = $requested_url;
        header('Location: login.php');
        exit;
    }
}

function csrf_token(): string {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function validate_csrf(string $token): bool {
    return isset($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $token);
}

function verify_csrf_token() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !validate_csrf($_POST['csrf_token'] ?? '')) {
        http_response_code(403);
        exit("Invalid CSRF token.");
    }
}

function h(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}
