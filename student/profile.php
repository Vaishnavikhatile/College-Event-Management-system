<?php
require dirname(__DIR__) . '/config.php';
require_student();
$sid = (int)$_SESSION['student_id'];
$me  = fetch_one("SELECT * FROM student WHERE student_id = ?", 'i', [$sid]);
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    if (($_POST['action'] ?? '') === 'profile') {
        $name = trim($_POST['name'] ?? ''); $phone = trim($_POST['phone'] ?? '');
        if (mb_strlen($name) < 3) $errors[] = 'Enter your full name (at least 3 characters).';
        if (!preg_match('/^[0-9]{10}$/', $phone)) $errors[] = 'Phone number must be exactly 10 digits.';
        if (!$errors) { q("UPDATE student SET name = ?, phone = ? WHERE student_id = ?", 'ssi', [$name, $phone, $sid]); $_SESSION['student_name'] = $name; flash('Profile updated.'); redirect(BASE_URL . '/student/profile.php'); }
    } else {
        $new = $_POST['new_password'] ?? '';
        if (!password_verify($_POST['current'] ?? '', $me['password'])) $errors[] = 'Current password is incorrect.';
        if (strlen($new) < 6) $errors[] = 'New password must be at least 6 characters.';
        if ($new !== ($_POST['confirm'] ?? '')) $errors[] = 'New passwords do not match.';
        if (!$errors) { q("UPDATE student SET password = ? WHERE student_id = ?", 'si', [password_hash($new, PASSWORD_DEFAULT), $sid]); flash('Password changed.'); redirect(BASE_URL . '/student/profile.php'); }
    }
}
$pageTitle = 'My profile';
include ROOT_PATH . '/includes/header.php';
?>
<h1 style="font-size:2.2rem">My profile</h1>
<?php if ($errors): ?><ul class="error-list"><?php foreach ($errors as $er) echo '<li>' . e($er) . '</li>'; ?></ul><?php endif; ?>
<div class="form-row" style="align-items:start">
  <form method="post" class="panel"><?= csrf_field() ?><input type="hidden" name="action" value="profile">
    <h3>Details</h3>
    <label for="name">Full name</label><input id="name" name="name" value="<?= e($me['name']) ?>" required>
    <label for="email">Email</label><input id="email" value="<?= e($me['email']) ?>" disabled>
    <label for="phone">Phone</label><input id="phone" name="phone" maxlength="10" value="<?= e($me['phone']) ?>" required>
    <div class="form-actions"><button class="btn">Save changes</button></div>
  </form>
  <form method="post" class="panel"><?= csrf_field() ?><input type="hidden" name="action" value="password">
    <h3>Change password</h3>
    <label for="current">Current password</label><input id="current" type="password" name="current" required>
    <label for="new_password">New password</label><input id="new_password" type="password" name="new_password" minlength="6" required>
    <label for="confirm">Confirm new password</label><input id="confirm" type="password" name="confirm" required>
    <div class="form-actions"><button class="btn btn-ghost">Change password</button></div>
  </form>
</div>
<?php include ROOT_PATH . '/includes/footer.php'; ?>
