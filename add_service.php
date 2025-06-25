<?php
require_once 'init.php';
require_once 'config.php';

require_login();

$group = $_GET['group'] ?? null;
$error = '';
$success = '';

if (!$group) {
    http_response_code(400);
    die("Missing group name.");
}

$group = urldecode($group);
$services = file_exists(SERVICES_FILE) ? json_decode(file_get_contents(SERVICES_FILE), true) : [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf($_POST['csrf'] ?? '')) {
        $error = "Invalid CSRF token.";
    } else {
        $new_url = trim($_POST['url'] ?? '');

        if (!filter_var($new_url, FILTER_VALIDATE_URL)) {
            $error = "Invalid URL format.";
        } else {
            $services[$group][] = $new_url;
            if (file_put_contents(SERVICES_FILE, json_encode($services, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)) === false) {
                $error = "Failed to write to services file.";
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
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf" content="<?= h(csrf_token()) ?>">
    <link rel="stylesheet" href="assets/styles.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="light-mode d-flex justify-content-center align-items-center vh-100">

<div class="card shadow p-4" style="min-width: 400px;">
    <h4 class="mb-3 text-center">➕ Add Service to <code><?= h($group) ?></code></h4>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= h($error) ?></div>
    <?php endif; ?>

    <form method="post">
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

    <footer class="text-center mt-4 small text-muted">
        Version <?= h(APP_VERSION) ?> — <?= date("Y-m-d") ?>
    </footer>
</div>

</body>
</html>
