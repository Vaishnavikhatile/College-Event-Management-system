<?php
require __DIR__ . '/config.php';
require ROOT_PATH . '/includes/event_card.php';
$pageTitle = 'All events';
$view = ($_GET['view'] ?? 'upcoming') === 'past' ? 'past' : 'upcoming';
$selectedCat = trim($_GET['cat'] ?? 'all');
$term = trim($_GET['q'] ?? '');
$like = '%' . $term . '%';
$op   = $view === 'past' ? '<' : '>=';
$order = $view === 'past' ? 'DESC' : 'ASC';

$sql = "SELECT * FROM events WHERE event_date $op CURDATE()
        AND (event_name LIKE ? OR venue LIKE ? OR organizer LIKE ? OR description LIKE ?)";
$params = [$like, $like, $like, $like];
$types = 'ssss';

if ($selectedCat !== '' && $selectedCat !== 'all') {
    $sql .= " AND category = ?";
    $params[] = $selectedCat;
    $types .= 's';
}
$sql .= " ORDER BY event_date $order, event_time $order";

$events = fetch_all($sql, $types, $params);
$mine = [];
if (!empty($_SESSION['student_id'])) {
    $mine = array_map('intval', array_column(fetch_all("SELECT event_id FROM registrations WHERE student_id = ?", 'i', [$_SESSION['student_id']]), 'event_id'));
}

// Category counts for the active view
$allForView = fetch_all("SELECT category FROM events WHERE event_date $op CURDATE()", '');
$categoryCounts = [];
foreach ($allForView as $row) {
    $c = $row['category'] ?? 'Technical';
    $categoryCounts[$c] = ($categoryCounts[$c] ?? 0) + 1;
}

include ROOT_PATH . '/includes/header.php';
?>

<div class="events-page-header">
  <div>
    <h1 class="page-title">Campus Events Explorer</h1>
    <p class="section-subtitle">Browse all upcoming workshops, fests, hackathons, and past event archives</p>
  </div>
  <div class="view-switcher-tabs">
    <a href="<?= BASE_URL ?>/events.php?view=upcoming" class="view-tab <?= $view === 'upcoming' ? 'active' : '' ?>">
      <span class="pulse-dot"></span> Upcoming (<?= count(fetch_all("SELECT event_id FROM events WHERE event_date >= CURDATE()")) ?>)
    </a>
    <a href="<?= BASE_URL ?>/events.php?view=past" class="view-tab <?= $view === 'past' ? 'active' : '' ?>">
      Past Archives (<?= count(fetch_all("SELECT event_id FROM events WHERE event_date < CURDATE()")) ?>)
    </a>
  </div>
</div>

<!-- Interactive Search and Filter Toolbar -->
<div class="toolbar-enhanced">
  <div class="toolbar-search-wrap">
    <svg class="search-icon" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
    <input type="text" id="live-search-input" value="<?= e($term) ?>" placeholder="Filter by title, venue, speaker or club..." aria-label="Search events">
    <button type="button" id="search-clear-btn" class="search-clear" style="<?= $term !== '' ? '' : 'display:none;' ?>">&times;</button>
  </div>

  <div class="toolbar-controls">
    <div class="control-group">
      <label for="quick-sort-select">Sort By</label>
      <select id="quick-sort-select" class="sort-select">
        <option value="date-asc" <?= $view === 'upcoming' ? 'selected' : '' ?>>Date: Soonest First</option>
        <option value="date-desc" <?= $view === 'past' ? 'selected' : '' ?>>Date: Latest First</option>
        <option value="name-asc">Name: A to Z</option>
      </select>
    </div>
  </div>
</div>

<!-- Interactive Category Filter Pills -->
<div class="category-filters" id="category-filters">
  <button type="button" class="cat-pill <?= ($selectedCat === 'all' || $selectedCat === '') ? 'active' : '' ?>" data-filter="all">
    All Categories <span class="pill-count"><?= count($allForView) ?></span>
  </button>
  <?php foreach (['Technical', 'Workshop', 'Cultural', 'Sports', 'Seminar'] as $cat): ?>
    <?php $cnt = $categoryCounts[$cat] ?? 0; ?>
    <button type="button" class="cat-pill <?= $selectedCat === $cat ? 'active' : '' ?>" data-filter="<?= e($cat) ?>">
      <?= e($cat) ?> <span class="pill-count"><?= $cnt ?></span>
    </button>
  <?php endforeach; ?>
</div>

<div class="results-bar">
  <span id="results-count-text">Showing <?= count($events) ?> <?= e($view) ?> events</span>
</div>

<?php if ($events): ?>
  <div class="ticket-grid" id="events-grid">
    <?php foreach ($events as $ev) echo event_card($ev, $mine); ?>
  </div>
  <div id="no-filter-match" class="empty" style="display:none;">
    <p>No <?= e($view) ?> events match your search.</p>
    <button type="button" class="btn btn-small btn-ghost" id="reset-filters-btn">Reset Filters</button>
  </div>
<?php else: ?>
  <div class="empty">
    <p>No <?= e($view) ?> events match your filter.</p>
    <a href="<?= BASE_URL ?>/events.php?view=<?= $view === 'past' ? 'upcoming' : 'past' ?>" class="btn btn-small btn-ghost">View <?= $view === 'past' ? 'upcoming' : 'past' ?> events instead</a>
  </div>
<?php endif; ?>

<?php include ROOT_PATH . '/includes/footer.php'; ?>
