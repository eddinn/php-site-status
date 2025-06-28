<?php
require_once 'includes/functions.php';

$groups = load_data();
$group_id = $_GET['group_id'] ?? '';
$service_index = isset($_GET['service_index']) ? (int)$_GET['service_index'] : -1;
$csrf_token = $_GET['csrf'] ?? '';

$page_title = "Delete Service";

if (!validate_csrf_token($csrf_token)) {
    set_flash("Invalid CSRF token.", 'danger');
} else {
    $group_index = -1;
    foreach ($groups as $i => $group) {
        if ($group['id'] === $group_id) {
            $group_index = $i;
            break;
        }
    }

    if (
        $group_index !== -1 &&
        isset($groups[$group_index]['services'][$service_index])
    ) {
        array_splice($groups[$group_index]['services'], $service_index, 1);
        save_data($groups);
        set_flash("Service deleted successfully.");
    } else {
        set_flash("Invalid group or service reference.", 'danger');
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
