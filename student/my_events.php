<?php
require dirname(__DIR__) . '/config.php';
require_student();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $rid = (int)($_POST['registration_id'] ?? 0);
    $row = fetch_one("SELECT r.registration_id, e.event_name FROM registrations r JOIN events e ON e.event_id = r.event_id
                      WHERE r.registration_id = ? AND r.student_id = ? AND e.event_date >= CURDATE()", 'ii', [$rid, $_SESSION['student_id']]);
    if ($row) {
        q("DELETE FROM registrations WHERE registration_id = ?", 'i', [$rid]);
        flash('Registration for "' . $row['event_name'] . '" cancelled.');
    } else {
        flash('That registration cannot be cancelled.', 'error');
    }
    redirect(BASE_URL . '/student/my_events.php');
}

$pageTitle = 'My events';
$student = fetch_one("SELECT * FROM student WHERE student_id = ?", 'i', [$_SESSION['student_id']]);

$rows = fetch_all("SELECT r.registration_id, r.registration_date, e.event_id, e.category, e.event_name, e.event_date, e.event_time, e.venue, a.status
                   FROM registrations r 
                   JOIN events e ON e.event_id = r.event_id
                   LEFT JOIN attendance a ON a.registration_id = r.registration_id
                   WHERE r.student_id = ? ORDER BY e.event_date DESC", 'i', [$_SESSION['student_id']]);

// Metrics
$totalCount = count($rows);
$upcomingCount = 0;
$attendedCount = 0;
foreach ($rows as $r) {
    if ($r['event_date'] >= date('Y-m-d')) $upcomingCount++;
    if ($r['status'] === 'Present') $attendedCount++;
}
$pastCount = $totalCount - $upcomingCount;
$attRate = $pastCount > 0 ? round(($attendedCount / $pastCount) * 100) : 100;

include ROOT_PATH . '/includes/header.php';
?>

<div class="student-dashboard-header">
  <div>
    <h1 class="page-title">My Registered Events</h1>
    <p class="section-subtitle">Track your registered campus events, digital entry passes, and verified attendance</p>
  </div>
  <a class="btn btn-gold" href="<?= BASE_URL ?>/events.php">+ Browse More Events</a>
</div>

<!-- Student Stats Summary -->
<div class="stats student-stats">
  <div class="stat">
    <b><?= $totalCount ?></b>
    <span>Total Registered</span>
  </div>
  <div class="stat gold">
    <b><?= $upcomingCount ?></b>
    <span>Upcoming Events</span>
  </div>
  <div class="stat">
    <b><?= $attendedCount ?></b>
    <span>Events Attended</span>
  </div>
  <div class="stat gold">
    <b><?= $attRate ?>%</b>
    <span>Attendance Rate</span>
  </div>
</div>

<?php if (!$rows): ?>
  <div class="empty">
    <h3>You have not registered for any events yet.</h3>
    <p class="muted">Explore upcoming hackathons, AI workshops, cultural fests, and sports leagues at COCSIT.</p>
    <a href="<?= BASE_URL ?>/events.php" class="btn btn-gold" style="margin-top:12px;">Browse Events Now &rarr;</a>
  </div>
<?php else: ?>
  <div class="toolbar-enhanced">
    <div class="toolbar-search-wrap">
      <svg class="search-icon" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
      <input type="text" data-filter="#my-events-table" placeholder="Filter my events by title, category or venue..." aria-label="Filter events">
    </div>
  </div>

  <div class="table-wrap">
    <table id="my-events-table">
      <thead>
        <tr>
          <th>Category</th>
          <th>Event Name</th>
          <th>Date &amp; Time</th>
          <th>Venue</th>
          <th>Attendance</th>
          <th style="text-align:right">Actions</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($rows as $r): 
        $past = $r['event_date'] < date('Y-m-d');
        $cat = !empty($r['category']) ? $r['category'] : 'Technical';
        $catSlug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $cat));
      ?>
        <tr>
          <td><span class="category-badge cat-<?= e($catSlug) ?>"><?= e($cat) ?></span></td>
          <td>
            <strong><a href="<?= BASE_URL ?>/event.php?id=<?= (int)$r['event_id'] ?>"><?= e($r['event_name']) ?></a></strong>
            <small class="muted" style="display:block">Registered on <?= e(fmt_date($r['registration_date'])) ?></small>
          </td>
          <td>
            <div><?= e(fmt_date($r['event_date'])) ?></div>
            <small class="muted"><?= e(fmt_time($r['event_time'])) ?></small>
          </td>
          <td><?= e($r['venue']) ?></td>
          <td>
            <?php if ($r['status']): ?>
              <span class="badge <?= $r['status'] === 'Present' ? 'badge-ok' : 'badge-bad' ?>"><?= e($r['status']) ?></span>
            <?php else: ?>
              <span class="badge badge-muted"><?= $past ? 'Not marked' : 'Upcoming' ?></span>
            <?php endif; ?>
          </td>
          <td class="actions" style="text-align:right">
            <!-- Digital Pass Trigger -->
            <button type="button" class="btn btn-tiny btn-ghost js-open-pass-btn"
                    data-event-name="<?= e($r['event_name']) ?>"
                    data-category="<?= e($cat) ?>"
                    data-date="<?= e(fmt_date($r['event_date'])) ?>"
                    data-time="<?= e(fmt_time($r['event_time'])) ?>"
                    data-venue="<?= e($r['venue']) ?>"
                    data-student-name="<?= e($student['name'] ?? $_SESSION['student_name']) ?>"
                    data-student-id="COCSIT-STU-<?= str_pad((string)$_SESSION['student_id'], 4, '0', STR_PAD_LEFT) ?>"
                    data-reg-id="REG-<?= str_pad((string)$r['registration_id'], 5, '0', STR_PAD_LEFT) ?>"
                    data-reg-date="<?= e(date('d M Y', strtotime($r['registration_date']))) ?>">
              🎟️ View Pass
            </button>

            <?php if (!$past): ?>
              <form method="post" data-confirm="Cancel your registration for <?= e($r['event_name']) ?>?" style="display:inline">
                <?= csrf_field() ?>
                <input type="hidden" name="registration_id" value="<?= (int)$r['registration_id'] ?>">
                <button class="btn btn-tiny btn-danger">Cancel</button>
              </form>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>

<?php include ROOT_PATH . '/includes/footer.php'; ?>
