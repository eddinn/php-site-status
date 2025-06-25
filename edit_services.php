<?php
require_once 'init.php';

// Block access if not logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$services_file = 'services.json';
$backup_file = 'services_backup_' . date('Ymd_His') . '.json';
$success = '';
$error = '';
$json_contents = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf($_POST['csrf'] ?? '')) {
        $error = "Invalid request token.";
    } else {
        $raw_json = trim($_POST['services_json'] ?? '');

        // Validate JSON before saving
        $decoded = json_decode($raw_json, true);
        if ($decoded === null || !is_array($decoded)) {
            $error = "Invalid JSON format.";
        } else {
            // Backup old file
            if (!copy($services_file, $backup_file)) {
                $error = "Failed to create backup before saving.";
            } else {
                // Write new JSON
                if (file_put_contents($services_file, json_encode($decoded, JSON_PRETTY_PRINT)) !== false) {
                    $success = "Service configuration updated.";
                } else {
                    $error = "Failed to write new configuration.";
                }
            }
        }

        $json_contents = $raw_json;
    }
} else {
    $json_contents = file_exists($services_file) ? file_get_contents($services_file) : "{}";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Services – Homelab Dashboard</title>
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
        <span class="navbar-brand">Homelab Config Editor</span>
        <div class="ms-auto">
            <a href="logout.php" class="btn btn-sm btn-outline-light">Logout</a>
        </div>
    </nav>

    <div class="container my-4">
        <h3>Edit Service Groups</h3>
        <?php if ($success): ?>
            <div class="alert alert-success"><?= h($success) ?></div>
        <?php elseif ($error): ?>
            <div class="alert alert-danger"><?= h($error) ?></div>
        <?php endif; ?>

        <form method="post">
            <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
            <div class="mb-3">
                <label for="services_json" class="form-label">Service Configuration (JSON)</label>
                <textarea name="services_json" id="services_json" class="form-control"><?= h($json_contents) ?></textarea>
            </div>
            <button type="submit" class="btn btn-success">Save Changes</button>
            <a href="index.php" class="btn btn-secondary ms-2">Back to Dashboard</a>
        </form>
    </div>
</body>
</html>
