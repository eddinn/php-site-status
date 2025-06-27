<?php
require_once 'includes/functions.php';

$groups = load_data();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        set_flash("Invalid CSRF token.", 'danger');
        header('Location: index.php');
        exit;
    }

    $name = trim($_POST['name'] ?? '');

    if ($name === '') {
        set_flash("Group name cannot be empty.", 'danger');
    } else {
        $groups[] = [
            'id' => uniqid('group'),
            'name' => $name,
            'services' => []
        ];
        save_data($groups);
        set_flash("Group \"$name\" added successfully.");
        header('Location: index.php');
        exit;
    }
}

$csrf = generate_csrf_token();
$page_title = "Add Group";
require_once 'includes/header.php';
?>

<h3>Add New Service Group</h3>
<form method="POST" class="mt-3">
    <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
    <div class="mb-3">
        <label for="name" class="form-label">Group Name</label>
        <input type="text" name="name" id="name" class="form-control" required>
    </div>
    <button type="submit" class="btn btn-success">Create Group</button>
    <a href="index.php" class="btn btn-secondary">Cancel</a>
</form>

<?php require_once 'includes/footer.php'; ?>
