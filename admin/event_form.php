<?php
require dirname(__DIR__) . '/config.php';
require_admin();
$id = (int)($_GET['id'] ?? 0);
$ev = ['category' => 'Technical', 'event_name' => '', 'description' => '', 'event_date' => '', 'event_time' => '', 'venue' => '', 'organizer' => ''];
if ($id) {
    $found = fetch_one("SELECT * FROM events WHERE event_id = ?", 'i', [$id]);
    if (!$found) { flash('Event not found.', 'error'); redirect(BASE_URL . '/admin/events.php'); }
    $ev = $found;
}
$errors = [];
$categories = ['Technical', 'Workshop', 'Cultural', 'Sports', 'Seminar'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    foreach (['category', 'event_name', 'description', 'event_date', 'event_time', 'venue', 'organizer'] as $f) $ev[$f] = trim($_POST[$f] ?? '');
    if (!in_array($ev['category'], $categories, true)) $ev['category'] = 'Technical';
    $ev['event_time'] = substr($ev['event_time'], 0, 5);
    if ($ev['event_name'] === '') $errors[] = 'Enter the event name.';
    if ($ev['description'] === '') $errors[] = 'Enter a description.';
    $d = DateTime::createFromFormat('Y-m-d', $ev['event_date']);
    if (!$d || $d->format('Y-m-d') !== $ev['event_date']) $errors[] = 'Choose a valid date.';
    if (!preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $ev['event_time'])) $errors[] = 'Choose a valid time.';
    if ($ev['venue'] === '') $errors[] = 'Enter the venue.';
    if ($ev['organizer'] === '') $errors[] = 'Enter the organizer.';
    if (!$errors) {
        if ($id) {
            q("UPDATE events SET category=?, event_name=?, description=?, event_date=?, event_time=?, venue=?, organizer=? WHERE event_id=?",
              'sssssssi', [$ev['category'], $ev['event_name'], $ev['description'], $ev['event_date'], $ev['event_time'], $ev['venue'], $ev['organizer'], $id]);
            flash('Event updated.');
        } else {
            q("INSERT INTO events (admin_id, category, event_name, description, event_date, event_time, venue, organizer) VALUES (?,?,?,?,?,?,?,?)",
              'isssssss', [$_SESSION['admin_id'], $ev['category'], $ev['event_name'], $ev['description'], $ev['event_date'], $ev['event_time'], $ev['venue'], $ev['organizer']]);
            flash('Event added.');
        }
        redirect(BASE_URL . '/admin/events.php');
    }
}
$pageTitle = $id ? 'Edit event' : 'Add event';
include ROOT_PATH . '/includes/header.php';
?>
<div class="panel form-wide">
  <h2><?= $id ? 'Edit event' : 'Add new event' ?></h2>
  <?php if ($errors): ?><ul class="error-list"><?php foreach ($errors as $er) echo '<li>' . e($er) . '</li>'; ?></ul><?php endif; ?>
  <form method="post" novalidate><?= csrf_field() ?>
    <div class="form-row">
      <div>
        <label for="event_name">Event name</label>
        <input id="event_name" name="event_name" value="<?= e($ev['event_name']) ?>" required>
      </div>
      <div>
        <label for="category">Category</label>
        <select id="category" name="category">
          <?php foreach ($categories as $cat): ?>
            <option value="<?= e($cat) ?>"<?= ($ev['category'] ?? '') === $cat ? ' selected' : '' ?>><?= e($cat) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>
    <label for="description">Description</label><textarea id="description" name="description" required><?= e($ev['description']) ?></textarea>
    <div class="form-row">
      <div><label for="event_date">Date</label><input id="event_date" type="date" name="event_date" value="<?= e($ev['event_date']) ?>" required></div>
      <div><label for="event_time">Time</label><input id="event_time" type="time" name="event_time" value="<?= e(substr($ev['event_time'], 0, 5)) ?>" required></div>
    </div>
    <div class="form-row">
      <div><label for="venue">Venue</label><input id="venue" name="venue" value="<?= e($ev['venue']) ?>" required></div>
      <div><label for="organizer">Organizer</label><input id="organizer" name="organizer" value="<?= e($ev['organizer']) ?>" required></div>
    </div>
    <div class="form-actions"><button class="btn"><?= $id ? 'Save changes' : 'Add event' ?></button><a class="btn btn-ghost" href="<?= BASE_URL ?>/admin/events.php">Cancel</a></div>
  </form>
</div>
<?php include ROOT_PATH . '/includes/footer.php'; ?>
