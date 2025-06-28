<?php
require_once 'includes/functions.php';

$groups = load_data();
$group_id = $_GET['id'] ?? '';
$csrf_token = $_GET['csrf'] ?? '';

$page_title = "Delete Group";

if (!validate_csrf_token($csrf_token)) {
    flash("Invalid CSRF token.", 'danger');
} else {
    $updated_groups = [];
    $found = false;

    foreach ($groups as $group) {
        if ($group['id'] === $group_id) {
            $found = true;
            continue;
        }
        $updated_groups[] = $group;
    }

    if ($found) {
        save_data($updated_groups);
        flash("Group deleted successfully.");
    } else {
        flash("Group not found.", 'warning');
    }
}

require_once 'includes/header.php';
?>

<div class="alert-container">
    <?= show_flash() ?>
    <p class="text-center">Redirecting to dashboard...</p>
</div>

<meta http-equiv="refresh" content="2;url=index.php">

<?php require_once 'includes/footer.php'; ?>
