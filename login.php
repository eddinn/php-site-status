<?php
require_once 'init.php';

$users_file = __DIR__ . '/../secure/users.json';

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

            if (!empty($users[$user]['force_password_change'])) {
                header("Location: change_password.php");
            } else {
                header("Location: edit_services.php");
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
    <form method="post" class="p-4 bg-white shadow rounded" style="min-width: 300px;">
        <h4 class="mb-3">Admin Login</h4>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= h($error) ?></div>
        <?php endif; ?>
        <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
        <div class="mb-3">
            <input type="password" name="password" placeholder="Password" required class="form-control" autocomplete="current-password">
        </div>
        <button type="submit" class="btn btn-primary w-100">Login</button>
        <div class="text-center mt-2">
            <a href="reset_password.php" class="btn btn-sm btn-outline-secondary w-100">Forgot Password?</a>
        </div>
    </form>
</body>
</html>
