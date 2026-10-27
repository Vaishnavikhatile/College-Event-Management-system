<?php
require __DIR__ . '/config.php';
$pageTitle = 'Create account';
$errors = []; $v = ['name' => '', 'email' => '', 'phone' => ''];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $v['name'] = trim($_POST['name'] ?? ''); $v['email'] = strtolower(trim($_POST['email'] ?? '')); $v['phone'] = trim($_POST['phone'] ?? '');
    $pw = $_POST['password'] ?? ''; $cf = $_POST['confirm'] ?? '';
    if (mb_strlen($v['name']) < 3) $errors[] = 'Enter your full name (at least 3 characters).';
    if (!filter_var($v['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Enter a valid email address.';
    if (!preg_match('/^[0-9]{10}$/', $v['phone'])) $errors[] = 'Phone number must be exactly 10 digits.';
    if (strlen($pw) < 6) $errors[] = 'Password must be at least 6 characters.';
    if ($pw !== $cf) $errors[] = 'Passwords do not match.';
    if (!$errors && fetch_one("SELECT student_id FROM student WHERE email = ?", 's', [$v['email']])) $errors[] = 'An account with this email already exists. Log in instead.';
    if (!$errors) {
        q("INSERT INTO student (name, email, password, phone) VALUES (?,?,?,?)", 'ssss', [$v['name'], $v['email'], password_hash($pw, PASSWORD_DEFAULT), $v['phone']]);
        flash('Account created. You can log in now.');
        redirect(BASE_URL . '/login.php');
    }
}
include ROOT_PATH . '/includes/header.php';
?>
<div class="panel form-narrow">
  <h2>Create student account</h2>
  <?php if ($errors): ?><ul class="error-list"><?php foreach ($errors as $er) echo '<li>' . e($er) . '</li>'; ?></ul><?php endif; ?>
  <form method="post" novalidate>
    <?= csrf_field() ?>
    <label for="name">Full name</label><input id="name" name="name" value="<?= e($v['name']) ?>" required>
    <label for="email">Email</label><input id="email" type="email" name="email" value="<?= e($v['email']) ?>" required>
    <label for="phone">Phone (10 digits)</label><input id="phone" name="phone" inputmode="numeric" maxlength="10" value="<?= e($v['phone']) ?>" required>
    <label for="password">Password</label><input id="password" type="password" name="password" minlength="6" required>
    <label for="confirm">Confirm password</label><input id="confirm" type="password" name="confirm" required>
    <div class="form-actions"><button class="btn">Create account</button></div>
  </form>
  <p class="hint">Already registered? <a href="<?= BASE_URL ?>/login.php">Log in</a></p>
</div>
<?php include ROOT_PATH . '/includes/footer.php'; ?>
