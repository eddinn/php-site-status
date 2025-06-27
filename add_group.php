<?php
require_once 'includes/functions.php';

$groups = load_data();
$dark_class = (isset($_COOKIE['theme']) && $_COOKIE['theme'] === 'dark') ? 'dark-mode' : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        set_flash("Invalid CSRF token.", 'danger');
        header('Location: index.php');
        exit;
    }

    $name = trim($_POST['name'] ?? '');

    if ($name === '') {
        set_flash("Group name cannot be empty.", 'danger');
    } else {
        $groups[] = [
            'id' => uniqid('group'),
            'name' => $name,
            'services' => []
        ];
        save_data($groups);
        set_flash("Group \"$name\" added successfully.");
        header('Location: index.php');
        exit;
    }
}

$csrf = generate_csrf_token();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Service Group</title>
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
    <h3>Add New Service Group</h3>
    <form method="POST" class="mt-3">
        <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
        <div class="mb-3">
            <label for="name" class="form-label">Group Name</label>
            <input type="text" name="name" id="name" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-success">Create Group</button>
        <a href="index.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/script.js" defer></script>
</body>
</html>
