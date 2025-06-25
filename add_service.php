<?php
require_once 'init.php';

if (!isset($_SESSION['user']) || $_SESSION['user'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$services_file = 'services.json';
$group = $_GET['group'] ?? null;
$error = '';
$success = '';

if (!$group) {
    http_response_code(400);
    die("Missing group name.");
}

$group = urldecode($group);
$services = file_exists($services_file) ? json_decode(file_get_contents($services_file), true) : [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf($_POST['csrf'] ?? '')) {
        $error = "Invalid CSRF token.";
    } else {
        $new_url = trim($_POST['url'] ?? '');

        if (!filter_var($new_url, FILTER_VALIDATE_URL)) {
            $error = "Invalid URL format.";
        } else {
            $services[$group][] = $new_url;
            if (file_put_contents($services_file, json_encode($services, JSON_PRETTY_PRINT)) === false) {
                $error = "Failed to write to services.json.";
            } else {
                header("Location: manage_services.php");
                exit;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Service – <?= h($group) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex justify-content-center align-items-center vh-100">
    <form method="post" class="p-4 bg-white shadow rounded" style="min-width: 400px;">
        <h4 class="mb-3">Add Service to <code><?= h($group) ?></code></h4>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= h($error) ?></div>
        <?php endif; ?>
        <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
        <div class="mb-3">
            <label for="url" class="form-label">Service URL</label>
            <input type="url" name="url" id="url" class="form-control" placeholder="http://example.com:port" required>
        </div>
        <div class="d-flex justify-content-between">
            <a href="manage_services.php" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-success">Add Service</button>
        </div>
    </form>
</body>
</html>
