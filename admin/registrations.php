<?php
require dirname(__DIR__) . '/config.php';
require_admin();
$pageTitle = 'Registrations';
$events = fetch_all("SELECT event_id, event_name, event_date FROM events ORDER BY event_date DESC");
$eid = (int)($_GET['event_id'] ?? 0);
$ev = $eid ? fetch_one("SELECT * FROM events WHERE event_id = ?", 'i', [$eid]) : null;
$rows = $ev ? fetch_all("SELECT r.registration_id, r.registration_date, s.name, s.email, s.phone, a.status
    FROM registrations r JOIN student s ON s.student_id = r.student_id
    LEFT JOIN attendance a ON a.registration_id = r.registration_id
    WHERE r.event_id = ? ORDER BY r.registration_date DESC", 'i', [$eid]) : [];
include ROOT_PATH . '/includes/header.php';
?>
<div class="section-head">
  <div>
    <h1 style="font-size:2.2rem;margin:0">Event Registrations</h1>
    <p class="section-subtitle">View and search students who signed up for campus activities</p>
  </div>
</div>

<form method="get" class="toolbar-enhanced">
  <div class="toolbar-search-wrap" style="max-width:440px;">
    <select id="event_id" name="event_id" onchange="this.form.submit()">
      <option value="0">Choose an event to inspect...</option>
      <?php foreach ($events as $o): ?>
        <option value="<?= (int)$o['event_id'] ?>"<?= $o['event_id'] == $eid ? ' selected' : '' ?>>
          <?= e($o['event_name']) ?> (<?= e(fmt_date($o['event_date'])) ?>)
        </option>
      <?php endforeach; ?>
    </select>
  </div>
  <noscript><button class="btn">Show</button></noscript>
</form>

<?php if (!$ev): ?>
  <div class="empty">Choose an event from the dropdown above to view student registrations.</div>
<?php elseif (!$rows): ?>
  <div class="empty">No students have registered for <strong><?= e($ev['event_name']) ?></strong> yet.</div>
<?php else: ?>
  <div class="section-head" style="margin-top:20px;">
    <div>
      <h2><?= e($ev['event_name']) ?></h2>
      <span class="badge badge-ok"><?= count($rows) ?> Enrolled Students</span>
    </div>
    <div style="display:flex;gap:10px;">
      <a class="btn btn-gold" href="<?= BASE_URL ?>/admin/attendance.php?event_id=<?= $eid ?>">Mark Attendance</a>
      <a class="btn btn-ghost" href="<?= BASE_URL ?>/admin/reports.php?event_id=<?= $eid ?>">Export Report</a>
    </div>
  </div>

  <div class="toolbar-enhanced">
    <div class="toolbar-search-wrap">
      <svg class="search-icon" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
      <input type="text" data-filter="#regs" placeholder="Filter by student name, email or phone..." aria-label="Filter registrations">
    </div>
  </div>

  <div class="table-wrap">
    <table id="regs">
      <thead>
        <tr>
          <th>#</th>
          <th>Student Name</th>
          <th>College Email</th>
          <th>Contact Phone</th>
          <th>Registration Timestamp</th>
          <th>Attendance Status</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($rows as $i => $r): ?>
        <tr>
          <td><?= $i + 1 ?></td>
          <td><strong><?= e($r['name']) ?></strong></td>
          <td><?= e($r['email']) ?></td>
          <td><?= e($r['phone']) ?></td>
          <td><?= e(date('d M Y, h:i A', strtotime($r['registration_date']))) ?></td>
          <td>
            <?php if ($r['status']): ?>
              <span class="badge <?= $r['status'] === 'Present' ? 'badge-ok' : 'badge-bad' ?>"><?= e($r['status']) ?></span>
            <?php else: ?>
              <span class="badge badge-muted">Not marked yet</span>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>

<?php include ROOT_PATH . '/includes/footer.php'; ?>
