<?php
require_once 'init.php';

if (!isset($_SESSION['user']) || $_SESSION['user'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$services_file = 'services.json';
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $group = $_POST['group'] ?? null;
    $index = $_POST['index'] ?? null;

    if (!validate_csrf($_POST['csrf'] ?? '')) {
        $error = "Invalid CSRF token.";
    } elseif (!$group || !is_numeric($index)) {
        $error = "Invalid input.";
    } else {
        $group = urldecode($group);
        $index = (int)$index;
        $services = file_exists($services_file) ? json_decode(file_get_contents($services_file), true) : [];

        if (!isset($services[$group][$index])) {
            $error = "Service not found.";
        } else {
            unset($services[$group][$index]);
            $services[$group] = array_values($services[$group]);
            if (file_put_contents($services_file, json_encode($services, JSON_PRETTY_PRINT)) === false) {
                $error = "Failed to save changes.";
            } else {
                header("Location: manage_services.php");
                exit;
            }
        }
    }
} else {
    // Initial GET request, show confirmation form
    $group = $_GET['group'] ?? null;
    $index = $_GET['index'] ?? null;

    if (!$group || !is_numeric($index)) {
        http_response_code(400);
        die("Invalid request.");
    }

    $group = urldecode($group);
    $index = (int)$index;
    $services = file_exists($services_file) ? json_decode(file_get_contents($services_file), true) : [];

    if (!isset($services[$group][$index])) {
        http_response_code(404);
        die("Service not found.");
    }

    $service_url = $services[$group][$index];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Delete Service – Homelab Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex justify-content-center align-items-center vh-100">
    <div class="bg-white shadow rounded p-4" style="min-width: 400px;">
        <?php if ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
            <div class="alert alert-danger"><?= h($error) ?></div>
            <a href="manage_services.php" class="btn btn-secondary">Back</a>
        <?php else: ?>
            <h4 class="mb-3">Confirm Delete</h4>
            <p>Are you sure you want to delete this service from <code><?= h($group) ?></code>?</p>
            <p class="text-danger"><strong><?= h($service_url) ?></strong></p>

            <form method="post">
                <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
                <input type="hidden" name="group" value="<?= h($group) ?>">
                <input type="hidden" name="index" value="<?= h($index) ?>">
                <div class="d-flex justify-content-between">
                    <a href="manage_services.php" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-danger">Delete</button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
