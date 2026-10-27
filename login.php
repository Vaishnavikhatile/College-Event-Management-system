<?php
require __DIR__ . '/config.php';
$pageTitle = 'Student login';
if (!empty($_SESSION['student_id'])) redirect(BASE_URL . '/student/my_events.php');
$error = ''; $email = '';
$redirect = trim($_GET['redirect'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $email = strtolower(trim($_POST['email'] ?? ''));
    $st = fetch_one("SELECT * FROM student WHERE email = ?", 's', [$email]);
    if ($st && password_verify($_POST['password'] ?? '', $st['password'])) {
        session_regenerate_id(true);
        unset($_SESSION['admin_id'], $_SESSION['admin_name']);
        $_SESSION['student_id'] = (int)$st['student_id'];
        $_SESSION['student_name'] = $st['name'];
        flash('Welcome back, ' . $st['name'] . '!');
        if ($redirect !== '' && strpos($redirect, '/') === false) {
            redirect(BASE_URL . '/' . $redirect);
        }
        redirect(BASE_URL . '/student/my_events.php');
    }
    $error = 'Email or password is incorrect.';
}
include ROOT_PATH . '/includes/header.php';
?>

<div class="panel form-narrow">
  <div class="form-title-wrap">
    <h2>Student Login</h2>
    <p class="muted">Access your registered events and digital event passes</p>
  </div>

  <?php if ($error): ?><ul class="error-list"><li><?= e($error) ?></li></ul><?php endif; ?>

  <form method="post" id="student-login-form">
    <?= csrf_field() ?>
    <label for="email">Student College Email</label>
    <input id="email" type="email" name="email" value="<?= e($email) ?>" placeholder="e.g. rohit.shinde@cocsit.edu" required autofocus>
    
    <label for="password">Password</label>
    <input id="password" type="password" name="password" placeholder="••••••••" required>
    
    <div class="form-actions">
      <button class="btn btn-gold btn-block">Log In &rarr;</button>
    </div>
  </form>

  <!-- Interactive Demo Autofill Box -->
  <div class="demo-login-box">
    <div class="demo-title">
      <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
      Quick Demo Login (1-Click)
    </div>
    <div class="demo-buttons">
      <button type="button" class="btn btn-tiny btn-ghost js-demo-fill" 
              data-email="rohit.shinde@cocsit.edu" data-pass="student123">
        Student: Rohit Shinde
      </button>
      <button type="button" class="btn btn-tiny btn-ghost js-demo-fill" 
              data-email="ananya.deshmukh@cocsit.edu" data-pass="student123">
        Student: Ananya Deshmukh
      </button>
    </div>
  </div>

  <div class="form-bottom-links">
    <p class="hint">Don't have an account? <a href="<?= BASE_URL ?>/register.php">Create student account</a>.</p>
    <p class="hint">Administrator? <a href="<?= BASE_URL ?>/admin/login.php">Admin login</a>.</p>
  </div>
</div>

<?php include ROOT_PATH . '/includes/footer.php'; ?>
