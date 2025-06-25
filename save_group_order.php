<?php
require_once 'init.php';
require_once 'config.php';

header('Content-Type: application/json');
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$csrf = $data['csrf'] ?? '';
$order = $data['order'] ?? [];

if (!validate_csrf($csrf)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
    exit;
}

if (!is_array($order)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid order format']);
    exit;
}

if (!file_exists(SERVICES_FILE)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Missing services file']);
    exit;
}

$existing = json_decode(file_get_contents(SERVICES_FILE), true);
if (!is_array($existing)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Corrupt services data']);
    exit;
}

$reordered = [];

// Preserve known groups in submitted order
foreach ($order as $group) {
    if (isset($existing[$group])) {
        $reordered[$group] = $existing[$group];
    }
}

// Append unlisted groups
foreach ($existing as $group => $items) {
    if (!isset($reordered[$group])) {
        $reordered[$group] = $items;
    }
}

if (file_put_contents(SERVICES_FILE, json_encode($reordered, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)) === false) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Failed to write services file']);
    exit;
}

echo json_encode(['success' => true]);
