<?php
require_once 'includes/functions.php';
session_start();

$data = load_data();
$group_id = $_GET['id'] ?? '';
$csrf = generate_csrf_token();

$group = null;
foreach ($data as &$g) {
    if ($g['id'] === $group_id) {
        $group = &$g;
        break;
    }
}

if (!$group) {
    flash('Group not found.', 'danger');
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        flash('Invalid CSRF token.', 'danger');
        header("Refresh: 2; URL=index.php");
        exit;
    }

    $name = trim($_POST['name'] ?? '');
    if ($name === '') {
        flash('Group name cannot be empty.', 'danger');
    } else {
        $group['name'] = $name;
        save_data($data);
        flash('Group name updated.', 'success');
        header('Location: index.php');
        exit;
    }
}

$page_title = "Edit Group";
require_once 'includes/header.php';
?>

<h2>Edit Service Group</h2>

<form method="post" class="mt-4">
    <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
    <div class="mb-3">
        <label for="name" class="form-label">Group Name</label>
        <input type="text" class="form-control" id="name" name="name" value="<?= e($group['name']) ?>" required>
    </div>

    <button type="submit" class="btn btn-primary">Update Group</button>
    <a href="index.php" class="btn btn-secondary">Cancel</a>
</form>

<?php require_once 'includes/footer.php'; ?>
