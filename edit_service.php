<?php
require_once 'init.php';
require_once 'config.php';

require_login();

$group = $_GET['group'] ?? '';
$index = $_GET['index'] ?? '';
$services = file_exists(SERVICES_FILE) ? json_decode(file_get_contents(SERVICES_FILE), true) : [];
$error = '';
$success = '';

if (!isset($services[$group][$index])) {
    http_response_code(404);
    echo "<h1>404 – Service Not Found</h1>";
    exit;
}

$current_url = $services[$group][$index];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf($_POST['csrf_token'] ?? '')) {
        $error = "Invalid request token.";
    } else {
        $new_url = trim($_POST['url'] ?? '');

        if (!filter_var($new_url, FILTER_VALIDATE_URL)) {
            $error = "Invalid URL format.";
        } else {
            $services[$group][$index] = $new_url;
            file_put_contents(SERVICES_FILE, json_encode($services, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            $success = "Service updated successfully.";
            $current_url = $new_url;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Service – <?= h($group) ?></title>
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
            <li class="breadcrumb-item active" aria-current="page">Edit Service</li>
        </ol>
    </nav>

    <h3>Edit URL in <strong><?= h($group) ?></strong></h3>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= h($error) ?></div>
    <?php elseif ($success): ?>
        <div class="alert alert-success"><?= h($success) ?></div>
    <?php endif; ?>

    <form method="post">
        <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
        <div class="mb-3">
            <label for="url" class="form-label">Service URL</label>
            <input type="url" id="url" name="url" value="<?= h($current_url) ?>" class="form-control" required>
        </div>
        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-success">💾 Save</button>
            <a href="manage_services.php" class="btn btn-secondary">← Back</a>
        </div>
    </form>
</div>

<footer class="text-center py-3 border-top bg-light mt-5">
    <small>Version <?= h(APP_VERSION) ?> — <?= date("Y-m-d") ?></small>
</footer>

</body>
</html>
