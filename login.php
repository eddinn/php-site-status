<?php
require_once 'init.php';
require_once 'config.php';

if (!file_exists(USERS_FILE)) {
    file_put_contents(
        USERS_FILE,
        json_encode([
            DEFAULT_USER => [
                'password' => password_hash(DEFAULT_PASSWORD, PASSWORD_DEFAULT),
                'force_password_change' => true,
                'last_login' => null,
                'last_ip' => null
            ]
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
    );
}

$users = json_decode(file_get_contents(USERS_FILE), true);
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf($_POST['csrf'] ?? '')) {
        $error = "Invalid request token.";
    } else {
        $password = $_POST['password'] ?? '';
        $user     = DEFAULT_USER;

        if (!isset($users[$user]) || !password_verify($password, $users[$user]['password'])) {
            $error = "Invalid password.";
        } else {
            session_regenerate_id(true);
            $_SESSION['user'] = $user;

            $users[$user]['last_login'] = date('Y-m-d H:i:s');
            $users[$user]['last_ip'] = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            file_put_contents(USERS_FILE, json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            $redirect = $_SESSION['redirect_to'] ?? 'index.php';
            unset($_SESSION['redirect_to']);

            if (!empty($users[$user]['force_password_change'])) {
                header("Location: change_password.php");
            } else {
                header("Location: $redirect");
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="d-flex justify-content-center align-items-center vh-100 bg-light">
<form method="post" class="p-4 bg-white shadow rounded" style="min-width:300px;">
    <h4 class="mb-3">Admin Login</h4>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= h($error) ?></div>
    <?php endif; ?>

    <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">

    <div class="mb-3">
        <input type="password"
               name="password"
               placeholder="Password"
               required
               autocomplete="current-password"
               class="form-control">
    </div>

    <button type="submit" class="btn btn-primary w-100">Login</button>

    <div class="text-center mt-2">
        <a href="reset_password.php" class="btn btn-sm btn-outline-secondary w-100">Forgot Password?</a>
    </div>
</form>
</body>
</html>
