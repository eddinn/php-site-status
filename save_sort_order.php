<?php
require_once 'includes/functions.php';
require_once 'config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}

verify_csrf_header();

$group_id = $_POST['group_id'] ?? '';
$order = $_POST['service_order'] ?? [];

if (!$group_id || !is_array($order)) {
    http_response_code(400);
    exit;
}

$data = load_data();

foreach ($data as &$group) {
    if ($group['id'] === $group_id) {
        $reordered = [];
        foreach ($order as $index) {
            $index = (int) $index;
            if (isset($group['services'][$index])) {
                $reordered[] = $group['services'][$index];
            }
        }
        if (count($reordered) === count($group['services'])) {
            $group['services'] = $reordered;
            save_data($data);
            echo json_encode(['success' => true]);
            exit;
        }
    }
}

http_response_code(400);
echo json_encode(['error' => 'Invalid input']);
?>
