<?php
require_once 'init.php';

$users_file = 'users.json';

// Block access if not logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$username = $_SESSION['user'];
$users = json_decode(file_get_contents($users_file), true);
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf($_POST['csrf'] ?? '')) {
        $error = "Invalid request token.";
    } else {
        $newpass = $_POST['newpass'] ?? '';
        $confirm = $_POST['confirm'] ?? '';

        if (strlen($newpass) < 6) {
            $error = "Password must be at least 6 characters.";
        } elseif ($newpass !== $confirm) {
            $error = "Passwords do not match.";
        } else {
            $users[$username]['password'] = password_hash($newpass, PASSWORD_DEFAULT);
            $users[$username]['force_password_change'] = false;

            if (file_put_contents($users_file, json_encode($users, JSON_PRETTY_PRINT)) === false) {
                $error = "Failed to save new password.";
            } else {
                $success = "Password changed. Redirecting...";
                header("Refresh:2; URL=edit_services.php");
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Change Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="d-flex justify-content-center align-items-center vh-100 bg-light">
    <form method="post" class="p-4 bg-white shadow rounded" style="min-width: 300px;">
        <h4 class="mb-3">Change Password</h4>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= h($error) ?></div>
        <?php elseif ($success): ?>
            <div class="alert alert-success"><?= h($success) ?></div>
        <?php endif; ?>
        <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
        <div class="mb-3">
            <input type="password" name="newpass" placeholder="New Password" required class="form-control" autocomplete="new-password">
        </div>
        <div class="mb-3">
            <input type="password" name="confirm" placeholder="Confirm Password" required class="form-control" autocomplete="new-password">
        </div>
        <button type="submit" class="btn btn-success w-100">Change Password</button>
    </form>
</body>
</html>
