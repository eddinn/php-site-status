<?php
require_once 'init.php';

if (!is_logged_in()) {
    header("Location: login.php");
    exit;
}

$services_file = 'services.json';
$services = file_exists($services_file) ? json_decode(file_get_contents($services_file), true) : [];

$group = $_GET['group'] ?? '';
$index = $_GET['index'] ?? null;

if (!isset($services[$group]) || !is_numeric($index) || !isset($services[$group][$index])) {
    http_response_code(404);
    exit("Service not found.");
}

$original_url = $services[$group][$index];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();

    $new_url = trim($_POST['url'] ?? '');
    if (!filter_var($new_url, FILTER_VALIDATE_URL)) {
        $error = "Invalid URL";
    } else {
        $services[$group][$index] = $new_url;
        file_put_contents($services_file, json_encode($services, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        header("Location: index.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Service</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="light-mode">
<div class="container py-5">
    <h2>Edit Service</h2>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= h($error) ?></div>
    <?php endif; ?>

    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">

        <div class="mb-3">
            <label for="url" class="form-label">Service URL</label>
            <input type="url" name="url" id="url" class="form-control" required value="<?= h($original_url) ?>">
        </div>

        <button type="submit" class="btn btn-primary">Save</button>
        <a href="index.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>
</body>
</html>
