<?php
require_once 'includes/functions.php';

$groups = load_data();
$group_id = $_GET['group_id'] ?? '';
$group_index = -1;
$dark_class = (isset($_COOKIE['theme']) && $_COOKIE['theme'] === 'dark') ? 'dark-mode' : '';

// Locate group
foreach ($groups as $i => $group) {
    if ($group['id'] === $group_id) {
        $group_index = $i;
        break;
    }
}

if ($group_index === -1) {
    set_flash("Invalid group ID.", 'danger');
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        set_flash("Invalid CSRF token.", 'danger');
        header("Location: index.php");
        exit;
    }

    $url = trim($_POST['url'] ?? '');

    if (!is_valid_url($url)) {
        set_flash("Invalid service URL.", 'danger');
    } else {
        $groups[$group_index]['services'][] = ['url' => $url];
        save_data($groups);
        set_flash("Service added successfully.");
        header('Location: index.php');
        exit;
    }
}

$csrf = generate_csrf_token();
$group_name = $groups[$group_index]['name'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Service</title>
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
    <h3>Add Service to <em><?= e($group_name) ?></em></h3>
    <form method="POST" class="mt-3">
        <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
        <div class="mb-3">
            <label for="url" class="form-label">Service URL</label>
            <input type="text" name="url" id="url" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">Add Service</button>
        <a href="index.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/script.js" defer></script>
</body>
</html>
