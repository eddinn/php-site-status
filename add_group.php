<?php
require_once 'includes/functions.php';
session_start();

$data = load_data();
$csrf = generate_csrf_token();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        flash('Invalid CSRF token.', 'danger');
        header("Refresh: 2; URL=index.php");
        exit;
    }

    $name = trim($_POST['name'] ?? '');
    if ($name === '') {
        flash('Group name is required.', 'danger');
    } else {
        $new_group = [
            'id' => 'group' . uniqid(),
            'name' => $name,
            'services' => []
        ];
        $data[] = $new_group;
        save_data($data);
        flash('Group added successfully.', 'success');
        header('Location: index.php');
        exit;
    }
}

$page_title = "Add Group";
require_once 'includes/header.php';
?>

<h2>Add New Service Group</h2>

<form method="post" class="mt-4">
    <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">

    <div class="mb-3">
        <label for="name" class="form-label">Group Name</label>
        <input type="text" class="form-control" id="name" name="name" required>
    </div>

    <button type="submit" class="btn btn-primary">Create Group</button>
    <a href="index.php" class="btn btn-secondary">Cancel</a>
</form>

<?php require_once 'includes/footer.php'; ?>
