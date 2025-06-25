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
$order = $data['order'] ?? [];

if (!validate_csrf($csrf) || !is_array($order)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
    exit;
}

$services_file = 'services.json';
if (!file_exists($services_file)) {
    echo json_encode(['success' => false, 'error' => 'Missing services file']);
    exit;
}

$existing = json_decode(file_get_contents($services_file), true);
$reordered = [];

// Preserve only existing groups in new order
foreach ($order as $group) {
    if (array_key_exists($group, $existing)) {
        $reordered[$group] = $existing[$group];
    }
}

// Append any leftover groups not in drag list
foreach ($existing as $group => $items) {
    if (!array_key_exists($group, $reordered)) {
        $reordered[$group] = $items;
    }
}

if (file_put_contents($services_file, json_encode($reordered, JSON_PRETTY_PRINT)) === false) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Failed to save file']);
    exit;
}

echo json_encode(['success' => true]);
