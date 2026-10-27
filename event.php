<?php
require __DIR__ . '/config.php';
$id = (int)($_GET['id'] ?? 0);
$ev = fetch_one("SELECT * FROM events WHERE event_id = ?", 'i', [$id]);
if (!$ev) {
    http_response_code(404);
    $pageTitle = 'Event not found';
    include ROOT_PATH . '/includes/header.php';
    echo '<div class="empty">That event does not exist. <a href="' . BASE_URL . '/events.php">Browse events</a></div>';
    include ROOT_PATH . '/includes/footer.php';
    exit;
}
$pageTitle = $ev['event_name'];
$past = $ev['event_date'] < date('Y-m-d');
$cat  = !empty($ev['category']) ? $ev['category'] : 'Technical';
$catSlug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $cat));

$reg = null;
if (!empty($_SESSION['student_id'])) {
    $reg = fetch_one("SELECT r.registration_id, r.registration_date, a.status, s.name as student_name, s.email as student_email, s.phone as student_phone 
                      FROM registrations r 
                      JOIN student s ON s.student_id = r.student_id
                      LEFT JOIN attendance a ON a.registration_id = r.registration_id
                      WHERE r.student_id = ? AND r.event_id = ?", 'ii', [$_SESSION['student_id'], $id]);
}
$count = fetch_one("SELECT COUNT(*) c FROM registrations WHERE event_id = ?", 'i', [$id])['c'];

// Calendar links
$startIso = date('Ymd\THis', strtotime($ev['event_date'] . ' ' . $ev['event_time']));
$endIso   = date('Ymd\THis', strtotime($ev['event_date'] . ' ' . $ev['event_time'] . ' +3 hours'));
$gcalUrl  = 'https://calendar.google.com/calendar/render?action=TEMPLATE' .
            '&text=' . urlencode($ev['event_name']) .
            '&dates=' . $startIso . '/' . $endIso .
            '&details=' . urlencode($ev['description'] . "\n\nOrganized by: " . $ev['organizer']) .
            '&location=' . urlencode($ev['venue'] . ', COCSIT Latur');

include ROOT_PATH . '/includes/header.php';
?>

<div class="breadcrumb">
  <a href="<?= BASE_URL ?>/events.php">&larr; Back to all events</a>
  <span class="breadcrumb-sep">/</span>
  <span class="category-badge cat-<?= e($catSlug) ?>"><?= e($cat) ?></span>
</div>

<div class="detail-grid">
  <!-- Main Event Information -->
  <article class="panel detail-main">
    <div class="detail-header">
      <div class="detail-tags">
        <span class="category-badge cat-<?= e($catSlug) ?>"><?= e($cat) ?></span>
        <?php if ($past): ?>
          <span class="status-pill status-past">Completed Event</span>
        <?php else: ?>
          <span class="status-pill status-urgent pulse">Live Registration Open</span>
        <?php endif; ?>
      </div>
      <h1 class="detail-title"><?= e($ev['event_name']) ?></h1>
      <p class="detail-organizer">Organized by <strong><?= e($ev['organizer']) ?></strong></p>
    </div>

    <!-- Live Countdown Banner for upcoming events -->
    <?php if (!$past): ?>
      <div class="detail-countdown-banner">
        <div class="countdown-label">
          <span class="pulse-dot"></span> Event Starts In:
        </div>
        <div class="countdown-box" data-countdown="<?= e($ev['event_date']) ?>T<?= e($ev['event_time']) ?>">
          <div class="countdown-unit"><span class="cd-val cd-days">00</span><span class="cd-lbl">Days</span></div>
          <span class="cd-colon">:</span>
          <div class="countdown-unit"><span class="cd-val cd-hours">00</span><span class="cd-lbl">Hours</span></div>
          <span class="cd-colon">:</span>
          <div class="countdown-unit"><span class="cd-val cd-mins">00</span><span class="cd-lbl">Mins</span></div>
          <span class="cd-colon">:</span>
          <div class="countdown-unit"><span class="cd-val cd-secs">00</span><span class="cd-lbl">Secs</span></div>
        </div>
      </div>
    <?php endif; ?>

    <div class="detail-section">
      <h3>About this Event</h3>
      <div class="prose"><?= nl2br(e($ev['description'])) ?></div>
    </div>

    <!-- Quick Share & Calendar Tools -->
    <div class="detail-tools-bar">
      <h4>Add to Calendar &amp; Share</h4>
      <div class="tool-buttons">
        <a href="<?= $gcalUrl ?>" target="_blank" rel="noopener" class="btn btn-small btn-ghost">
          <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
          Google Calendar
        </a>
        <button type="button" class="btn btn-small btn-ghost js-share-btn" data-url="<?= BASE_URL ?>/event.php?id=<?= (int)$id ?>" data-title="<?= e($ev['event_name']) ?>">
          <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"/><polyline points="16 6 12 2 8 6"/><line x1="12" y1="2" x2="12" y2="15"/></svg>
          Copy Share Link
        </button>
        <a href="https://api.whatsapp.com/send?text=<?= urlencode('Check out ' . $ev['event_name'] . ' at COCSIT! Register here: ' . (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . BASE_URL . '/event.php?id=' . $id) ?>" target="_blank" rel="noopener" class="btn btn-small btn-ghost">
          <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>
          WhatsApp
        </a>
      </div>
    </div>
  </article>

  <!-- Sidebar: Event Quick Facts & Registration Card -->
  <aside class="panel detail-sidebar">
    <div class="sidebar-header">
      <h3>Event Details</h3>
    </div>
    
    <ul class="facts">
      <li>
        <span class="fact-label">
          <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
          Date
        </span>
        <strong><?= e(fmt_date($ev['event_date'])) ?></strong>
      </li>
      <li>
        <span class="fact-label">
          <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
          Time
        </span>
        <strong><?= e(fmt_time($ev['event_time'])) ?></strong>
      </li>
      <li>
        <span class="fact-label">
          <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2a8 8 0 0 0-8 8c0 5.25 8 12 8 12s8-6.75 8-12a8 8 0 0 0-8-8z"/><circle cx="12" cy="10" r="3"/></svg>
          Venue
        </span>
        <strong>
          <?= e($ev['venue']) ?>
          <a href="https://maps.google.com/?q=<?= urlencode($ev['venue'] . ' COCSIT Latur') ?>" target="_blank" rel="noopener" class="map-link">Map &nearr;</a>
        </strong>
      </li>
      <li>
        <span class="fact-label">
          <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
          Enrolled
        </span>
        <strong class="highlight-stat"><?= (int)$count ?> Student<?= $count == 1 ? '' : 's' ?></strong>
      </li>
    </ul>

    <!-- Action Section -->
    <div class="sidebar-action-box">
      <?php if ($past): ?>
        <div class="alert-box alert-muted">
          <strong>Event Completed</strong>
          <p>Registration for this event is closed.</p>
          <?php if ($reg && !empty($reg['status'])): ?>
            <div style="margin-top:8px">
              Your Attendance: 
              <span class="badge <?= $reg['status'] === 'Present' ? 'badge-ok' : 'badge-bad' ?>"><?= e($reg['status']) ?></span>
            </div>
          <?php endif; ?>
        </div>
      <?php elseif ($reg): ?>
        <div class="alert-box alert-success">
          <div class="alert-head">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
            <strong>You Are Registered!</strong>
          </div>
          <p>Registered on <?= e(date('d M Y, h:i A', strtotime($reg['registration_date']))) ?></p>
          
          <!-- Digital Pass Trigger -->
          <button type="button" class="btn btn-gold btn-block js-open-pass-btn" 
                  data-event-name="<?= e($ev['event_name']) ?>"
                  data-category="<?= e($cat) ?>"
                  data-date="<?= e(fmt_date($ev['event_date'])) ?>"
                  data-time="<?= e(fmt_time($ev['event_time'])) ?>"
                  data-venue="<?= e($ev['venue']) ?>"
                  data-student-name="<?= e($reg['student_name']) ?>"
                  data-student-id="COCSIT-STU-<?= str_pad((string)$_SESSION['student_id'], 4, '0', STR_PAD_LEFT) ?>"
                  data-reg-id="REG-<?= str_pad((string)$reg['registration_id'], 5, '0', STR_PAD_LEFT) ?>"
                  data-reg-date="<?= e(date('d M Y', strtotime($reg['registration_date']))) ?>">
            🎟️ View Digital Event Pass
          </button>
          
          <a href="<?= BASE_URL ?>/student/my_events.php" class="hint link-center">View all your registered events &rarr;</a>
        </div>
      <?php elseif (!empty($_SESSION['student_id'])): ?>
        <form method="post" action="<?= BASE_URL ?>/student/register_event.php">
          <?= csrf_field() ?>
          <input type="hidden" name="event_id" value="<?= (int)$id ?>">
          <button class="btn btn-gold btn-large btn-block pulse">Register for this Event &rarr;</button>
        </form>
        <p class="hint center-text">Instant confirmation &bull; Entry pass generated automatically</p>
      <?php else: ?>
        <div class="auth-prompt-box">
          <p>Log in with your student account to register for this event.</p>
          <a class="btn btn-gold btn-block" href="<?= BASE_URL ?>/login.php?redirect=event.php?id=<?= (int)$id ?>">Log In to Register</a>
          <p class="hint center-text">Don't have an account? <a href="<?= BASE_URL ?>/register.php">Sign up here</a></p>
        </div>
      <?php endif; ?>
    </div>
  </aside>
</div>

<?php include ROOT_PATH . '/includes/footer.php'; ?>
