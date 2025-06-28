<?php
require_once 'includes/functions.php';
require_once 'config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}

verify_csrf_header();

$data = load_data();
$group_ids = $_POST['group_ids'] ?? [];

if (!is_array($group_ids)) {
    http_response_code(400);
    exit;
}

$reordered = [];
foreach ($group_ids as $group_id) {
    foreach ($data as $group) {
        if ($group['id'] === $group_id) {
            $reordered[] = $group;
            break;
        }
    }
}

if (count($reordered) === count($data)) {
    save_data($reordered);
    echo json_encode(['success' => true]);
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Mismatch in group count']);
}
?>
