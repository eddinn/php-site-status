<?php
require_once 'init.php';
require_once 'config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf($_POST['csrf'] ?? '')) {
        $error = "Invalid request token.";
    } else {
        $users = load_users();
        $users['admin']['password'] = password_hash('changeme', PASSWORD_DEFAULT);
        $users['admin']['force_password_change'] = true;
        if (save_users($users)) {
            $success = "Password has been reset to default ('changeme'). You will be prompted to change it on next login.";
        } else {
            $error = "Failed to reset password. Check file permissions.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reset Password – Homelab Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="assets/styles.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="light-mode d-flex justify-content-center align-items-center vh-100">

<div class="card shadow p-4" style="max-width: 450px; width: 100%;">
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="login.php">Login</a></li>
            <li class="breadcrumb-item active" aria-current="page">Reset Password</li>
        </ol>
    </nav>

    <h4 class="mb-3 text-center">🔁 Reset Admin Password</h4>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= h($success) ?></div>
        <a href="login.php" class="btn btn-primary w-100">Back to Login</a>
    <?php else: ?>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= h($error) ?></div>
        <?php endif; ?>

        <form method="post">
            <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
            <p>This will reset the admin password to <code>changeme</code> and require a change on next login.</p>
            <button type="submit" class="btn btn-danger w-100">Reset Password</button>
        </form>
    <?php endif; ?>

    <div class="mt-3 text-muted small">
        Or manually reset it by deleting:<br>
        <code><?= h(USERS_FILE) ?></code>
    </div>

    <footer class="text-center mt-4 small text-muted">
        Version <?= h(APP_VERSION) ?> — <?= date("Y-m-d") ?>
    </footer>
</div>

</body>
</html>
