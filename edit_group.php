<?php
require_once 'includes/functions.php';

$groups = load_data();
$group_id = $_GET['id'] ?? '';
$dark_class = (isset($_COOKIE['theme']) && $_COOKIE['theme'] === 'dark') ? 'dark-mode' : '';
$group_index = -1;

// Find group
foreach ($groups as $i => $group) {
    if ($group['id'] === $group_id) {
        $group_index = $i;
        break;
    }
}

if ($group_index === -1) {
    set_flash("Group not found.", 'danger');
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        set_flash("Invalid CSRF token.", 'danger');
        header('Location: index.php');
        exit;
    }

    $new_name = trim($_POST['name'] ?? '');

    if ($new_name === '') {
        set_flash("Group name cannot be empty.", 'danger');
    } else {
        $groups[$group_index]['name'] = $new_name;
        save_data($groups);
        set_flash("Group renamed successfully.");
        header('Location: index.php');
        exit;
    }
}

$csrf = generate_csrf_token();
$current_name = $groups[$group_index]['name'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Service Group</title>
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
    <h3>Edit Service Group</h3>
    <form method="POST" class="mt-3">
        <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
        <div class="mb-3">
            <label for="name" class="form-label">Group Name</label>
            <input type="text" name="name" id="name" class="form-control" required value="<?= e($current_name) ?>">
        </div>
        <button type="submit" class="btn btn-primary">Update Group</button>
        <a href="index.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/script.js" defer></script>
</body>
</html>
