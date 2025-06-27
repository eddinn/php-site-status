<?php
require_once 'init.php';
require_once 'config.php';

require_login();

$services = file_exists(SERVICES_FILE) ? json_decode(file_get_contents(SERVICES_FILE), true) : [];

$old_group = $_GET['group'] ?? null;
$is_edit = $old_group && isset($services[$old_group]);

$error = '';
$success = '';

if ($is_edit && !isset($services[$old_group])) {
    http_response_code(404);
    echo "<h1>404 – Group Not Found</h1>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf($_POST['csrf'] ?? '')) {
        $error = "Invalid request token.";
    } else {
        $new_group = trim($_POST['group'] ?? '');

        if ($new_group === '') {
            $error = "Group name cannot be empty.";
        } elseif (isset($services[$new_group]) && $new_group !== $old_group) {
            $error = "Another group with that name already exists.";
        } else {
            if ($is_edit) {
                $services[$new_group] = $services[$old_group];
                if ($new_group !== $old_group) {
                    unset($services[$old_group]);
                }
            } else {
                $services[$new_group] = [];
            }

            if (file_put_contents(SERVICES_FILE, json_encode($services, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES))) {
                header("Location: manage_services.php");
                exit;
            } else {
                $error = "Failed to save group. Check permissions.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= $is_edit ? 'Edit Group' : 'Add Group' ?> – Homelab Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="assets/styles.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="light-mode">

<nav class="navbar navbar-expand-lg navbar-dark bg-primary px-4">
    <a class="navbar-brand" href="index.php">Homelab Dashboard</a>
</nav>

<div class="container my-5" style="max-width: 700px;">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="manage_services.php">Manage Services</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?= $is_edit ? 'Edit Group' : 'Add Group' ?></li>
        </ol>
    </nav>

    <h3><?= $is_edit ? 'Edit Group Name' : 'Create New Group' ?></h3>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= h($error) ?></div>
    <?php endif; ?>

    <form method="post">
        <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
        <div class="mb-3">
            <label for="group" class="form-label">Group Name</label>
            <input type="text" name="group" id="group" class="form-control" required value="<?= h($old_group ?? '') ?>" placeholder="e.g. Media, Networking">
        </div>
        <div class="d-flex gap-2">
            <a href="manage_services.php" class="btn btn-secondary">← Back</a>
            <button type="submit" class="btn btn-success"><?= $is_edit ? '💾 Save' : '➕ Add Group' ?></button>
        </div>
    </form>
</div>

<footer class="text-center py-3 border-top bg-light mt-5">
    <small>Version <?= h(APP_VERSION) ?> — <?= date("Y-m-d") ?></small>
</footer>

</body>
</html>
