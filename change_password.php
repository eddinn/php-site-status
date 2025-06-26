<?php
require_once 'init.php';
require_once 'config.php';

require_login();

$users_file = USERS_FILE;
$users = json_decode(file_get_contents($users_file), true);
$user = $_SESSION['user'];

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf($_POST['csrf'] ?? '')) {
        $error = "Invalid request token.";
    } else {
        $current = $_POST['current_password'] ?? '';
        $new = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        if (!password_verify($current, $users[$user]['password'])) {
            $error = "Current password is incorrect.";
        } elseif (strlen($new) < 8) {
            $error = "New password must be at least 8 characters.";
        } elseif ($new !== $confirm) {
            $error = "New passwords do not match.";
        } else {
            $users[$user]['password'] = password_hash($new, PASSWORD_DEFAULT);
            $users[$user]['force_password_change'] = false;

            if (file_put_contents($users_file, json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES))) {
                $success = "Password successfully changed.";
                header("Refresh:2; url=index.php");
            } else {
                $error = "Failed to save changes. Check file permissions.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf" content="<?= h(csrf_token()) ?>">
    <link rel="stylesheet" href="assets/styles.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="assets/dashboard.js" defer></script>
    <meta charset="UTF-8">
    <title>Change Password – Homelab Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf" content="<?= h(csrf_token()) ?>">
    <link rel="stylesheet" href="assets/styles.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="light-mode d-flex justify-content-center align-items-center vh-100">

<div class="card shadow-sm p-4" style="min-width: 350px; max-width: 460px;">
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Change Password</li>
        </ol>
    </nav>

    <h4 class="mb-3 text-center">🔐 Change Admin Password</h4>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= h($error) ?></div>
    <?php elseif ($success): ?>
        <div class="alert alert-success"><?= h($success) ?><br>Redirecting…</div>
    <?php endif; ?>

    <form method="post">
        <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">

        <div class="mb-3">
            <label for="current_password" class="form-label">Current Password</label>
            <input type="password" name="current_password" id="current_password" class="form-control" required autocomplete="current-password">
        </div>

        <div class="mb-3">
            <label for="new_password" class="form-label">New Password</label>
            <input type="password" name="new_password" id="new_password" class="form-control" required autocomplete="new-password">
        </div>

        <div class="mb-3">
            <label for="confirm_password" class="form-label">Confirm New Password</label>
            <input type="password" name="confirm_password" id="confirm_password" class="form-control" required autocomplete="new-password">
        </div>

        <button type="submit" class="btn btn-success w-100">Update Password</button>

        <div class="mt-3 small text-muted">
            Forgot your password? Delete the file below to reset:<br>
            <code><?= h($users_file) ?></code>
        </div>
    </form>

    <footer class="text-center mt-4 small text-muted">
        Version <?= h(APP_VERSION) ?> — <?= date("Y-m-d") ?>
    </footer>
</div>

</body>
</html>
