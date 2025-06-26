<?php
require_once 'init.php';
require_once 'config.php';

$services = file_exists(SERVICES_FILE) ? json_decode(file_get_contents(SERVICES_FILE), true) : [];
$is_logged_in = isset($_SESSION['user']) && $_SESSION['user'] === 'admin';
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
    <title>Service Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf" content="<?= h(csrf_token()) ?>">
    <link rel="stylesheet" href="assets/styles.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <script src="assets/dashboard.js" defer></script>
</head>
<body class="light-mode" data-admin="<?= $is_logged_in ? '1' : '0' ?>">

<div class="d-flex">
    <!-- Sidebar -->
    <div class="bg-dark text-white p-3 sidebar">
        <h4 class="mb-4">Menu</h4>
        <ul class="nav flex-column gap-2">
            <li class="nav-item"><a class="nav-link text-white" href="index.php">Dashboard</a></li>
            <?php if ($is_logged_in): ?>
                <li class="nav-item"><a class="nav-link text-white" href="manage_services.php">Manage Services</a></li>
                <li class="nav-item"><a class="nav-link text-white" href="edit_services.php">Edit JSON</a></li>
                <li class="nav-item"><a class="nav-link text-white" href="logout.php">Logout</a></li>
            <?php else: ?>
                <li class="nav-item"><a class="nav-link text-white" href="login.php">Login</a></li>
            <?php endif; ?>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="flex-fill">
        <nav class="navbar navbar-expand-lg navbar-dark bg-primary px-4">
            <span class="navbar-brand">Homelab Services</span>
            <div class="ms-auto d-flex gap-2">
                <button id="toggle-dark" class="btn btn-sm btn-outline-light">🌓 Dark Mode</button>
                <button id="toggle-offline" class="btn btn-sm btn-outline-light">🔴 Show Offline</button>
                <button id="export-md" class="btn btn-sm btn-outline-light">📄 Export MD</button>
                <button id="export-json" class="btn btn-sm btn-outline-light">🔧 Export JSON</button>
                <button onclick="location.reload()" class="btn btn-sm btn-outline-light">🔄 Refresh</button>
            </div>
        </nav>

        <div class="container-fluid p-4">
            <h2 class="mb-4">Service Status</h2>
            <div id="service-dashboard" class="d-flex flex-wrap gap-4">
                <!-- Cards injected here -->
            </div>
        </div>

        <footer class="bg-light text-center py-3 mt-5 border-top">
            <small>Version <?= h(APP_VERSION) ?> — <?= date("Y-m-d") ?></small>
        </footer>
    </div>
</div>

</body>
</html>
