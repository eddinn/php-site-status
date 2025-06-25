<?php
require_once 'init.php';
require_once 'config.php';

require_login();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $group = $_POST['group'] ?? null;

    if (!validate_csrf($_POST['csrf'] ?? '')) {
        $error = "Invalid CSRF token.";
    } elseif (!$group) {
        $error = "Missing group name.";
    } else {
        $group = urldecode($group);
        $services = file_exists(SERVICES_FILE) ? json_decode(file_get_contents(SERVICES_FILE), true) : [];

        if (!isset($services[$group])) {
            $error = "Group not found.";
        } else {
            unset($services[$group]);
            if (file_put_contents(SERVICES_FILE, json_encode($services, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)) === false) {
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
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf" content="<?= h(csrf_token()) ?>">
    <link rel="stylesheet" href="assets/styles.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="light-mode d-flex justify-content-center align-items-center vh-100">
    <div class="card shadow p-4" style="min-width: 400px;">
        <?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && $error): ?>
            <h4 class="mb-3 text-danger">Error</h4>
            <div class="alert alert-danger"><?= h($error) ?></div>
            <a href="manage_services.php" class="btn btn-secondary">Back</a>
        <?php else: ?>
            <h4 class="mb-3 text-danger">Confirm Group Deletion</h4>
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

        <footer class="text-center mt-4 small text-muted">
            Version <?= h(APP_VERSION) ?> — <?= date("Y-m-d") ?>
        </footer>
    </div>
</body>
</html>
