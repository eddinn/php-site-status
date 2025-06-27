<?php
require_once 'includes/functions.php';

$groups = load_data();
$csrf = generate_csrf_token();
$page_title = "Dashboard";
require_once 'includes/header.php';
?>

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
                <ul class="list-group list-group-flush sortable-group" data-group-id="<?= e($group['id']) ?>">
                    <?php foreach ($group['services'] as $index => $service): 
                        [$online, $title] = check_url($service['url']);
                    ?>
                        <li class="list-group-item draggable-item" draggable="true" data-url="<?= e($service['url']) ?>">
                            <div class="service-title-row">
                                <div class="d-flex align-items-center">
                                    <span class="drag-handle">&#9776;</span>
                                    <strong><?= e($title) ?></strong>
                                </div>
                                <span class="badge bg-<?= $online ? 'success' : 'danger' ?>">
                                    <?= $online ? 'Online' : 'Offline' ?>
                                </span>
                            </div>
                            <div class="mt-1">
                                <a href="<?= e($service['url']) ?>" target="_blank"><?= e($service['url']) ?></a>
                            </div>
                            <div class="service-controls btn-group mt-2">
                                <a href="edit_service.php?group_id=<?= urlencode($group['id']) ?>&service_index=<?= $index ?>" class="btn btn-outline-secondary">Edit</a>
                                <a href="delete_service.php?group_id=<?= urlencode($group['id']) ?>&service_index=<?= $index ?>&csrf=<?= $csrf ?>"
                                   class="btn btn-outline-danger"
                                   onclick="return confirm('Delete this service?')">Delete</a>
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

<?php require_once 'includes/footer.php'; ?>
<script src="assets/drag.js"></script>
<script src="assets/script.js"></script>
