<?php
require_once 'init.php';
require_once 'config.php';

require_login();
verify_csrf_token();

$services = file_exists(SERVICES_FILE) ? json_decode(file_get_contents(SERVICES_FILE), true) : [];

$type = $_POST['type'] ?? '';
$group = $_POST['group'] ?? '';
$index = isset($_POST['index']) ? intval($_POST['index']) : null;

$modified = false;

if ($type === 'group' && isset($services[$group])) {
    unset($services[$group]);
    $modified = true;
} elseif ($type === 'service' && isset($services[$group][$index])) {
    array_splice($services[$group], $index, 1);
    $modified = true;
}

if ($modified) {
    file_put_contents(SERVICES_FILE, json_encode($services, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
}

header("Location: manage_services.php");
exit;
