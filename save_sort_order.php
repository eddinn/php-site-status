<?php
require_once 'includes/functions.php';
session_start();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$csrf_token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
if (!validate_csrf_token($csrf_token)) {
    http_response_code(403);
    echo json_encode(['error' => 'Invalid CSRF token']);
    exit;
}

$group_id = $_POST['group_id'] ?? '';
$service_order = $_POST['service_order'] ?? [];

if (!$group_id || !is_array($service_order)) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing or invalid parameters']);
    exit;
}

$data = load_data();
$group_found = false;

foreach ($data as &$group) {
    if ($group['id'] === $group_id) {
        $original = $group['services'];
        if (count($original) !== count($service_order)) {
            http_response_code(400);
            echo json_encode(['error' => 'Service count mismatch']);
            exit;
        }

        $reordered = [];
        foreach ($service_order as $index) {
            if (!isset($original[$index])) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid index in service_order']);
                exit;
            }
            $reordered[] = $original[$index];
        }

        $group['services'] = $reordered;
        $group_found = true;
        break;
    }
}

if (!$group_found) {
    http_response_code(404);
    echo json_encode(['error' => 'Group not found']);
    exit;
}

if (save_data($data)) {
    echo json_encode(['success' => true]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to save']);
}
