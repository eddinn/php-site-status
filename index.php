<?php
require_once 'includes/functions.php';
require_once 'config.php';
session_start();

$data = load_data();
$page_title = "Dashboard";
require_once 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="mb-0">Service Dashboard</h2>
    <a href="add_group.php" class="btn btn-sm btn-success">+ Add Group</a>
</div>

<div id="group-container" class="row sortable-groups" data-csrf="<?= e(generate_csrf_token()) ?>">
    <?php foreach ($data as $group): ?>
        <div class="col-md-6 mb-4 group-card" data-group-id="<?= e($group['id']) ?>">
            <div class="card shadow">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <strong><?= e($group['name']) ?></strong>
                    <div>
                        <a href="edit_group.php?id=<?= e($group['id']) ?>" class="btn btn-sm btn-outline-secondary">Edit</a>
                        <a href="delete_group.php?id=<?= e($group['id']) ?>&csrf_token=<?= e(generate_csrf_token()) ?>" class="btn btn-sm btn-outline-danger">Delete</a>
                    </div>
                </div>
                <ul class="list-group list-group-flush sortable-services" data-group-id="<?= e($group['id']) ?>">
                    <?php foreach ($group['services'] as $index => $service): ?>
                        <?php $online = check_url($service['url']); ?>
                        <li class="list-group-item service-item" data-service-index="<?= $index ?>">
                            <div class="service-title-row d-flex justify-content-between align-items-center">
                                <strong><?= e($service['title'] ?? 'Unnamed Service') ?></strong>
                                <span class="badge bg-<?= $online ? 'success' : 'danger' ?>">
                                    <?= $online ? 'Online' : 'Offline' ?>
                                </span>
                            </div>
                            <div><a href="<?= e($service['url']) ?>" target="_blank"><?= e($service['url']) ?></a></div>
                            <div class="service-controls mt-2">
                                <a href="edit_service.php?group_id=<?= e($group['id']) ?>&service_index=<?= e($index) ?>" class="btn btn-sm btn-outline-secondary me-2">Edit</a>
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
