<?php
require_once 'init.php';
require_once 'config.php';

require_login();

$group = $_GET['group'] ?? $_POST['group'] ?? '';
$services = file_exists(SERVICES_FILE) ? json_decode(file_get_contents(SERVICES_FILE), true) : [];
$error = '';

if (!$group || !isset($services[$group])) {
    http_response_code(400);
    die("Invalid request.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();
    unset($services[$group]);
    if (file_put_contents(SERVICES_FILE, json_encode($services, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES))) {
        header("Location: manage_services.php");
        exit;
    } else {
        $error = "Deletion failed.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Delete Group – <?= h($group) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf" content="<?= h(csrf_token()) ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/styles.css">
</head>
<body class="light-mode">
<div class="d-flex">
    <?php include 'sidebar.php'; ?>
    <div class="flex-fill ms-sidebar">
        <nav class="navbar navbar-expand-lg navbar-dark bg-primary px-4">
            <a class="navbar-brand text-white" href="manage_services.php">← Back</a>
        </nav>
        <div class="container py-4">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="manage_services.php">Manage Services</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Delete Group</li>
                </ol>
            </nav>
            <h4>Delete Group <strong><?= h($group) ?></strong></h4>
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= h($error) ?></div>
            <?php endif; ?>
            <form method="post">
                <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
                <button type="submit" class="btn btn-danger">🗑️ Delete Group</button>
            </form>
        </div>

        <footer class="text-center py-3 border-top mt-4">
            <small>Version <?= h(APP_VERSION) ?> — <?= date("Y-m-d") ?></small>
        </footer>
    </div>
</div>
</body>
</html>
