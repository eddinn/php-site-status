<?php
require_once 'init.php';
require_once 'config.php';

require_login();

$services = file_exists(SERVICES_FILE) ? json_decode(file_get_contents(SERVICES_FILE), true) : [];
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();
    $json_input = $_POST['services_json'] ?? '';

    $decoded = json_decode($json_input, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        if (file_put_contents(SERVICES_FILE, json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES))) {
            $services = $decoded;
            $success = "Services updated successfully.";
        } else {
            $error = "Failed to write to services file.";
        }
    } else {
        $error = "Invalid JSON: " . json_last_error_msg();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Services JSON – Homelab Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf" content="<?= h(csrf_token()) ?>">
    <link rel="stylesheet" href="assets/styles.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="<?= is_dark_mode() ? 'dark-mode' : 'light-mode' ?>">

<nav class="navbar navbar-expand-lg navbar-dark bg-primary px-4">
    <a class="navbar-brand" href="index.php">← Back to Dashboard</a>
</nav>

<div class="container my-5" style="max-width: 900px;">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="manage_services.php">Manage Services</a></li>
            <li class="breadcrumb-item active" aria-current="page">Edit JSON</li>
        </ol>
    </nav>

    <h3>Edit Raw JSON Configuration</h3>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= h($error) ?></div>
    <?php elseif ($success): ?>
        <div class="alert alert-success"><?= h($success) ?></div>
    <?php endif; ?>

    <form method="post">
        <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
        <div class="mb-3">
            <textarea name="services_json" rows="20" class="form-control" style="font-family: monospace;"><?= h(json_encode($services, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)) ?></textarea>
        </div>
        <div class="d-flex gap-2">
            <a href="manage_services.php" class="btn btn-secondary">← Back</a>
            <button type="submit" class="btn btn-success">💾 Save</button>
        </div>
    </form>
</div>

<footer class="text-center py-3 border-top bg-light mt-5">
    <small>Version <?= h(APP_VERSION) ?> — <?= date("Y-m-d") ?></small>
</footer>

</body>
</html>
