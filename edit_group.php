<?php
require_once 'includes/functions.php';

$groups = load_data();
$group_id = $_GET['id'] ?? '';
$group_index = -1;

foreach ($groups as $i => $group) {
    if ($group['id'] === $group_id) {
        $group_index = $i;
        break;
    }
}

if ($group_index === -1) {
    flash("Group not found.", 'danger');
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        flash("Invalid CSRF token.", 'danger');
        header('Location: index.php');
        exit;
    }

    $new_name = trim($_POST['name'] ?? '');

    if ($new_name === '') {
        flash("Group name cannot be empty.", 'danger');
    } else {
        $groups[$group_index]['name'] = $new_name;
        save_data($groups);
        flash("Group renamed successfully.");
        header('Location: index.php');
        exit;
    }
}

$csrf = generate_csrf_token();
$page_title = "Edit Group";
$current_name = $groups[$group_index]['name'];
require_once 'includes/header.php';
?>

<h3>Edit Service Group</h3>
<form method="POST" class="mt-3">
    <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
    <div class="mb-3">
        <label for="name" class="form-label">Group Name</label>
        <input type="text" name="name" id="name" class="form-control" required value="<?= e($current_name) ?>">
    </div>
    <button type="submit" class="btn btn-primary">Update Group</button>
    <a href="index.php" class="btn btn-secondary">Cancel</a>
</form>

<?php require_once 'includes/footer.php'; ?>
