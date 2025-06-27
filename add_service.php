<?php
require_once 'includes/functions.php';

$groups = load_data();
$group_id = $_GET['group_id'] ?? '';
$group_index = -1;

foreach ($groups as $i => $group) {
    if ($group['id'] === $group_id) {
        $group_index = $i;
        break;
    }
}

if ($group_index === -1) {
    set_flash("Invalid group ID.", 'danger');
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        set_flash("Invalid CSRF token.", 'danger');
        header("Location: index.php");
        exit;
    }

    $url = trim($_POST['url'] ?? '');

    if (!is_valid_url($url)) {
        set_flash("Invalid service URL.", 'danger');
    } else {
        $groups[$group_index]['services'][] = ['url' => $url];
        save_data($groups);
        set_flash("Service added successfully.");
        header('Location: index.php');
        exit;
    }
}

$csrf = generate_csrf_token();
$page_title = "Add Service";
$group_name = $groups[$group_index]['name'];
require_once 'includes/header.php';
?>

<h3>Add Service to <em><?= e($group_name) ?></em></h3>
<form method="POST" class="mt-3">
    <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
    <div class="mb-3">
        <label for="url" class="form-label">Service URL</label>
        <input type="text" name="url" id="url" class="form-control" required>
    </div>
    <button type="submit" class="btn btn-primary">Add Service</button>
    <a href="index.php" class="btn btn-secondary">Cancel</a>
</form>

<?php require_once 'includes/footer.php'; ?>
