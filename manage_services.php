<?php
require_once 'init.php';

if (!isset($_SESSION['user']) || $_SESSION['user'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$services_file = 'services.json';
$services = file_exists($services_file) ? json_decode(file_get_contents($services_file), true) : [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Services – Homelab Dashboard</title>
    <meta name="csrf" content="<?= h(csrf_token()) ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .table td {
            vertical-align: middle;
        }
        .drag-handle {
            cursor: grab;
            margin-right: 10px;
        }
        .group-card {
            cursor: grab;
        }
    </style>
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-dark px-4">
    <span class="navbar-brand">Service Management</span>
    <div class="ms-auto">
        <a href="edit_services.php" class="btn btn-sm btn-outline-light me-2">Edit JSON</a>
        <a href="index.php" class="btn btn-sm btn-outline-light">Back to Dashboard</a>
    </div>
</nav>

<div class="container my-4">
    <h3 class="mb-4">Manage Services by Group</h3>

    <?php if (empty($services)): ?>
        <div class="alert alert-info">No services defined.</div>
    <?php endif; ?>

    <div id="group-container">
        <?php foreach ($services as $group => $items): ?>
            <div class="card mb-4 group-card" data-group="<?= h($group) ?>">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><span class="drag-handle">&#x2630;</span> <strong><?= h($group) ?></strong></span>
                    <div class="d-flex gap-2">
                        <a href="add_service.php?group=<?= urlencode($group) ?>" class="btn btn-sm btn-success">Add Service</a>
                        <a href="delete_group.php?group=<?= urlencode($group) ?>" class="btn btn-sm btn-danger"
                           onclick="return confirm('Delete entire group <?= h($group) ?>?')">Delete Group</a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <table class="table table-bordered mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>URL</th>
                                <th style="width: 200px">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="sortable-group" data-group="<?= h($group) ?>">
                            <?php foreach ($items as $index => $url): ?>
                                <tr data-url="<?= h($url) ?>">
                                    <td><span class="drag-handle">&#9776;</span> <?= $index ?></td>
                                    <td><a href="<?= h($url) ?>" target="_blank" rel="noopener noreferrer"><?= h($url) ?></a></td>
                                    <td class="d-flex gap-2">
                                        <a href="edit_service.php?group=<?= urlencode($group) ?>&index=<?= $index ?>" class="btn btn-sm btn-primary">Edit</a>
                                        <form method="get" action="delete_service.php" onsubmit="return confirm('Delete this service?');">
                                            <input type="hidden" name="group" value="<?= h(urlencode($group)) ?>">
                                            <input type="hidden" name="index" value="<?= h($index) ?>">
                                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script src="assets/drag.js"></script>
</body>
</html>
