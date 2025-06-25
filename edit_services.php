<?php
require_once 'init.php';

$users_file = __DIR__ . '/../secure/users.json';
$services_file = 'services.json';
$backup_file = 'services_backup_' . date('Ymd_His') . '.json';

$is_logged_in = isset($_SESSION['user']) && $_SESSION['user'] === 'admin';
$error = '';
$success = '';
$json_contents = file_exists($services_file) ? file_get_contents($services_file) : "{}";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $is_logged_in) {
    if (!validate_csrf($_POST['csrf'] ?? '')) {
        $error = "Invalid request token.";
    } else {
        $raw_json = trim($_POST['services_json'] ?? '');
        $decoded = json_decode($raw_json, true);

        if ($decoded === null || !is_array($decoded)) {
            $error = "Invalid JSON format.";
        } else {
            if (!copy($services_file, $backup_file)) {
                $error = "Failed to create backup.";
            } elseif (file_put_contents($services_file, json_encode($decoded, JSON_PRETTY_PRINT)) === false) {
                $error = "Failed to write changes.";
            } else {
                $success = "Service configuration updated.";
                $json_contents = json_encode($decoded, JSON_PRETTY_PRINT);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Service Config – Homelab Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        textarea {
            font-family: monospace;
            font-size: 14px;
            min-height: 400px;
        }
    </style>
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark px-4">
        <span class="navbar-brand">Homelab Config</span>
        <div class="ms-auto">
            <?php if ($is_logged_in): ?>
                <a href="logout.php" class="btn btn-sm btn-outline-light">Logout</a>
            <?php else: ?>
                <a href="login.php" class="btn btn-sm btn-outline-light">Login</a>
            <?php endif; ?>
        </div>
    </nav>

    <div class="container my-4">
        <h3>Service Groups JSON</h3>

        <?php if ($success): ?>
            <div class="alert alert-success"><?= h($success) ?></div>
        <?php elseif ($error): ?>
            <div class="alert alert-danger"><?= h($error) ?></div>
        <?php endif; ?>

        <?php if ($is_logged_in): ?>
        <form method="post">
            <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
            <div class="mb-3">
                <textarea name="services_json" class="form-control"><?= h($json_contents) ?></textarea>
            </div>
            <button type="submit" class="btn btn-success">Save Changes</button>
            <a href="index.php" class="btn btn-secondary ms-2">Back to Dashboard</a>
        </form>
        <?php else: ?>
            <div class="alert alert-warning">You are not logged in. This view is read-only.</div>
            <textarea class="form-control" readonly><?= h($json_contents) ?></textarea>
            <a href="login.php" class="btn btn-primary mt-3">Login to Edit</a>
        <?php endif; ?>
    </div>
</body>
</html>
