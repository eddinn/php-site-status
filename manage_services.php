<?php
require_once 'init.php';
require_once 'config.php';
require_login();

$error = $success = '';
$services = file_exists(SERVICES_FILE) ? json_decode(file_get_contents(SERVICES_FILE), true) : [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();

    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_group':
                $g = trim($_POST['new_group'] ?? '');
                if ($g && !isset($services[$g])) {
                    $services[$g] = [];
                    $success = "Group '$g' added.";
                } else {
                    $error = "Invalid or duplicate group name.";
                }
                break;
            case 'delete_group':
                $g = $_POST['group'] ?? '';
                if (isset($services[$g])) {
                    unset($services[$g]);
                    $success = "Group '$g' removed.";
                } else {
                    $error = "Group not found.";
                }
                break;
            case 'add_service':
                $g = $_POST['group'] ?? '';
                $u = trim($_POST['url'] ?? '');
                if (isset($services[$g]) && filter_var($u, FILTER_VALIDATE_URL)) {
                    $services[$g][] = $u;
                    $success = "Service added.";
                } else {
                    $error = "Invalid group or URL.";
                }
                break;
            case 'delete_service':
                $g = $_POST['group'] ?? '';
                $i = intval($_POST['index'] ?? -1);
                if (isset($services[$g][$i])) {
                    array_splice($services[$g], $i, 1);
                    $success = "Service removed.";
                } else {
                    $error = "Service not found.";
                }
                break;
            case 'reorder':
                $o = json_decode($_POST['order'] ?? '', true);
                if (is_array($o)) {
                    $new = [];
                    foreach ($o as $grp => $urls) {
                        if (isset($services[$grp]) && is_array($urls)) {
                            $new[$grp] = array_values(array_filter($urls, fn($u) => filter_var($u, FILTER_VALIDATE_URL)));
                        }
                    }
                    $services = $new;
                    $success = "Reordered successfully.";
                } else {
                    $error = "Bad order data.";
                }
                break;
        }

        if (!$error) {
            file_put_contents(SERVICES_FILE, json_encode($services, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Services – Homelab Dashboard</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <meta name="csrf" content="<?=h(csrf_token())?>">
  <link href="assets/styles.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
</head>
<body class="light-mode">
<nav class="navbar navbar-dark bg-primary">
  <div class="container-fluid">
    <a class="navbar-brand" href="index.php">Dashboard</a>
    <span class="navbar-text">Manage Services</span>
  </div>
</nav>

<div class="container py-4">
  <?php if ($error): ?>
    <div class="alert alert-danger"><?=h($error)?></div>
  <?php elseif ($success): ?>
    <div class="alert alert-success"><?=h($success)?></div>
  <?php endif; ?>

  <form method="post" class="mb-4 d-flex gap-2">
    <input type="hidden" name="csrf" value="<?=h(csrf_token())?>">
    <input type="hidden" name="action" value="add_group">
    <input type="text" name="new_group" class="form-control" placeholder="New group name" required>
    <button class="btn btn-primary">➕ Add Group</button>
  </form>

  <div id="group-list">
    <?php foreach ($services as $grp => $urls): ?>
      <div class="card mb-4 group-card" data-group="<?=h($grp)?>">
        <div class="card-header d-flex justify-content-between align-items-center">
          <strong class="drag-handle">≡ <?=h($grp)?></strong>
          <div>
            <a href="edit_service.php?group=<?=urlencode($grp)?>&index=0" class="btn btn-sm btn-outline-secondary me-2" title="Edit group">&#9998;</a>
            <button class="btn btn-sm btn-danger delete-group-btn">🗑️</button>
          </div>
        </div>
        <ul class="list-group list-group-flush service-list">
          <?php foreach ($urls as $i => $u): ?>
            <li class="list-group-item d-flex justify-content-between align-items-center" data-index="<?=$i?>">
              <span><?=h($u)?></span>
              <div class="d-flex gap-2">
                <button class="btn btn-sm btn-secondary edit-service-btn">✏️</button>
                <button class="btn btn-sm btn-danger delete-service-btn">🗑️</button>
              </div>
            </li>
          <?php endforeach;?>
        </ul>
        <form method="post" class="card-body d-flex gap-2">
          <input type="hidden" name="csrf" value="<?=h(csrf_token())?>">
          <input type="hidden" name="action" value="add_service">
          <input type="hidden" name="group" value="<?=h($grp)?>">
          <input type="url" name="url" class="form-control" placeholder="Add service URL" required>
          <button class="btn btn-success">Add</button>
        </form>
      </div>
    <?php endforeach;?>
  </div>

  <button id="save-order" class="btn btn-outline-primary mb-4">💾 Save Order</button>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const csrf = document.querySelector('meta[name=csrf]').content;
  const groupList = document.getElementById('group-list');

  // Draggable groups and services
  Sortable.create(groupList, {
    handle: '.drag-handle',
    animation: 150,
    onEnd: saveOrder
  });
  document.querySelectorAll('.service-list').forEach(el=>{
    Sortable.create(el, {
      animation: 150,
      onEnd: saveOrder
    });
  });

  // Event delegation for edit/delete
  groupList.addEventListener('click', e => {
    const li = e.target.closest('li');
    const card = e.target.closest('.group-card');
    const group = card.dataset.group;

    if (e.target.matches('.delete-service-btn')) {
      if (!confirm('Delete this service?')) return;
      const idx = li.dataset.index;
      postAction({action:'delete_service', group, index: idx});
    }
    if (e.target.matches('.edit-service-btn')) {
      const idx = li.dataset.index;
      window.location = `edit_service.php?group=${encodeURIComponent(group)}&index=${idx}`;
    }
    if (e.target.matches('.delete-group-btn')) {
      if (!confirm('Delete entire group?')) return;
      postAction({action:'delete_group', group});
    }
  });

  document.getElementById('save-order').addEventListener('click', saveOrder);

  function saveOrder() {
    const data = {};
    groupList.querySelectorAll('.group-card').forEach(card => {
      const g = card.dataset.group;
      const urls = [...card.querySelectorAll('.service-list li')].map(li=>li.querySelector('span').textContent);
      data[g] = urls;
    });
    fetch('manage_services.php', {
      method: 'POST',
      headers: {'Content-Type':'application/x-www-form-urlencoded'},
      body: `csrf=${encodeURIComponent(csrf)}&action=reorder&order=${encodeURIComponent(JSON.stringify(data))}`
    }).then(res => location.reload());
  }

  function postAction(obj) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.innerHTML = `
      <input type="hidden" name="csrf" value="${csrf}">
      <input type="hidden" name="action" value="${obj.action}">
      <input type="hidden" name="group" value="${obj.group}">
      ${obj.index ? `<input type="hidden" name="index" value="${obj.index}">` : ''}
    `;
    document.body.appendChild(form);
    form.submit();
  }
});
</script>

</body>
</html>
