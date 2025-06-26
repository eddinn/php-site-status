<?php
require_once 'init.php';
require_once 'config.php';

$services_file = SERVICES_FILE;
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
            } elseif (file_put_contents($services_file, json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)) === false) {
                $error = "Failed to write changes.";
            } else {
                $success = "Service configuration updated. Backup created: <code>$backup_file</code>";
                $json_contents = json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf" content="<?= h(csrf_token()) ?>">
    <link rel="stylesheet" href="assets/styles.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="assets/dashboard.js" defer></script>
    <meta charset="UTF-8">
    <title>Edit Services (Raw JSON)</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="assets/styles.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="<?= isset($_COOKIE['darkMode']) && $_COOKIE['darkMode'] === 'true' ? 'dark-mode' : 'light-mode' ?>">

<!-- Top Bar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary px-4">
    <span class="navbar-brand">Edit Service JSON</span>
    <div class="ms-auto">
        <a href="index.php" class="navbar-brand text-black btn btn-sm btn-outline-dark">← Back to Dashboard</a>
    </div>
</nav>

<div class="container my-4">
    <h3>Edit JSON Configuration</h3>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php elseif ($error): ?>
        <div class="alert alert-danger"><?= h($error) ?></div>
    <?php endif; ?>

    <?php if ($is_logged_in): ?>
    <form method="post">
        <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
        <div class="mb-3">
            <textarea name="services_json" class="form-control" rows="20"><?= h($json_contents) ?></textarea>
        </div>
        <button type="submit" class="btn btn-success">💾 Save Changes</button>
        <a href="index.php" class="btn btn-secondary ms-2">Cancel</a>
    </form>
    <?php else: ?>
        <div class="alert alert-warning">You are not logged in. This view is read-only.</div>
        <textarea class="form-control" readonly rows="20"><?= h($json_contents) ?></textarea>
        <a href="login.php" class="btn btn-primary mt-3">Login to Edit</a>
    <?php endif; ?>
</div>

<footer class="text-center py-3 border-top bg-light">
    <small>Version <?= h(APP_VERSION) ?> — <?= date("Y-m-d") ?></small>
</footer>

</body>
</html>
