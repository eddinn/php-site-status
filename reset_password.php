<?php
require_once 'init.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reset Password – Homelab Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="d-flex justify-content-center align-items-center vh-100 bg-light">
    <div class="p-4 bg-white shadow rounded" style="max-width: 400px;">
        <h4 class="mb-3">Reset Password</h4>
        <p>For security reasons, password reset is only available by editing the <code>users.json</code> file directly on the server.</p>
        <p>If you’ve lost access, SSH into the server and replace the password hash for the user manually.</p>
        <p class="text-muted small">Need help? <code>password_hash("newpass", PASSWORD_DEFAULT)</code> in PHP will give you the hash.</p>
        <a href="login.php" class="btn btn-secondary mt-2 w-100">Back to Login</a>
    </div>
</body>
</html>
