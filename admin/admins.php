<?php
require dirname(__DIR__) . '/config.php';
require_admin();

$errors = [];
$myId = (int)$_SESSION['admin_id'];

// 1. Handle Delete Action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    csrf_verify();
    $delId = (int)($_POST['admin_id'] ?? 0);
    $totalAdmins = (int)fetch_one("SELECT COUNT(*) c FROM admin")['c'];

    if ($delId === $myId) {
        flash('You cannot delete your own currently logged-in account.', 'error');
    } elseif ($totalAdmins <= 1) {
        flash('Cannot delete the only remaining admin account.', 'error');
    } else {
        $target = fetch_one("SELECT name FROM admin WHERE admin_id = ?", 'i', [$delId]);
        if ($target) {
            q("DELETE FROM admin WHERE admin_id = ?", 'i', [$delId]);
            flash('Administrator "' . $target['name'] . '" has been removed.');
        } else {
            flash('Admin not found.', 'error');
        }
    }
    redirect(BASE_URL . '/admin/admins.php');
}

// 2. Handle Create / Edit Action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (($_POST['action'] ?? '') === 'create' || ($_POST['action'] ?? '') === 'edit')) {
    csrf_verify();
    $act = $_POST['action'];
    $editId = (int)($_POST['admin_id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm'] ?? '';

    if (mb_strlen($name) < 3) $errors[] = 'Admin name must be at least 3 characters.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Enter a valid email address.';

    // Check email uniqueness
    $exists = fetch_one("SELECT admin_id FROM admin WHERE email = ? AND admin_id != ?", 'si', [$email, $editId]);
    if ($exists) $errors[] = 'An admin account with that email already exists.';

    if ($act === 'create') {
        if (strlen($password) < 6) $errors[] = 'Password must be at least 6 characters.';
        if ($password !== $confirm) $errors[] = 'Passwords do not match.';

        if (!$errors) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            q("INSERT INTO admin (name, email, password) VALUES (?, ?, ?)", 'sss', [$name, $email, $hash]);
            flash('New administrator "' . $name . '" created successfully.');
            redirect(BASE_URL . '/admin/admins.php');
        }
    } elseif ($act === 'edit') {
        if ($password !== '') {
            if (strlen($password) < 6) $errors[] = 'New password must be at least 6 characters.';
            if ($password !== $confirm) $errors[] = 'Passwords do not match.';
        }

        if (!$errors) {
            if ($password !== '') {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                q("UPDATE admin SET name = ?, email = ?, password = ? WHERE admin_id = ?", 'sssi', [$name, $email, $hash, $editId]);
            } else {
                q("UPDATE admin SET name = ?, email = ? WHERE admin_id = ?", 'ssi', [$name, $email, $editId]);
            }
            if ($editId === $myId) $_SESSION['admin_name'] = $name;
            flash('Admin profile updated.');
            redirect(BASE_URL . '/admin/admins.php');
        }
    }
}

$pageTitle = 'Manage Administrators';
$admins = fetch_all("SELECT a.admin_id, a.name, a.email, 
                     (SELECT COUNT(*) FROM events e WHERE e.admin_id = a.admin_id) AS events_created
                     FROM admin a ORDER BY a.admin_id ASC");

$editRow = null;
if (!empty($_GET['edit'])) {
    $editRow = fetch_one("SELECT admin_id, name, email FROM admin WHERE admin_id = ?", 'i', [(int)$_GET['edit']]);
}

include ROOT_PATH . '/includes/header.php';
?>

<div class="section-head">
  <div>
    <h1 style="font-size:2.2rem;margin:0">Manage Administrators</h1>
    <p class="section-subtitle">Create new administrators, manage faculty credentials, and oversee staff access</p>
  </div>
  <?php if (!$editRow): ?>
    <button type="button" class="btn btn-gold" onclick="document.getElementById('add-admin-panel').scrollIntoView({behavior:'smooth'});document.getElementById('new-name').focus();">+ Add New Admin</button>
  <?php endif; ?>
</div>

<?php if ($errors): ?>
  <ul class="error-list">
    <?php foreach ($errors as $er) echo '<li>' . e($er) . '</li>'; ?>
  </ul>
<?php endif; ?>

<!-- Edit Admin Panel -->
<?php if ($editRow): ?>
<div class="panel form-wide" style="margin-bottom:32px;border-left:5px solid var(--blue);">
  <h2>Edit Administrator: <?= e($editRow['name']) ?></h2>
  <form method="post">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="edit">
    <input type="hidden" name="admin_id" value="<?= (int)$editRow['admin_id'] ?>">
    
    <div class="form-row">
      <div>
        <label for="edit-name">Full Name</label>
        <input id="edit-name" name="name" value="<?= e($editRow['name']) ?>" required>
      </div>
      <div>
        <label for="edit-email">Admin Email</label>
        <input id="edit-email" type="email" name="email" value="<?= e($editRow['email']) ?>" required>
      </div>
    </div>

    <div class="form-row">
      <div>
        <label for="edit-password">New Password <span class="muted small">(leave blank to keep current)</span></label>
        <input id="edit-password" type="password" name="password" placeholder="••••••••">
      </div>
      <div>
        <label for="edit-confirm">Confirm New Password</label>
        <input id="edit-confirm" type="password" name="confirm" placeholder="••••••••">
      </div>
    </div>

    <div class="form-actions" style="display:flex;gap:12px;">
      <button class="btn btn-gold">Save Changes</button>
      <a class="btn btn-ghost" href="<?= BASE_URL ?>/admin/admins.php">Cancel</a>
    </div>
  </form>
</div>
<?php endif; ?>

<!-- Administrators Table -->
<div class="toolbar-enhanced">
  <div class="toolbar-search-wrap">
    <svg class="search-icon" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
    <input type="text" data-filter="#admins-table" placeholder="Filter administrators by name or email..." aria-label="Filter administrators">
  </div>
  <span class="muted small">Total: <strong><?= count($admins) ?></strong> Administrators</span>
</div>

<div class="table-wrap" style="margin-bottom:40px;">
  <table id="admins-table">
    <thead>
      <tr>
        <th style="width:70px;">ID</th>
        <th>Admin Name</th>
        <th>Email Address</th>
        <th>Events Managed</th>
        <th>Role / Status</th>
        <th style="text-align:right">Actions</th>
      </tr>
    </thead>
    <tbody>
    <?php foreach ($admins as $ad): 
      $isMe = (int)$ad['admin_id'] === $myId;
    ?>
      <tr>
        <td>#<?= (int)$ad['admin_id'] ?></td>
        <td>
          <strong><?= e($ad['name']) ?></strong>
          <?php if ($isMe): ?>
            <span class="badge badge-ok" style="margin-left:6px;">Current Account</span>
          <?php endif; ?>
        </td>
        <td><?= e($ad['email']) ?></td>
        <td>
          <span class="badge badge-muted"><?= (int)$ad['events_created'] ?> Events</span>
        </td>
        <td>
          <span class="status-pill status-registered">Active Admin</span>
        </td>
        <td class="actions" style="text-align:right">
          <a class="btn btn-tiny btn-ghost" href="<?= BASE_URL ?>/admin/admins.php?edit=<?= (int)$ad['admin_id'] ?>">Edit / Reset</a>

          <?php if (!$isMe): ?>
            <form method="post" data-confirm="Are you sure you want to delete administrator '<?= e($ad['name']) ?>'?">
              <?= csrf_field() ?>
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="admin_id" value="<?= (int)$ad['admin_id'] ?>">
              <button class="btn btn-tiny btn-danger">Delete</button>
            </form>
          <?php else: ?>
            <button class="btn btn-tiny btn-ghost" disabled title="You cannot delete your own logged-in account" style="opacity:0.5;cursor:not-allowed;">Delete</button>
          <?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>

<!-- Create New Administrator Form -->
<div class="panel form-wide" id="add-admin-panel" style="border-top:4px solid var(--gold);">
  <div class="form-title-wrap" style="text-align:left;margin-bottom:18px;">
    <h2 style="font-size:1.4rem;margin-bottom:4px;">+ Create New Administrator Account</h2>
    <p class="section-subtitle">Authorize a new faculty coordinator, committee member, or event convener</p>
  </div>

  <form method="post">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="create">

    <div class="form-row">
      <div>
        <label for="new-name">Full Name</label>
        <input id="new-name" name="name" placeholder="e.g. Prof. Rajesh Sharma" required>
      </div>
      <div>
        <label for="new-email">Official / College Email</label>
        <input id="new-email" type="email" name="email" placeholder="e.g. rajesh.sharma@cocsit.edu" required>
      </div>
    </div>

    <div class="form-row">
      <div>
        <label for="new-password">Password (6+ characters)</label>
        <input id="new-password" type="password" name="password" placeholder="••••••••" required>
      </div>
      <div>
        <label for="new-confirm">Confirm Password</label>
        <input id="new-confirm" type="password" name="confirm" placeholder="••••••••" required>
      </div>
    </div>

    <div class="form-actions" style="margin-top:20px;">
      <button class="btn btn-gold btn-large">Create Administrator Account &rarr;</button>
    </div>
  </form>
</div>

<?php include ROOT_PATH . '/includes/footer.php'; ?>
