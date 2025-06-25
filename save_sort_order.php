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
$group = $data['group'] ?? '';
$order = $data['order'] ?? [];

if (!validate_csrf($csrf)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
    exit;
}

if (!$group || !is_array($order)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid group or order format']);
    exit;
}

$services = file_exists(SERVICES_FILE) ? json_decode(file_get_contents(SERVICES_FILE), true) : [];

if (!isset($services[$group])) {
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'Group not found']);
    exit;
}

// Overwrite service order within the group
$services[$group] = array_values($order);  // Reindex array for consistency

if (file_put_contents(SERVICES_FILE, json_encode($services, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)) === false) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Failed to write services file']);
    exit;
}

echo json_encode(['success' => true]);
