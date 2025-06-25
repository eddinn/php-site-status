<?php
require_once 'init.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user']) || $_SESSION['user'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

$csrf = $data['csrf'] ?? '';
$group = $data['group'] ?? '';
$order = $data['order'] ?? [];

if (!validate_csrf($csrf) || !$group || !is_array($order)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
    exit;
}

$services_file = 'services.json';
$services = file_exists($services_file) ? json_decode(file_get_contents($services_file), true) : [];

if (!isset($services[$group])) {
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'Group not found']);
    exit;
}

// Overwrite order of URLs in group
$services[$group] = $order;

if (file_put_contents($services_file, json_encode($services, JSON_PRETTY_PRINT)) === false) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Failed to save']);
    exit;
}

echo json_encode(['success' => true]);
