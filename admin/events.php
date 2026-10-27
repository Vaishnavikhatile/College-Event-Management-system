<?php
require dirname(__DIR__) . '/config.php';
require_admin();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    q("DELETE FROM events WHERE event_id = ?", 'i', [(int)($_POST['event_id'] ?? 0)]);
    flash('Event deleted along with its registrations and attendance.');
    redirect(BASE_URL . '/admin/events.php');
}
$pageTitle = 'Manage events';
$events = fetch_all("SELECT e.*, (SELECT COUNT(*) FROM registrations r WHERE r.event_id = e.event_id) AS regs FROM events e ORDER BY e.event_date DESC, e.event_time DESC");
include ROOT_PATH . '/includes/header.php';
?>
<div class="section-head">
  <div>
    <h1 style="font-size:2.2rem;margin:0">Manage Campus Events</h1>
    <p class="section-subtitle">Create, edit, view registrations, and oversee campus activities</p>
  </div>
  <a class="btn btn-gold" href="<?= BASE_URL ?>/admin/event_form.php">+ Add New Event</a>
</div>

<?php if (!$events): ?>
  <div class="empty">No events yet. <a href="<?= BASE_URL ?>/admin/event_form.php">Add the first one</a>.</div>
<?php else: ?>
  <div class="toolbar-enhanced">
    <div class="toolbar-search-wrap">
      <svg class="search-icon" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
      <input type="text" data-filter="#evt" placeholder="Live filter events by name, category, venue..." aria-label="Filter events">
    </div>
    <span class="muted small"><?= count($events) ?> Total Events</span>
  </div>

  <div class="table-wrap">
    <table id="evt">
      <thead>
        <tr>
          <th>Category</th>
          <th>Event Name</th>
          <th>Date &amp; Time</th>
          <th>Venue</th>
          <th>Organizer</th>
          <th>Enrolled</th>
          <th style="text-align:right">Actions</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($events as $ev): 
        $cat = !empty($ev['category']) ? $ev['category'] : 'Technical';
        $catSlug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $cat));
        $past = $ev['event_date'] < date('Y-m-d');
      ?>
        <tr>
          <td><span class="category-badge cat-<?= e($catSlug) ?>"><?= e($cat) ?></span></td>
          <td>
            <strong><a href="<?= BASE_URL ?>/event.php?id=<?= (int)$ev['event_id'] ?>" target="_blank"><?= e($ev['event_name']) ?></a></strong>
            <?php if ($past): ?><span class="badge badge-muted small" style="margin-left:4px;">Past</span><?php endif; ?>
          </td>
          <td><?= e(fmt_date($ev['event_date'])) ?>, <small class="muted"><?= e(fmt_time($ev['event_time'])) ?></small></td>
          <td><?= e($ev['venue']) ?></td>
          <td><?= e($ev['organizer']) ?></td>
          <td>
            <a href="<?= BASE_URL ?>/admin/registrations.php?event_id=<?= (int)$ev['event_id'] ?>" class="badge badge-ok">
              <?= (int)$ev['regs'] ?> Students
            </a>
          </td>
          <td class="actions" style="text-align:right">
            <a class="btn btn-tiny btn-ghost" href="<?= BASE_URL ?>/admin/attendance.php?event_id=<?= (int)$ev['event_id'] ?>" title="Attendance">Sheet</a>
            <a class="btn btn-tiny btn-ghost" href="<?= BASE_URL ?>/admin/event_form.php?id=<?= (int)$ev['event_id'] ?>">Edit</a>
            <form method="post" data-confirm="Delete '<?= e($ev['event_name']) ?>' and all related registrations?">
              <?= csrf_field() ?>
              <input type="hidden" name="event_id" value="<?= (int)$ev['event_id'] ?>">
              <button class="btn btn-tiny btn-danger">Delete</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>

<?php include ROOT_PATH . '/includes/footer.php'; ?>
