<?php
require_once 'includes/functions.php';
session_start();

$group_id = $_GET['group_id'] ?? '';
$service_index = $_GET['service_index'] ?? null;
$data = load_data();
$csrf = generate_csrf_token();

if (!isset($data) || !is_numeric($service_index)) {
    flash('Invalid request.', 'danger');
    header("Location: index.php");
    exit;
}

$group = null;
foreach ($data as &$g) {
    if ($g['id'] === $group_id) {
        $group = &$g;
        break;
    }
}

if (!$group || !isset($group['services'][$service_index])) {
    flash('Service not found.', 'danger');
    header("Location: index.php");
    exit;
}

$service = $group['services'][$service_index];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        flash('Invalid CSRF token.', 'danger');
        header("Refresh: 2; URL=index.php");
        exit;
    }

    $title = trim($_POST['title'] ?? '');
    $url = trim($_POST['url'] ?? '');

    if ($title === '' || $url === '') {
        flash('Title and URL are required.', 'danger');
    } else {
        $group['services'][$service_index] = [
            'title' => $title,
            'url' => $url
        ];
        save_data($data);
        flash('Service updated successfully.', 'success');
        header("Location: index.php");
        exit;
    }
}

$page_title = "Edit Service";
require_once 'includes/header.php';
?>

<h2>Edit Service</h2>

<form method="post" class="mt-4">
    <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
    <input type="hidden" name="group_id" value="<?= e($group_id) ?>">
    <input type="hidden" name="service_index" value="<?= e($service_index) ?>">

    <div class="mb-3">
        <label for="title" class="form-label">Service Name</label>
        <input type="text" class="form-control" id="title" name="title" required value="<?= e($service['title']) ?>">
    </div>

    <div class="mb-3">
        <label for="url" class="form-label">Service URL</label>
        <input type="url" class="form-control" id="url" name="url" required value="<?= e($service['url']) ?>">
    </div>

    <button type="submit" class="btn btn-primary">Update Service</button>
    <a href="index.php" class="btn btn-secondary">Cancel</a>
</form>

<?php require_once 'includes/footer.php'; ?>
