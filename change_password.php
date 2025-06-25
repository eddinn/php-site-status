<?php
require_once 'init.php';
require_once 'config.php';

require_login();

$users = file_exists(USERS_FILE)
    ? json_decode(file_get_contents(USERS_FILE), true)
    : [];

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();

    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (strlen($new_password) < 6) {
        $error = "Password must be at least 6 characters.";
    } elseif ($new_password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        $users[DEFAULT_USER]['password'] = password_hash($new_password, PASSWORD_DEFAULT);
        $users[DEFAULT_USER]['force_password_change'] = false;

        file_put_contents(USERS_FILE, json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        $success = "Password updated successfully.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Change Password – Homelab Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5" style="max-width: 500px;">
    <h3 class="mb-4">Change Admin Password</h3>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= h($error) ?></div>
    <?php elseif ($success): ?>
        <div class="alert alert-success"><?= h($success) ?></div>
    <?php endif; ?>

    <form method="post">
        <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
        <div class="mb-3">
            <input type="password" name="new_password" class="form-control" placeholder="New password" required>
        </div>
        <div class="mb-3">
            <input type="password" name="confirm_password" class="form-control" placeholder="Confirm password" required>
        </div>
        <button type="submit" class="btn btn-primary">Change Password</button>
        <a href="index.php" class="btn btn-secondary ms-2">Cancel</a>
    </form>

    <hr>
    <p class="text-muted mt-4">
        ⚠️ If you forget your password and can't log in:<br>
        <strong>Manually reset it</strong> by editing the file:<br>
        <code><?= h(USERS_FILE) ?></code><br>
        Replace the hash with:<br>
        <code><?= h(password_hash(DEFAULT_PASSWORD, PASSWORD_DEFAULT)) ?></code><br>
        Then log in again using password "<strong><?= h(DEFAULT_PASSWORD) ?></strong>".
    </p>
</div>
</body>
</html>
