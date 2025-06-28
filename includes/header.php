<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/functions.php';

$page_title = $page_title ?? DEFAULT_TITLE;
$dark_class = (isset($_COOKIE['theme']) && $_COOKIE['theme'] === 'dark') ? 'dark-mode' : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= e($page_title) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
    <link rel="stylesheet" href="assets/styles.css">
</head>
<body class="<?= $dark_class ?>">
<nav class="navbar navbar-dark bg-dark px-3">
    <a class="navbar-brand text-light" href="index.php"><?= e(SITE_NAME) ?></a>
    <?php if (ENABLE_DARK_MODE): ?>
    <div class="d-flex align-items-center ms-auto">
        <div class="form-check form-switch text-light me-3">
            <input class="form-check-input" type="checkbox" id="darkModeToggle">
            <label class="form-check-label" for="darkModeToggle">Dark Mode</label>
        </div>
    </div>
    <?php endif; ?>
</nav>
<div class="container mt-4">
<?= show_flash() ?>
