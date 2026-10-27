<?php
require __DIR__ . '/config.php';
require ROOT_PATH . '/includes/event_card.php';
$pageTitle = 'Home';
$today  = date('Y-m-d');
$events = fetch_all("SELECT * FROM events WHERE event_date >= ? ORDER BY event_date ASC, event_time ASC", 's', [$today]);
$mine   = [];
if (!empty($_SESSION['student_id'])) {
    $mine = array_map('intval', array_column(fetch_all("SELECT event_id FROM registrations WHERE student_id = ?", 'i', [$_SESSION['student_id']]), 'event_id'));
}

// Category counts for interactive pills
$categoryCounts = [];
foreach ($events as $e) {
    $c = $e['category'] ?? 'Technical';
    $categoryCounts[$c] = ($categoryCounts[$c] ?? 0) + 1;
}

// Overall system stats for hero banner
$stats = fetch_one("SELECT 
    (SELECT COUNT(*) FROM events WHERE event_date >= CURDATE()) AS upcoming_count,
    (SELECT COUNT(*) FROM registrations) AS reg_count,
    (SELECT COUNT(DISTINCT student_id) FROM registrations) AS active_students,
    (SELECT COUNT(DISTINCT category) FROM events) AS cat_count");

include ROOT_PATH . '/includes/header.php';
?>

<!-- Hero Section -->
<section class="hero-wrap">
  <div class="hero-content">
    <div class="hero-badge">
      <span class="pulse-dot"></span> COCSIT Campus Event Portal &bull; 2026-27
    </div>
    <h1 class="hero-title">Discover, Connect &amp; Excel at <span class="gradient-text">COCSIT Events</span></h1>
    <p class="hero-lead">From high-stakes hackathons and AI masterclasses to vibrant cultural fests and sports championships &mdash; explore campus happenings, register instantly, and manage your digital event passes in one place.</p>
    
    <div class="hero-actions">
      <a class="btn btn-gold btn-large" href="<?= BASE_URL ?>/events.php">
        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
        Explore All Events
      </a>
      <?php if (empty($_SESSION['student_id'])): ?>
        <a class="btn btn-ghost btn-large" href="<?= BASE_URL ?>/register.php">Create Student Account</a>
      <?php else: ?>
        <a class="btn btn-ghost btn-large" href="<?= BASE_URL ?>/student/my_events.php">My Registrations (<?= count($mine) ?>)</a>
      <?php endif; ?>
    </div>

    <!-- Live Campus Stats Counter -->
    <div class="hero-stats">
      <div class="hero-stat-item">
        <span class="stat-number" data-count="<?= (int)($stats['upcoming_count'] ?? 0) ?>"><?= (int)($stats['upcoming_count'] ?? 0) ?></span>
        <span class="stat-label">Upcoming Events</span>
      </div>
      <div class="hero-stat-item">
        <span class="stat-number" data-count="<?= (int)($stats['reg_count'] ?? 0) ?>"><?= (int)($stats['reg_count'] ?? 0) ?></span>
        <span class="stat-label">Registrations</span>
      </div>
      <div class="hero-stat-item">
        <span class="stat-number" data-count="<?= (int)($stats['active_students'] ?? 0) ?>"><?= (int)($stats['active_students'] ?? 0) ?></span>
        <span class="stat-label">Active Students</span>
      </div>
      <div class="hero-stat-item">
        <span class="stat-number" data-count="100">100%</span>
        <span class="stat-label">Verified Passes</span>
      </div>
    </div>
  </div>

  <!-- Featured Spotlight Card -->
  <div class="hero-spotlight">
    <?php if ($events): $next = $events[0]; ?>
      <div class="spotlight-card">
        <div class="spotlight-badge">
          <span class="pulse-ring"></span> Next Major Event Spotlight
        </div>
        <div class="spotlight-body">
          <span class="category-badge cat-<?= strtolower(e($next['category'] ?? 'technical')) ?>"><?= e($next['category'] ?? 'Technical') ?></span>
          <h2 class="spotlight-title"><a href="<?= BASE_URL ?>/event.php?id=<?= (int)$next['event_id'] ?>"><?= e($next['event_name']) ?></a></h2>
          <p class="spotlight-desc"><?= e(mb_strimwidth($next['description'], 0, 140, '...')) ?></p>
          
          <!-- Live Countdown Component -->
          <div class="countdown-box" data-countdown="<?= e($next['event_date']) ?>T<?= e($next['event_time']) ?>">
            <div class="countdown-unit"><span class="cd-val cd-days">00</span><span class="cd-lbl">Days</span></div>
            <span class="cd-colon">:</span>
            <div class="countdown-unit"><span class="cd-val cd-hours">00</span><span class="cd-lbl">Hours</span></div>
            <span class="cd-colon">:</span>
            <div class="countdown-unit"><span class="cd-val cd-mins">00</span><span class="cd-lbl">Mins</span></div>
            <span class="cd-colon">:</span>
            <div class="countdown-unit"><span class="cd-val cd-secs">00</span><span class="cd-lbl">Secs</span></div>
          </div>

          <div class="spotlight-footer">
            <div class="spotlight-meta">
              <span>📅 <?= e(fmt_date($next['event_date'])) ?> &bull; ⏰ <?= e(fmt_time($next['event_time'])) ?></span>
              <small>📍 <?= e($next['venue']) ?></small>
            </div>
            <a href="<?= BASE_URL ?>/event.php?id=<?= (int)$next['event_id'] ?>" class="btn btn-small btn-gold">
              <?= in_array((int)$next['event_id'], $mine, true) ? 'View Pass &check;' : 'Register Now &rarr;' ?>
            </a>
          </div>
        </div>
      </div>
    <?php else: ?>
      <div class="empty">No upcoming events scheduled right now. Check back soon.</div>
    <?php endif; ?>
  </div>
</section>

<!-- Interactive Filter Toolbar -->
<section class="events-browser-section">
  <div class="section-head">
    <div>
      <h2 class="section-title">Upcoming Campus Events</h2>
      <p class="section-subtitle">Filter instantly by category or search by topic, venue, or club</p>
    </div>
    <div class="search-box">
      <svg class="search-icon" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
      <input type="text" id="live-search-input" placeholder="Quick search events..." aria-label="Search events">
      <button type="button" id="search-clear-btn" class="search-clear" style="display:none;" aria-label="Clear search">&times;</button>
    </div>
  </div>

  <!-- Interactive Category Filter Pills -->
  <div class="category-filters" id="category-filters">
    <button type="button" class="cat-pill active" data-filter="all">
      All Categories <span class="pill-count"><?= count($events) ?></span>
    </button>
    <?php foreach (['Technical', 'Workshop', 'Cultural', 'Sports', 'Seminar'] as $cat): ?>
      <?php $cCount = $categoryCounts[$cat] ?? 0; ?>
      <button type="button" class="cat-pill" data-filter="<?= e($cat) ?>">
        <?= e($cat) ?> <span class="pill-count"><?= $cCount ?></span>
      </button>
    <?php endforeach; ?>
  </div>

  <!-- Real-time results counter -->
  <div class="results-bar">
    <span id="results-count-text">Showing all <?= count($events) ?> upcoming events</span>
    <div class="view-toggles">
      <span class="muted small">Sort:</span>
      <select id="quick-sort-select" class="sort-select">
        <option value="date-asc">Date: Soonest First</option>
        <option value="date-desc">Date: Furthest Out</option>
        <option value="name-asc">Name: A to Z</option>
      </select>
    </div>
  </div>

  <!-- Events Ticket Grid -->
  <?php if ($events): ?>
    <div class="ticket-grid" id="events-grid">
      <?php foreach ($events as $ev) echo event_card($ev, $mine); ?>
    </div>
    <div id="no-filter-match" class="empty" style="display:none;">
      <p>No events matched your current search and category filter.</p>
      <button type="button" class="btn btn-small btn-ghost" id="reset-filters-btn">Reset Filters</button>
    </div>
  <?php else: ?>
    <div class="empty">More events will appear here as the admin adds them.</div>
  <?php endif; ?>
</section>

<!-- Fast Links / Campus Highlights -->
<section class="campus-highlights">
  <div class="highlight-card">
    <div class="highlight-icon">🎟️</div>
    <h3>Instant Registration</h3>
    <p>Sign up for hackathons, seminars, and sports tournaments in one click. No paper forms required.</p>
  </div>
  <div class="highlight-card">
    <div class="highlight-icon">📱</div>
    <h3>Digital Event Pass</h3>
    <p>Get instant verified digital entry passes with simulated QR verification for your attendance.</p>
  </div>
  <div class="highlight-card">
    <div class="highlight-icon">📊</div>
    <h3>Live Attendance Tracking</h3>
    <p>Faculty &amp; organizers mark attendance in real-time. Track your participation rate on your student profile.</p>
  </div>
  <div class="highlight-card">
    <div class="highlight-icon">📅</div>
    <h3>Calendar Integration</h3>
    <p>Sync event schedules directly with Google Calendar or Apple Calendar (.ics) with one click.</p>
  </div>
</section>

<?php include ROOT_PATH . '/includes/footer.php'; ?>
