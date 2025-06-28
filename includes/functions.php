<?php
require_once __DIR__ . '/../vendor/autoload.php';

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

function load_data(): array {
    $path = realpath(__DIR__ . '/../data.json');
    if (!file_exists($path)) return [];
    return json_decode(file_get_contents($path), true) ?? [];
}

function save_data(array $data): bool {
    $path = realpath(__DIR__ . '/../data.json');
    if (!$path) $path = __DIR__ . '/../data.json';
    return file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT)) !== false;
}

function e($text): string {
    return htmlspecialchars((string)$text, ENT_QUOTES, 'UTF-8');
}

function generate_csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validate_csrf_token(string $token): bool {
    return hash_equals($_SESSION['csrf_token'] ?? '', $token);
}

function check_url(string $url): bool {
    static $client = null;
    if (!$client) {
        $client = new Client([
            'timeout' => 10,
            'verify' => false,
            'allow_redirects' => true,
        ]);
    }

    try {
        $response = $client->request('GET', $url, ['http_errors' => false]);
        $status = $response->getStatusCode();
        return $status >= 200 && $status < 400;
    } catch (GuzzleException $e) {
        return false;
    }
}

function flash(string $msg, string $type = 'info'): void {
    $_SESSION['flash'][] = ['msg' => $msg, 'type' => $type];
}

function show_flash(): void {
    if (empty($_SESSION['flash'])) return;
    foreach ($_SESSION['flash'] as $f) {
        echo "<div class=\"alert alert-{$f['type']} alert-dismissible fade show\" role=\"alert\">";
        echo e($f['msg']);
        echo "<button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"alert\" aria-label=\"Close\"></button>";
        echo "</div>";
    }
    unset($_SESSION['flash']);
}

function get_version_info(): string {
    $git_head = trim(@shell_exec('git rev-parse --short HEAD')) ?: 'unknown';
    $version = '1.0.1';
    $date = date('d/m/Y');
    return "Version: $version $git_head — $date";
}
