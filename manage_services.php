<?php
require_once 'init.php';
require_once 'config.php';

require_login();

$error = '';
$success = '';
$services = file_exists(SERVICES_FILE) ? json_decode(file_get_contents(SERVICES_FILE), true) : [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();

    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_group':
                $new_group = trim($_POST['new_group'] ?? '');
                if ($new_group && !isset($services[$new_group])) {
                    $services[$new_group] = [];
                    $success = "Group '$new_group' added.";
                } else {
                    $error = "Invalid or duplicate group name.";
                }
                break;

            case 'delete_group':
                $group = $_POST['group'] ?? '';
                if (isset($services[$group])) {
                    unset($services[$group]);
                    $success = "Group '$group' deleted.";
                } else {
                    $error = "Group not found.";
                }
                break;

            case 'add_service':
                $group = $_POST['group'] ?? '';
                $url = trim($_POST['url'] ?? '');
                if (isset($services[$group]) && filter_var($url, FILTER_VALIDATE_URL)) {
                    $services[$group][] = $url;
                    $success = "Service added to '$group'.";
                } else {
                    $error = "Invalid group or URL.";
                }
                break;

            case 'delete_service':
                $group = $_POST['group'] ?? '';
                $index = intval($_POST['index'] ?? -1);
                if (isset($services[$group][$index])) {
                    array_splice($services[$group], $index, 1);
                    $success = "Service removed from '$group'.";
                } else {
                    $error = "Service not found.";
                }
                break;

            case 'reorder':
                $new_order = json_decode($_POST['order'] ?? '', true);
                if (is_array($new_order)) {
                    $reordered = [];
                    foreach ($new_order as $group => $urls) {
                        if (isset($services[$group]) && is_array($urls)) {
                            $clean_urls = array_values(array_filter($urls, fn($u) => filter_var($u, FILTER_VALIDATE_URL)));
                            $reordered[$group] = $clean_urls;
                        }
                    }
                    $services = $reordered;
                    $success = "Services reordered.";
                } else {
                    $error = "Invalid order data.";
                }
                break;
        }

        // Save after any successful operation
        if (!$error) {
            file_put_contents(SERVICES_FILE, json_encode($services, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Services – Homelab Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf" content="<?= h(csrf_token()) ?>">
    <link rel="stylesheet" href="assets/styles.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
</head>
<body class="light-mode">
<div class="container py-4">
    <h2>Manage Service Groups</h2>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= h($error) ?></div>
    <?php elseif ($success): ?>
        <div class="alert alert-success"><?= h($success) ?></div>
    <?php endif; ?>

    <!-- Add Group -->
    <form method="post" class="mb-4 d-flex align-items-end gap-2">
        <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
        <input type="hidden" name="action" value="add_group">
        <div class="form-group">
            <label for="new_group">New Group Name</label>
            <input type="text" class="form-control" id="new_group" name="new_group" required>
        </div>
        <button class="btn btn-primary">Add Group</button>
    </form>

    <!-- Group Cards -->
    <div id="group-list">
        <?php foreach ($services as $group => $urls): ?>
            <div class="card mb-4 group-card" data-group="<?= h($group) ?>">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <strong><?= h($group) ?></strong>
                    <form method="post" class="m-0">
                        <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
                        <input type="hidden" name="action" value="delete_group">
                        <input type="hidden" name="group" value="<?= h($group) ?>">
                        <button class="btn btn-sm btn-danger" onclick="return confirm('Delete group <?= h($group) ?>?')">Delete</button>
                    </form>
                </div>
                <ul class="list-group list-group-flush service-list">
                    <?php foreach ($urls as $index => $url): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center" data-index="<?= $index ?>">
                            <span><?= h($url) ?></span>
                            <form method="post" class="m-0">
                                <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
                                <input type="hidden" name="action" value="delete_service">
                                <input type="hidden" name="group" value="<?= h($group) ?>">
                                <input type="hidden" name="index" value="<?= $index ?>">
                                <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Remove service?')">Remove</button>
                            </form>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <form method="post" class="card-body d-flex align-items-end gap-2">
                    <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
                    <input type="hidden" name="action" value="add_service">
                    <input type="hidden" name="group" value="<?= h($group) ?>">
                    <div class="form-group flex-grow-1">
                        <label>Add Service URL</label>
                        <input type="url" name="url" class="form-control" placeholder="http://example.com" required>
                    </div>
                    <button class="btn btn-success">Add</button>
                </form>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Reorder Button -->
    <form method="post" onsubmit="return handleReorder(this);">
        <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
        <input type="hidden" name="action" value="reorder">
        <input type="hidden" name="order" id="order-input">
        <button class="btn btn-outline-primary">Save Reorder</button>
    </form>
</div>

<script>
    function handleReorder(form) {
        const data = {};
        document.querySelectorAll('.group-card').forEach(card => {
            const group = card.getAttribute('data-group');
            const urls = [];
            card.querySelectorAll('.service-list li').forEach(li => {
                const url = li.querySelector('span')?.textContent?.trim();
                if (url) urls.push(url);
            });
            data[group] = urls;
        });
        form.order.value = JSON.stringify(data);
        return true;
    }

    document.querySelectorAll('.service-list').forEach(ul => {
        Sortable.create(ul, {
            animation: 150,
            handle: '.list-group-item',
            ghostClass: 'bg-warning'
        });
    });

    Sortable.create(document.getElementById('group-list'), {
        animation: 150,
        handle: '.card-header',
        ghostClass: 'bg-warning',
        onEnd: function (evt) {
            const container = document.getElementById('group-list');
            const reordered = {};
            Array.from(container.children).forEach(card => {
                reordered[card.getAttribute('data-group')] = [];
            });
        }
    });
</script>
</body>
</html>
