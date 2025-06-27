<?php
require_once 'init.php';
require_once 'config.php';

require_login();

$old_group = $_GET['group'] ?? '';
$services = file_exists(SERVICES_FILE) ? json_decode(file_get_contents(SERVICES_FILE), true) : [];
$error = '';

if (!$old_group || !isset($services[$old_group])) {
    http_response_code(404);
    die("Group not found.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();

    $new_group = trim($_POST['group'] ?? '');
    if ($new_group === '') {
        $error = "Group name cannot be empty.";
    } elseif ($new_group !== $old_group && isset($services[$new_group])) {
        $error = "That name is already taken.";
    } else {
        $services[$new_group] = $services[$old_group];
        if ($new_group !== $old_group) {
            unset($services[$old_group]);
        }
        if (file_put_contents(SERVICES_FILE, json_encode($services, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES))) {
            header("Location: manage_services.php");
            exit;
        } else {
            $error = "Failed saving changes.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Group – <?= h($old_group) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf" content="<?= h(csrf_token()) ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/styles.css">
</head>
<body class="light-mode">
<div class="d-flex">
    <?php include 'sidebar.php'; ?>
    <div class="flex-fill ms-sidebar">
        <nav class="navbar navbar-expand-lg navbar-dark bg-primary px-4">
            <a class="navbar-brand text-white" href="manage_services.php">← Back</a>
        </nav>
        <div class="container py-4">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="manage_services.php">Manage Services</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Edit Group</li>
                </ol>
            </nav>
            <h3>Edit Group Name</h3>
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= h($error) ?></div>
            <?php endif; ?>
            <form method="post" class="mt-3">
                <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
                <div class="mb-3">
                    <label for="group" class="form-label">New Group Name</label>
                    <input type="text" name="group" id="group" class="form-control" value="<?= h($old_group) ?>" required>
                </div>
                <button type="submit" class="btn btn-success">💾 Save</button>
            </form>
        </div>

        <footer class="text-center py-3 border-top mt-4">
            <small>Version <?= h(APP_VERSION) ?> — <?= date("Y-m-d") ?></small>
        </footer>
    </div>
</div>
</body>
</html>
