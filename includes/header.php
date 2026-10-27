<?php
$pageTitle = $pageTitle ?? 'College Event Management System';
$isAdmin   = !empty($_SESSION['admin_id']);
$isStudent = !empty($_SESSION['student_id']);
$self      = basename($_SERVER['SCRIPT_NAME']);
$inAdmin   = strpos(str_replace('\\', '/', $_SERVER['SCRIPT_NAME']), '/admin/') !== false;

function nav_link(string $href, string $label, string $file): string {
    global $self;
    $cur = ($self === $file) ? ' aria-current="page"' : '';
    return '<a href="' . BASE_URL . $href . '"' . $cur . '>' . e($label) . '</a>';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($pageTitle) ?> | COCSIT Event Portal</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Bricolage+Grotesque:wght@600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body class="<?= $inAdmin ? 'area-admin' : 'area-public' ?>">
<header class="site-header">
  <div class="wrap bar">
    <a class="brand" href="<?= BASE_URL ?>/index.php">
      <span class="brand-mark">C</span>
      <div>
        <span class="brand-title">COCSIT Events</span>
        <small class="brand-sub"><?= $inAdmin ? 'Admin Control Center' : 'Campus Event Portal &bull; Latur' ?></small>
      </div>
    </a>
    <button class="nav-toggle" aria-label="Toggle navigation menu" aria-expanded="false">
      <span></span><span></span><span></span>
    </button>
    <nav class="nav" id="nav">
      <?php if ($inAdmin && $isAdmin): ?>
        <?= nav_link('/admin/dashboard.php', 'Dashboard', 'dashboard.php') ?>
        <?= nav_link('/admin/events.php', 'Events', 'events.php') ?>
        <?= nav_link('/admin/registrations.php', 'Registrations', 'registrations.php') ?>
        <?= nav_link('/admin/attendance.php', 'Attendance', 'attendance.php') ?>
        <?= nav_link('/admin/reports.php', 'Reports', 'reports.php') ?>
        <?= nav_link('/admin/admins.php', 'Admin Management', 'admins.php') ?>
        <a href="<?= BASE_URL ?>/index.php" target="_blank" class="nav-preview" title="View public site">Public Site &nearr;</a>
        <a href="<?= BASE_URL ?>/logout.php" class="nav-logout">Log Out (<?= e($_SESSION['admin_name'] ?? 'Admin') ?>)</a>
      <?php else: ?>
        <?= nav_link('/index.php', 'Home', 'index.php') ?>
        <?= nav_link('/events.php', 'Browse Events', 'events.php') ?>
        <?php if ($isAdmin): ?>
          <?= nav_link('/admin/dashboard.php', 'Admin Dashboard', 'dashboard.php') ?>
          <?= nav_link('/admin/admins.php', 'Admin Management', 'admins.php') ?>
          <a href="<?= BASE_URL ?>/logout.php" class="nav-logout">Log Out (<?= e($_SESSION['admin_name'] ?? 'Admin') ?>)</a>
        <?php elseif ($isStudent): ?>
          <?= nav_link('/student/my_events.php', 'My Events', 'my_events.php') ?>
          <?= nav_link('/student/profile.php', 'My Profile', 'profile.php') ?>
          <a href="<?= BASE_URL ?>/logout.php" class="nav-logout">Log Out (<?= e(explode(' ', $_SESSION['student_name'] ?? 'Student')[0]) ?>)</a>
        <?php else: ?>
          <?= nav_link('/login.php', 'Student Login', 'login.php') ?>
          <?= nav_link('/register.php', 'Register Account', 'register.php') ?>
          <a class="nav-admin" href="<?= BASE_URL ?>/admin/login.php">Admin Portal</a>
        <?php endif; ?>
      <?php endif; ?>
    </nav>
  </div>
</header>
<main class="wrap main">
<?php show_flash(); ?>
