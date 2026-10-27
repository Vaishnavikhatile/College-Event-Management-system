<?php
require dirname(__DIR__) . '/config.php';
$pageTitle = 'Admin login';
if (!empty($_SESSION['admin_id'])) redirect(BASE_URL . '/admin/dashboard.php');
$noAdmin = (int)fetch_one("SELECT COUNT(*) c FROM admin")['c'] === 0;   // first run: let the first admin be created
$errors = []; $email = ''; $name = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $email = strtolower(trim($_POST['email'] ?? ''));
    if ($noAdmin && ($_POST['action'] ?? '') === 'setup') {
        $name = trim($_POST['name'] ?? ''); $pw = $_POST['password'] ?? '';
        if (mb_strlen($name) < 3) $errors[] = 'Enter the admin name.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Enter a valid email address.';
        if (strlen($pw) < 8) $errors[] = 'Password must be at least 8 characters.';
        if (!$errors) {
            q("INSERT INTO admin (name, email, password) VALUES (?,?,?)", 'sss', [$name, $email, password_hash($pw, PASSWORD_DEFAULT)]);
            flash('Admin account created. Log in below.');
            redirect(BASE_URL . '/admin/login.php');
        }
    } else {
        $ad = fetch_one("SELECT * FROM admin WHERE email = ?", 's', [$email]);
        if ($ad && password_verify($_POST['password'] ?? '', $ad['password'])) {
            session_regenerate_id(true);
            unset($_SESSION['student_id'], $_SESSION['student_name']);
            $_SESSION['admin_id'] = (int)$ad['admin_id'];
            $_SESSION['admin_name'] = $ad['name'];
            redirect(BASE_URL . '/admin/dashboard.php');
        }
        $errors[] = 'Email or password is incorrect.';
    }
}
$allAdmins = fetch_all("SELECT admin_id, name, email FROM admin ORDER BY admin_id");
include ROOT_PATH . '/includes/header.php';
?>
<div class="panel form-narrow">
  <?php if ($noAdmin): ?>
    <h2>Create the first admin</h2>
    <p class="muted">No admin exists yet. This form disappears once the first admin is created.</p>
  <?php else: ?><h2>Admin login</h2><?php endif; ?>
  <?php if ($errors): ?><ul class="error-list"><?php foreach ($errors as $er) echo '<li>' . e($er) . '</li>'; ?></ul><?php endif; ?>
  <form method="post"><?= csrf_field() ?>
    <?php if ($noAdmin): ?><input type="hidden" name="action" value="setup">
      <label for="name">Name</label><input id="name" name="name" value="<?= e($name) ?>" required><?php endif; ?>
    <label for="email">Admin Email</label><input id="email" type="email" name="email" value="<?= e($email) ?>" placeholder="e.g. vaishnavikhatile@gmail.com" required>
    <label for="password">Password<?= $noAdmin ? ' (8+ characters)' : '' ?></label><input id="password" type="password" name="password" placeholder="••••••••" required>
    <div class="form-actions"><button class="btn btn-gold btn-block"><?= $noAdmin ? 'Create admin' : 'Log In &rarr;' ?></button></div>
  </form>
  <?php if (!$noAdmin && $allAdmins): ?>
    <div class="demo-login-box">
      <div class="demo-title">
        <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
        Admin Accounts (1-Click Login)
      </div>
      <div class="demo-buttons" style="display:flex;flex-direction:column;gap:6px;">
        <?php foreach ($allAdmins as $adRow): ?>
          <button type="button" class="btn btn-tiny btn-ghost js-demo-fill" 
                  data-email="<?= e($adRow['email']) ?>" data-pass="admin123"
                  style="justify-content:space-between;text-align:left;width:100%;padding:8px 12px;">
            <span><strong><?= e($adRow['name']) ?></strong></span>
            <span class="muted small"><?= e($adRow['email']) ?></span>
          </button>
        <?php endforeach; ?>
      </div>
      <p class="hint" style="margin-top:8px;font-size:0.75rem;">Password for all demo admins: <code>admin123</code></p>
    </div>
  <?php endif; ?>
  <div class="form-bottom-links">
    <p class="hint">Student? <a href="<?= BASE_URL ?>/login.php">Student login portal</a>.</p>
  </div>
</div>
<?php include ROOT_PATH . '/includes/footer.php'; ?>
