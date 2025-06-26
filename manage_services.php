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
                    $success = "Group '{$new_group}' added.";
                } else {
                    $error = "Invalid or duplicate group name.";
                }
                break;

            case 'delete_group':
                $group = $_POST['group'] ?? '';
                if (isset($services[$group])) {
                    unset($services[$group]);
                    $success = "Group '{$group}' deleted.";
                } else {
                    $error = "Group not found.";
                }
                break;

            case 'add_service':
                $group = $_POST['group'] ?? '';
                $url = trim($_POST['url'] ?? '');
                if (isset($services[$group]) && filter_var($url, FILTER_VALIDATE_URL)) {
                    $services[$group][] = $url;
                    $success = "Service added.";
                } else {
                    $error = "Invalid group or URL.";
                }
                break;

            case 'delete_service':
                $group = $_POST['group'] ?? '';
                $index = intval($_POST['index'] ?? -1);
                if (isset($services[$group][$index])) {
                    array_splice($services[$group], $index, 1);
                    $success = "Service removed.";
                } else {
                    $error = "Service not found.";
                }
                break;
        }

        if (!$error) {
            file_put_contents(SERVICES_FILE, json_encode($services, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Manage Services – Homelab Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf" content="<?= h(csrf_token()) ?>" />
    <link rel="stylesheet" href="assets/styles.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
</head>
<body class="light-mode">

<nav class="navbar navbar-expand-lg navbar-dark bg-primary px-4">
    <a class="navbar-brand text-white" href="index.php">← Back to Dashboard</a>
</nav>

<div class="container py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Manage Services</li>
        </ol>
    </nav>

    <h2 class="mb-4">Manage Service Groups</h2>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= h($error) ?></div>
    <?php elseif ($success): ?>
        <div class="alert alert-success"><?= h($success) ?></div>
    <?php endif; ?>

    <!-- Add Group Form -->
    <form method="post" class="mb-4 row g-2 align-items-end">
        <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>" />
        <input type="hidden" name="action" value="add_group" />
        <div class="col-sm-8">
            <label for="new_group" class="form-label">New Group Name</label>
            <input type="text" name="new_group" id="new_group" class="form-control" required placeholder="e.g. Media, Networking" />
        </div>
        <div class="col-sm-4 text-end">
            <button class="btn btn-success w-100">Add Group</button>
        </div>
    </form>

    <!-- Groups & Services -->
    <div id="group-list" class="d-flex flex-wrap gap-4">
        <?php foreach ($services as $group => $urls): ?>
            <div class="card group-card" data-group="<?= h($group) ?>">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><strong><?= h($group) ?></strong></span>
                    <div class="btn-group">
                        <a href="add_service.php?group=<?= urlencode($group) ?>" class="btn btn-sm btn-outline-primary">✚ Add</a>
                        <a href="edit_group.php?group=<?= urlencode($group) ?>" class="btn btn-sm btn-outline-secondary">✎ Edit</a>
                        <form method="post" class="m-0 d-inline">
                            <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>" />
                            <input type="hidden" name="action" value="delete_group" />
                            <input type="hidden" name="group" value="<?= h($group) ?>" />
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete group <?= h($group) ?>?')">🗑️</button>
                        </form>
                    </div>
                </div>
                <ul class="list-group list-group-flush service-list">
                    <?php foreach ($urls as $index => $url): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center" data-index="<?= $index ?>">
                            <span><?= h($url) ?></span>
                            <div class="btn-group">
                                <a href="edit_service.php?group=<?= urlencode($group) ?>&index=<?= $index ?>" class="btn btn-sm btn-outline-secondary">✎</a>
                                <form method="post" class="m-0 d-inline">
                                    <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>" />
                                    <input type="hidden" name="action" value="delete_service" />
                                    <input type="hidden" name="group" value="<?= h($group) ?>" />
                                    <input type="hidden" name="index" value="<?= $index ?>" />
                                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Remove this service?')">🗑️</button>
                                </form>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<footer class="text-center py-3 border-top bg-light">
    <small>Version <?= h(APP_VERSION) ?> — <?= date("Y-m-d") ?></small>
</footer>

<script>
    Sortable.create(document.getElementById('group-list'), {
        handle: '.card-header',
        animation: 150,
        onEnd: () => {
            const order = Array.from(document.querySelectorAll('.group-card')).map(c => c.getAttribute('data-group'));
            fetch('save_group_order.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ csrf: '<?= h(csrf_token()) ?>', order })
            });
        }
    });

    document.querySelectorAll('.service-list').forEach(ul => {
        Sortable.create(ul, {
            handle: 'li',
            animation: 150,
            onEnd: (evt) => {
                const group = evt.to.closest('.group-card').getAttribute('data-group');
                const order = Array.from(evt.to.querySelectorAll('li')).map(li => li.querySelector('span').textContent.trim());
                fetch('save_sort_order.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({ csrf: '<?= h(csrf_token()) ?>', group, order })
                });
            }
        });
    });
</script>
</body>
</html>
