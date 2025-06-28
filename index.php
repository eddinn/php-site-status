<?php
require_once 'includes/functions.php';
require_once 'config.php';
session_start();

$data = load_data();
$page_title = "Dashboard";
require_once 'includes/header.php';
?>

<h2>Service Dashboard</h2>

<?php show_flash(); ?>

<div class="row">
    <?php foreach ($data as $group): ?>
        <div class="col-md-6 mb-4">
            <div class="card shadow">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <strong><?= e($group['name']) ?></strong>
                    <div>
                        <a href="edit_group.php?id=<?= e($group['id']) ?>" class="btn btn-sm btn-outline-secondary">Edit</a>
                        <a href="delete_group.php?id=<?= e($group['id']) ?>&csrf_token=<?= e(generate_csrf_token()) ?>" class="btn btn-sm btn-outline-danger">Delete</a>
                    </div>
                </div>
                <ul class="list-group list-group-flush">
                    <?php foreach ($group['services'] as $index => $service): ?>
                        <?php $online = check_url($service['url']); ?>
                        <li class="list-group-item">
                            <div class="service-title-row">
                                <strong><?= e($service['title'] ?? 'Unnamed Service') ?></strong>
                                <span class="badge bg-<?= $online ? 'success' : 'danger' ?>">
                                    <?= $online ? 'Online' : 'Offline' ?>
                                </span>
                            </div>
                            <div><a href="<?= e($service['url']) ?>" target="_blank"><?= e($service['url']) ?></a></div>
                            <div class="service-controls">
                                <a href="edit_service.php?group_id=<?= e($group['id']) ?>&service_index=<?= e($index) ?>" class="btn btn-sm btn-outline-secondary">Edit</a>
                                <a href="delete_service.php?group_id=<?= e($group['id']) ?>&service_index=<?= e($index) ?>&csrf_token=<?= e(generate_csrf_token()) ?>" class="btn btn-sm btn-outline-danger">Delete</a>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <div class="card-footer text-end">
                    <a href="add_service.php?group_id=<?= e($group['id']) ?>" class="btn btn-sm btn-primary">Add Service</a>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
