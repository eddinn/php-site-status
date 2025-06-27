<?php
require_once 'includes/functions.php';

$groups = load_data();
$csrf = generate_csrf_token();
$dark_class = (isset($_COOKIE['theme']) && $_COOKIE['theme'] === 'dark') ? 'dark-mode' : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Service Status</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
    <link rel="stylesheet" href="assets/styles.css">
</head>
<body class="<?= $dark_class ?>">
<nav class="navbar navbar-dark bg-dark px-3">
    <a class="navbar-brand text-light" href="index.php">Dashboard</a>
    <div class="d-flex align-items-center ms-auto">
        <div class="form-check form-switch text-light me-3">
            <input class="form-check-input" type="checkbox" id="darkModeToggle">
            <label class="form-check-label" for="darkModeToggle">Dark Mode</label>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <?= show_flash() ?>
    <div class="row">
        <?php foreach ($groups as $group): ?>
            <div class="col-md-6 mb-4">
                <div class="card shadow">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <strong><?= e($group['name']) ?></strong>
                        <div class="btn-group">
                            <a href="edit_group.php?id=<?= urlencode($group['id']) ?>" class="btn btn-sm btn-outline-secondary">Edit</a>
                            <a href="delete_group.php?id=<?= urlencode($group['id']) ?>&csrf=<?= $csrf ?>" class="btn btn-sm btn-outline-danger"
                               onclick="return confirm('Delete this group and all its services?')">Delete</a>
                        </div>
                    </div>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($group['services'] as $index => $service): 
                            [$online, $title] = check_url($service['url']);
                        ?>
                            <li class="list-group-item d-flex justify-content-between align-items-start">
                                <div class="me-auto">
                                    <strong><?= e($title) ?></strong><br>
                                    <a href="<?= e($service['url']) ?>" target="_blank"><?= e($service['url']) ?></a>
                                </div>
                                <div class="text-end ms-3">
                                    <span class="badge bg-<?= $online ? 'success' : 'danger' ?>">
                                        <?= $online ? 'Online' : 'Offline' ?>
                                    </span>
                                    <div class="btn-group mt-2">
                                        <a href="edit_service.php?group_id=<?= urlencode($group['id']) ?>&service_index=<?= $index ?>" class="btn btn-sm btn-outline-secondary">Edit</a>
                                        <a href="delete_service.php?group_id=<?= urlencode($group['id']) ?>&service_index=<?= $index ?>&csrf=<?= $csrf ?>"
                                           class="btn btn-sm btn-outline-danger"
                                           onclick="return confirm('Delete this service?')">Delete</a>
                                    </div>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <div class="card-footer text-end">
                        <a href="add_service.php?group_id=<?= urlencode($group['id']) ?>" class="btn btn-sm btn-primary">+ Add Service</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="text-center mt-4">
        <a href="add_group.php" class="btn btn-success">+ Add New Group</a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/script.js" defer></script>
</body>
</html>
