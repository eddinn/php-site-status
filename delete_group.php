<?php
require_once 'init.php';
require_once 'config.php';

require_login();

$error = '';
$group = $_GET['group'] ?? ($_POST['group'] ?? null);

if (!$group) {
    http_response_code(400);
    die("Missing group name.");
}

$group = urldecode($group);
$services = file_exists(SERVICES_FILE) ? json_decode(file_get_contents(SERVICES_FILE), true) : [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf($_POST['csrf'] ?? '')) {
        $error = "Invalid CSRF token.";
    } elseif (!isset($services[$group])) {
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Delete Group – Homelab Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="assets/styles.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="light-mode">

<nav class="navbar navbar-expand-lg navbar-dark bg-primary px-4">
    <a class="navbar-brand" href="index.php">Homelab Dashboard</a>
</nav>

<div class="container my-5" style="max-width: 700px;">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="manage_services.php">Manage Services</a></li>
            <li class="breadcrumb-item active" aria-current="page">Delete Group</li>
        </ol>
    </nav>

    <?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && $error): ?>
        <div class="alert alert-danger"><?= h($error) ?></div>
        <a href="manage_services.php" class="btn btn-secondary">← Back</a>
    <?php else: ?>
        <h4 class="mb-3">Delete Entire Group</h4>
        <p>Are you sure you want to delete the entire group <code><?= h($group) ?></code> and all its services?</p>

        <form method="post">
            <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
            <input type="hidden" name="group" value="<?= h($group) ?>">
            <div class="d-flex gap-2">
                <a href="manage_services.php" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-danger">Delete Group</button>
            </div>
        </form>
    <?php endif; ?>
</div>

<footer class="text-center py-3 border-top bg-light mt-5">
    <small>Version <?= h(APP_VERSION) ?> — <?= date("Y-m-d") ?></small>
</footer>

</body>
</html>
