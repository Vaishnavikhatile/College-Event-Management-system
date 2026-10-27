<?php
require dirname(__DIR__) . '/config.php';
require_admin();
$pageTitle = 'Dashboard';
$c = fetch_one("SELECT
  (SELECT COUNT(*) FROM events) AS events,
  (SELECT COUNT(*) FROM events WHERE event_date >= CURDATE()) AS upcoming,
  (SELECT COUNT(*) FROM student) AS students,
  (SELECT COUNT(*) FROM registrations) AS regs");

$recent = fetch_all("SELECT s.name, s.email, e.event_name, e.category, r.registration_date 
  FROM registrations r
  JOIN student s ON s.student_id = r.student_id 
  JOIN events e ON e.event_id = r.event_id
  ORDER BY r.registration_date DESC LIMIT 10");

include ROOT_PATH . '/includes/header.php';
?>
<div class="section-head">
  <div>
    <h1 style="font-size:2.2rem;margin:0">Admin Control Center</h1>
    <p class="section-subtitle">Welcome back, <?= e($_SESSION['admin_name']) ?> &bull; COCSIT Campus Events System</p>
  </div>
  <div style="display:flex;gap:10px;flex-wrap:wrap;">
    <a class="btn btn-gold" href="<?= BASE_URL ?>/admin/event_form.php">+ Add New Event</a>
    <a class="btn btn-ghost" href="<?= BASE_URL ?>/admin/admins.php">Admin Management</a>
    <a class="btn btn-ghost" href="<?= BASE_URL ?>/admin/attendance.php">Attendance Sheet</a>
    <a class="btn btn-ghost" href="<?= BASE_URL ?>/admin/reports.php">Reports &amp; Export</a>
  </div>
</div>

<div class="stats">
  <div class="stat">
    <b><?= (int)$c['events'] ?></b>
    <span>Total Campus Events</span>
  </div>
  <div class="stat gold">
    <b><?= (int)$c['upcoming'] ?></b>
    <span>Active Upcoming Events</span>
  </div>
  <div class="stat">
    <b><?= (int)$c['students'] ?></b>
    <span>Registered Students</span>
  </div>
  <div class="stat gold">
    <b><?= (int)$c['regs'] ?></b>
    <span>Total Registrations</span>
  </div>
</div>

<div class="section-head" style="margin-top:32px;">
  <h2>Recent Student Registrations</h2>
  <div class="toolbar-search-wrap" style="max-width:320px;">
    <input type="text" data-filter="#reg-table" placeholder="Filter recent entries..." aria-label="Filter registrations">
  </div>
</div>

<?php if ($recent): ?>
<div class="table-wrap">
  <table id="reg-table">
    <thead>
      <tr>
        <th>Student Name</th>
        <th>College Email</th>
        <th>Category</th>
        <th>Event Name</th>
        <th>Registered At</th>
      </tr>
    </thead>
    <tbody>
    <?php foreach ($recent as $r): 
      $cat = $r['category'] ?? 'Technical';
      $catSlug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $cat));
    ?>
      <tr>
        <td><strong><?= e($r['name']) ?></strong></td>
        <td><?= e($r['email']) ?></td>
        <td><span class="category-badge cat-<?= e($catSlug) ?>"><?= e($cat) ?></span></td>
        <td><?= e($r['event_name']) ?></td>
        <td><?= e(date('d M Y, h:i A', strtotime($r['registration_date']))) ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php else: ?>
  <div class="empty">No registrations yet.</div>
<?php endif; ?>

<?php include ROOT_PATH . '/includes/footer.php'; ?>
