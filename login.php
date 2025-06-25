<?php
require_once 'init.php';
require_once 'config.php';

$users_file = USERS_FILE;

if (!file_exists($users_file)) {
    file_put_contents($users_file, json_encode([
        'admin' => [
            'password' => password_hash('changeme', PASSWORD_DEFAULT),
            'force_password_change' => true
        ]
    ], JSON_PRETTY_PRINT));
}

$users = json_decode(file_get_contents($users_file), true);
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf($_POST['csrf'] ?? '')) {
        $error = "Invalid request token.";
    } else {
        $password = $_POST['password'] ?? '';
        $user = 'admin';

        if (!isset($users[$user]) || !password_verify($password, $users[$user]['password'])) {
            $error = "Invalid password.";
        } else {
            session_regenerate_id(true);
            $_SESSION['user'] = $user;

            // Last login tracking
            if (!isset($users[$user]['last_login'])) {
                $users[$user]['last_login'] = [];
            }
            $users[$user]['last_login'][] = [
                'timestamp' => date('Y-m-d H:i:s'),
                'ip' => $_SERVER['REMOTE_ADDR']
            ];
            file_put_contents($users_file, json_encode($users, JSON_PRETTY_PRINT));

            $redirect = $_SESSION['redirect_after_login'] ?? 'index.php';
            unset($_SESSION['redirect_after_login']);

            if (!empty($users[$user]['force_password_change'])) {
                header("Location: change_password.php");
            } else {
                header("Location: " . $redirect);
            }
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login – Homelab Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf" content="<?= h(csrf_token()) ?>">
    <link rel="stylesheet" href="assets/styles.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="light-mode d-flex justify-content-center align-items-center vh-100">

<div class="card shadow-sm p-4" style="min-width: 320px; max-width: 400px;">
    <h4 class="mb-3 text-center">🔒 Admin Login</h4>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= h($error) ?></div>
    <?php endif; ?>

    <form method="post" autocomplete="off">
        <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">

        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" name="password" id="password" placeholder="changeme" required class="form-control" autocomplete="current-password">
        </div>

        <button type="submit" class="btn btn-primary w-100">Login</button>

        <div class="text-center mt-3">
            <a href="reset_password.php" class="btn btn-sm btn-outline-secondary w-100">Forgot Password?</a>
        </div>
    </form>

    <footer class="text-center mt-4 small text-muted">
        Version <?= h(APP_VERSION) ?> — <?= date("Y-m-d") ?>
    </footer>
</div>

</body>
</html>
