<?php
require_once 'init.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Homelab Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/styles.css" rel="stylesheet">
</head>
<body>
<div class="container my-4">
    <?php if (!isset($_SESSION['user'])): ?>
        <div class="alert alert-warning text-center">
            You are not logged in. <a href="login.php" class="btn btn-sm btn-primary ms-2">Log in here</a>
        </div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Homelab Dashboard</h2>
        <div>
            <label class="form-label me-2 mb-0">Auto-refresh:</label>
            <select id="refreshInterval" class="form-select d-inline-block w-auto me-3">
                <option value="0">Off</option>
                <option value="10000">Every 10s</option>
                <option value="30000" selected>Every 30s</option>
                <option value="60000">Every 60s</option>
            </select>
            <button id="darkToggle" class="btn btn-outline-secondary">Toggle Dark Mode</button>
            <button id="offlineToggle" class="btn btn-outline-danger ms-2">Show Only Offline</button>
        </div>
    </div>

    <div class="row" id="dashboard">
        <?php
        $groups = json_decode(file_get_contents('services.json'), true);
        foreach ($groups as $group => $urls) {
            $group_id = preg_replace('/[^a-z0-9]/i', '_', $group);
            echo "<div class='col-md-6 mb-4 group-card' data-group='$group_id'>";
            echo "<div class='card'><div class='card-header d-flex justify-content-between align-items-center'>";
            echo "<span>" . h($group) . "</span><span class='badge bg-secondary' id='{$group_id}_badge'>0 / " . count($urls) . "</span>";
            echo "</div><ul class='list-group list-group-flush'>";
            foreach ($urls as $index => $url) {
                $service_id = "{$group_id}_service_$index";
                echo "<li class='list-group-item service-item' id='{$service_id}' data-group='{$group_id}' data-url='" . h($url) . "'>";
                echo "<span class='status-dot bg-secondary'></span> <strong>Loading...</strong><br>";
                echo "<a href='" . h($url) . "' target='_blank'>" . h($url) . "</a>";
                echo "</li>";
            }
            echo "</ul></div></div>";
        }
        ?>
    </div>
</div>
<script src="assets/script.js"></script>
</body>
</html>
