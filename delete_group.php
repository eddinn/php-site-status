<?php
require_once 'init.php';

if (!isset($_SESSION['user']) || $_SESSION['user'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$services_file = 'services.json';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $group = $_POST['group'] ?? null;

    if (!validate_csrf($_POST['csrf'] ?? '')) {
        $error = "Invalid CSRF token.";
    } elseif (!$group) {
        $error = "Missing group name.";
    } else {
        $group = urldecode($group);
        $services = file_exists($services_file) ? json_decode(file_get_contents($services_file), true) : [];

        if (!isset($services[$group])) {
            $error = "Group not found.";
        } else {
            unset($services[$group]);
            if (file_put_contents($services_file, json_encode($services, JSON_PRETTY_PRINT)) === false) {
                $error = "Failed to delete group.";
            } else {
                header("Location: manage_services.php");
                exit;
            }
        }
    }
} else {
    $group = $_GET['group'] ?? null;

    if (!$group) {
        http_response_code(400);
        die("Missing group name.");
    }

    $group = urldecode($group);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Delete Group – Homelab Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex justify-content-center align-items-center vh-100">
    <div class="bg-white shadow rounded p-4" style="min-width: 400px;">
        <?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && $error): ?>
            <div class="alert alert-danger"><?= h($error) ?></div>
            <a href="manage_services.php" class="btn btn-secondary">Back</a>
        <?php else: ?>
            <h4 class="mb-3">Delete Entire Group</h4>
            <p>Are you sure you want to delete the entire group <code><?= h($group) ?></code> and all its services?</p>

            <form method="post">
                <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
                <input type="hidden" name="group" value="<?= h($group) ?>">
                <div class="d-flex justify-content-between">
                    <a href="manage_services.php" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-danger">Delete Group</button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
