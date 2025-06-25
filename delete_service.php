<?php
require_once 'init.php';
require_once 'config.php';

require_login();

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
        $services = file_exists(SERVICES_FILE) ? json_decode(file_get_contents(SERVICES_FILE), true) : [];

        if (!isset($services[$group][$index])) {
            $error = "Service not found.";
        } else {
            unset($services[$group][$index]);
            $services[$group] = array_values($services[$group]); // Reindex
            if (file_put_contents(SERVICES_FILE, json_encode($services, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)) === false) {
                $error = "Failed to save changes.";
            } else {
                header("Location: manage_services.php");
                exit;
            }
        }
    }
} else {
    $group = $_GET['group'] ?? null;
    $index = $_GET['index'] ?? null;

    if (!$group || !is_numeric($index)) {
        http_response_code(400);
        die("Invalid request.");
    }

    $group = urldecode($group);
    $index = (int)$index;
    $services = file_exists(SERVICES_FILE) ? json_decode(file_get_contents(SERVICES_FILE), true) : [];

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
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf" content="<?= h(csrf_token()) ?>">
    <link rel="stylesheet" href="assets/styles.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="light-mode d-flex justify-content-center align-items-center vh-100">

<div class="card shadow p-4" style="min-width: 400px;">
    <?php if ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
        <h4 class="mb-3">Error</h4>
        <div class="alert alert-danger"><?= h($error) ?></div>
        <a href="manage_services.php" class="btn btn-secondary">Back</a>
    <?php else: ?>
        <h4 class="mb-3 text-danger">Confirm Deletion</h4>
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

    <footer class="text-center mt-4 small text-muted">
        Version <?= h(APP_VERSION) ?> — <?= date("Y-m-d") ?>
    </footer>
</div>

</body>
</html>
