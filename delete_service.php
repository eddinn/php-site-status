<?php
require_once 'init.php';
require_once 'config.php';

require_login();

$error = '';
$services = file_exists(SERVICES_FILE) ? json_decode(file_get_contents(SERVICES_FILE), true) : [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf($_POST['csrf'] ?? '')) {
        $error = "Invalid CSRF token.";
    } else {
        $group = urldecode($_POST['group'] ?? '');
        $index = intval($_POST['index'] ?? -1);

        if (!isset($services[$group][$index])) {
            $error = "Service not found.";
        } else {
            array_splice($services[$group], $index, 1);
            if (file_put_contents(SERVICES_FILE, json_encode($services, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES))) {
                header("Location: manage_services.php");
                exit;
            } else {
                $error = "Failed to save changes.";
            }
        }
    }
} else {
    $group = $_GET['group'] ?? '';
    $index = intval($_GET['index'] ?? -1);

    if (!isset($services[$group][$index])) {
        http_response_code(404);
        exit("Service not found.");
    }
    $service_url = $services[$group][$index];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Delete Service – Homelab Dashboard</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="assets/styles.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="light-mode">
<nav class="navbar navbar-expand-lg navbar-dark bg-primary px-4">
  <a class="navbar-brand" href="index.php">Homelab Dashboard</a>
</nav>
<div class="container my-5" style="max-width:700px">
  <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
      <li class="breadcrumb-item"><a href="manage_services.php">Manage</a></li>
      <li class="breadcrumb-item active">Delete Service</li>
    </ol>
  </nav>
  <?php if ($error): ?>
    <div class="alert alert-danger"><?= h($error) ?></div>
    <a href="manage_services.php" class="btn btn-secondary">← Back</a>
  <?php else: ?>
    <h4 class="mb-3">Confirm Deletion</h4>
    <p>Delete this service from <code><?= h($group) ?></code>?</p>
    <pre><code><?= h($service_url) ?></code></pre>
    <form method="post">
      <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
      <input type="hidden" name="group" value="<?= h($group) ?>">
      <input type="hidden" name="index" value="<?= h($index) ?>">
      <div class="d-flex gap-2">
        <a href="manage_services.php" class="btn btn-secondary">Cancel</a>
        <button class="btn btn-danger">Delete</button>
      </div>
    </form>
  <?php endif; ?>
</div>
<footer class="text-center py-3 border-top bg-light mt-5">
  <small>Version <?= h(APP_VERSION) ?> — <?= date("Y-m-d") ?></small>
</footer>
</body>
</html>
