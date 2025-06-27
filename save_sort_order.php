<?php
require_once 'includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Invalid request method']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
if (!isset($data['group_id'], $data['new_order']) || !is_array($data['new_order'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing or invalid data']);
    exit;
}

$group_id = $data['group_id'];
$new_urls = $data['new_order'];

$groups = load_data();
$found = false;

foreach ($groups as &$group) {
    if ($group['id'] === $group_id) {
        // Rebuild services in new order
        $current_services = $group['services'];
        $reordered = [];

        foreach ($new_urls as $url) {
            foreach ($current_services as $service) {
                if ($service['url'] === $url) {
                    $reordered[] = $service;
                    break;
                }
            }
        }

        // Only save if all expected services are found
        if (count($reordered) === count($current_services)) {
            $group['services'] = $reordered;
            $found = true;
        }

        break;
    }
}

if ($found) {
    save_data($groups);
    echo json_encode(['success' => true]);
} else {
    http_response_code(404);
    echo json_encode(['error' => 'Group not found or bad service list']);
}
