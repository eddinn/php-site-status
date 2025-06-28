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

$group_ids = $_POST['group_ids'] ?? null;
if (!is_array($group_ids)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid payload']);
    exit;
}

$data = load_data();
$reordered = [];

foreach ($group_ids as $id) {
    foreach ($data as $group) {
        if ($group['id'] === $id) {
            $reordered[] = $group;
            break;
        }
    }
}

if (count($reordered) !== count($data)) {
    http_response_code(400);
    echo json_encode(['error' => 'Group ID mismatch']);
    exit;
}

if (save_data($reordered)) {
    echo json_encode(['success' => true]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to save data']);
}
