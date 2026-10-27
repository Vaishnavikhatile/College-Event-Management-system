<?php
require dirname(__DIR__) . '/config.php';
require_admin();
$eid = (int)($_GET['event_id'] ?? $_POST['event_id'] ?? 0);
$ev  = $eid ? fetch_one("SELECT * FROM events WHERE event_id = ?", 'i', [$eid]) : null;
if ($ev && $_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $valid = array_column(fetch_all("SELECT registration_id FROM registrations WHERE event_id = ?", 'i', [$eid]), 'registration_id');
    $n = 0;
    foreach (($_POST['status'] ?? []) as $rid => $status) {
        $rid = (int)$rid;
        if (in_array($rid, array_map('intval', $valid), true) && in_array($status, ['Present', 'Absent'], true)) {
            q("INSERT INTO attendance (registration_id, status) VALUES (?, ?) ON DUPLICATE KEY UPDATE status = VALUES(status)", 'is', [$rid, $status]);
            $n++;
        }
    }
    flash("Attendance saved for $n student(s).");
    redirect(BASE_URL . '/admin/attendance.php?event_id=' . $eid);
}
$pageTitle = 'Attendance';
$events = fetch_all("SELECT event_id, event_name, event_date FROM events ORDER BY event_date DESC");
$rows = $ev ? fetch_all("SELECT r.registration_id, s.name, s.email, a.status FROM registrations r
    JOIN student s ON s.student_id = r.student_id LEFT JOIN attendance a ON a.registration_id = r.registration_id
    WHERE r.event_id = ? ORDER BY s.name", 'i', [$eid]) : [];

$pCount = 0;
$aCount = 0;
foreach ($rows as $r) {
    if ($r['status'] === 'Present') $pCount++;
    if ($r['status'] === 'Absent') $aCount++;
}

include ROOT_PATH . '/includes/header.php';
?>
<div class="section-head">
  <div>
    <h1 style="font-size:2.2rem;margin:0">Event Attendance Management</h1>
    <p class="section-subtitle">Select an event, mark attendance status, and save records</p>
  </div>
</div>

<form method="get" class="toolbar-enhanced">
  <div class="toolbar-search-wrap" style="max-width:440px;">
    <select id="event_id" name="event_id" onchange="this.form.submit()">
      <option value="0">Choose an event to manage...</option>
      <?php foreach ($events as $o): ?>
        <option value="<?= (int)$o['event_id'] ?>"<?= $o['event_id'] == $eid ? ' selected' : '' ?>>
          <?= e($o['event_name']) ?> (<?= e(fmt_date($o['event_date'])) ?>)
        </option>
      <?php endforeach; ?>
    </select>
  </div>
  <noscript><button class="btn">Load Event</button></noscript>
</form>

<?php if (!$ev): ?>
  <div class="empty">Please select a campus event from the dropdown above to view and mark student attendance.</div>
<?php elseif (!$rows): ?>
  <div class="empty">No students have registered for "<strong><?= e($ev['event_name']) ?></strong>" yet.</div>
<?php else: ?>
  <div class="stats" style="margin-top:20px;">
    <div class="stat"><b><?= count($rows) ?></b><span>Total Enrolled</span></div>
    <div class="stat gold"><b><?= $pCount ?></b><span>Marked Present</span></div>
    <div class="stat"><b><?= $aCount ?></b><span>Marked Absent</span></div>
    <div class="stat gold"><b><?= count($rows) - $pCount - $aCount ?></b><span>Unmarked</span></div>
  </div>

  <form method="post">
    <?= csrf_field() ?>
    <input type="hidden" name="event_id" value="<?= $eid ?>">
    
    <div class="section-head">
      <div>
        <h2><?= e($ev['event_name']) ?></h2>
        <small class="muted"><?= e(fmt_date($ev['event_date'])) ?> &bull; <?= e(fmt_time($ev['event_time'])) ?> &bull; <?= e($ev['venue']) ?></small>
      </div>
      <div class="no-print" style="display:flex;gap:8px;">
        <button type="button" class="btn btn-small btn-ghost" data-mark-all="Present">Mark All Present</button>
        <button type="button" class="btn btn-small btn-ghost" data-mark-all="Absent">Mark All Absent</button>
      </div>
    </div>

    <div class="toolbar-search-wrap" style="max-width:340px;margin-bottom:12px;">
      <input type="text" data-filter="#att-table" placeholder="Filter enrolled students..." aria-label="Filter students">
    </div>

    <div class="table-wrap">
      <table id="att-table">
        <thead>
          <tr>
            <th>#</th>
            <th>Student Name</th>
            <th>Email Address</th>
            <th>Status Selection</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($rows as $i => $r): $rid = (int)$r['registration_id']; ?>
          <tr>
            <td><?= $i + 1 ?></td>
            <td><strong><?= e($r['name']) ?></strong></td>
            <td><?= e($r['email']) ?></td>
            <td>
              <div class="seg">
                <label>
                  <input type="radio" name="status[<?= $rid ?>]" value="Present"<?= $r['status'] === 'Present' ? ' checked' : '' ?>>
                  <span class="badge badge-ok">Present</span>
                </label>
                <label>
                  <input type="radio" name="status[<?= $rid ?>]" value="Absent"<?= $r['status'] === 'Absent' ? ' checked' : '' ?>>
                  <span class="badge badge-bad">Absent</span>
                </label>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <div class="form-actions" style="margin-top:20px;display:flex;gap:12px;align-items:center;">
      <button class="btn btn-gold btn-large">Save Attendance Records</button>
      <a class="btn btn-ghost" href="<?= BASE_URL ?>/admin/reports.php?event_id=<?= $eid ?>">View Report &amp; Print</a>
    </div>
  </form>
<?php endif; ?>

<?php include ROOT_PATH . '/includes/footer.php'; ?>
