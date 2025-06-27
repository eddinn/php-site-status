<?php
require_once 'includes/functions.php';

$groups = load_data();
$group_id = $_GET['id'] ?? '';
$csrf_token = $_GET['csrf'] ?? '';

if (!verify_csrf_token($csrf_token)) {
    set_flash("Invalid CSRF token.", 'danger');
    header('Location: index.php');
    exit;
}

$updated_groups = [];
$found = false;

foreach ($groups as $group) {
    if ($group['id'] === $group_id) {
        $found = true;
        continue; // Skip this group (delete)
    }
    $updated_groups[] = $group;
}

if ($found) {
    save_data($updated_groups);
    set_flash("Group deleted successfully.");
} else {
    set_flash("Group not found.", 'warning');
}

header('Location: index.php');
exit;
