<?php
require_once 'includes/functions.php';
session_start();

$group_id = $_GET['group_id'] ?? '';
$data = load_data();
$csrf = generate_csrf_token();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        flash('Invalid CSRF token.', 'danger');
        header("Refresh: 2; URL=index.php");
        exit;
    }

    $title = trim($_POST['title'] ?? '');
    $url = trim($_POST['url'] ?? '');
    $group_id = trim($_POST['group_id'] ?? '');

    if ($title === '' || $url === '') {
        flash('Title and URL are required.', 'danger');
    } else {
        foreach ($data as &$group) {
            if ($group['id'] === $group_id) {
                $group['services'][] = [
                    'title' => $title,
                    'url' => $url
                ];
                save_data($data);
                flash('Service added successfully.', 'success');
                header("Location: index.php");
                exit;
            }
        }
        flash('Group not found.', 'danger');
    }
}

$page_title = "Add Service";
require_once 'includes/header.php';
?>

<h2>Add Service</h2>

<form method="post" class="mt-4">
    <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
    <input type="hidden" name="group_id" value="<?= e($group_id) ?>">

    <div class="mb-3">
        <label for="title" class="form-label">Service Name</label>
        <input type="text" class="form-control" id="title" name="title" required>
    </div>

    <div class="mb-3">
        <label for="url" class="form-label">Service URL</label>
        <input type="url" class="form-control" id="url" name="url" required placeholder="http://example.local:port">
    </div>

    <button type="submit" class="btn btn-primary">Add Service</button>
    <a href="index.php" class="btn btn-secondary">Cancel</a>
</form>

<?php require_once 'includes/footer.php'; ?>
