<?php
require_once 'init.php';
require_once 'config.php';

require_login();

$services = file_exists(SERVICES_FILE)
    ? json_decode(file_get_contents(SERVICES_FILE), true)
    : [];

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf($_POST['csrf'] ?? '')) {
        $error = "Invalid request token.";
    } else {
        $raw_json = trim($_POST['services_json'] ?? '');
        $decoded = json_decode($raw_json, true);

        if (!is_array($decoded)) {
            $error = "Invalid JSON format.";
        } else {
            // Create a timestamped backup before saving
            $timestamp = date('Ymd_His');
            $backup_path = dirname(SERVICES_FILE) . '/services_backup_' . $timestamp . '.json';
            if (!copy(SERVICES_FILE, $backup_path)) {
                $error = "Failed to create backup.";
            } elseif (file_put_contents(SERVICES_FILE, json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)) === false) {
                $error = "Failed to write updated services.";
            } else {
                $services = $decoded;
                $success = "Service configuration updated successfully.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Services (Raw JSON)</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="assets/styles.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="light-mode">

<nav class="navbar navbar-expand-lg navbar-dark bg-primary px-4">
    <span class="navbar-brand">Edit JSON</span>
    <div class="ms-auto">
        <a href="index.php" class="btn btn-sm btn-outline-light">← Back</a>
    </div>
</nav>

<div class="container my-5">
    <h3>Edit Service Groups JSON</h3>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= h($error) ?></div>
    <?php elseif ($success): ?>
        <div class="alert alert-success"><?= h($success) ?></div>
    <?php endif; ?>

    <form method="post">
        <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
        <div class="mb-3">
            <textarea name="services_json" class="form-control" rows="20"><?= h(json_encode($services, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)) ?></textarea>
        </div>
        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-success">💾 Save</button>
            <a href="index.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<footer class="text-center py-3 border-top bg-light mt-5">
    <small>Version <?= h(APP_VERSION) ?> — <?= date("Y-m-d") ?></small>
</footer>

</body>
</html>
