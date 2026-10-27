<?php
/* Renders one event as an interactive ticket-style card. $registeredIds = event ids the logged-in student joined. */
function event_card(array $ev, array $registeredIds = []): string {
    $ts       = strtotime($ev['event_date']);
    $todayTs  = strtotime(date('Y-m-d'));
    $diffDays = (int)round(($ts - $todayTs) / 86400);
    $past     = $diffDays < 0;
    $mine     = in_array((int)$ev['event_id'], $registeredIds, true);
    $desc     = mb_strimwidth($ev['description'], 0, 115, '...');
    $cat      = !empty($ev['category']) ? $ev['category'] : 'Technical';
    $catSlug  = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $cat));

    // Urgency tag
    $urgencyBadge = '';
    if ($past) {
        $urgencyBadge = '<span class="status-pill status-past">Completed</span>';
    } elseif ($diffDays === 0) {
        $urgencyBadge = '<span class="status-pill status-urgent pulse">🔥 Today!</span>';
    } elseif ($diffDays === 1) {
        $urgencyBadge = '<span class="status-pill status-soon">⚡ Tomorrow</span>';
    } elseif ($diffDays <= 7) {
        $urgencyBadge = '<span class="status-pill status-upcoming">⏳ In ' . $diffDays . ' days</span>';
    } else {
        $urgencyBadge = '<span class="status-pill status-normal">' . date('M Y', $ts) . '</span>';
    }

    $eventJson = htmlspecialchars(json_encode([
        'id'          => (int)$ev['event_id'],
        'title'       => $ev['event_name'],
        'category'    => $cat,
        'date'        => $ev['event_date'],
        'date_fmt'    => fmt_date($ev['event_date']),
        'time'        => $ev['event_time'],
        'time_fmt'    => fmt_time($ev['event_time']),
        'venue'       => $ev['venue'],
        'organizer'   => $ev['organizer'],
        'description' => $ev['description'],
        'is_past'     => $past,
        'is_mine'     => $mine,
        'url'         => BASE_URL . '/event.php?id=' . (int)$ev['event_id']
    ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP), ENT_QUOTES, 'UTF-8');

    ob_start(); ?>
    <article class="ticket<?= $past ? ' is-past' : '' ?>" 
             data-card-category="<?= e($cat) ?>"
             data-card-name="<?= e(strtolower($ev['event_name'])) ?>"
             data-card-venue="<?= e(strtolower($ev['venue'])) ?>"
             data-card-organizer="<?= e(strtolower($ev['organizer'])) ?>"
             data-card-date="<?= e($ev['event_date']) ?>"
             data-card-id="<?= (int)$ev['event_id'] ?>">
      <div class="stub">
        <span class="day"><?= date('d', $ts) ?></span>
        <span class="mon"><?= date('M', $ts) ?></span>
        <span class="yr"><?= date('Y', $ts) ?></span>
        <span class="stub-notch top"></span>
        <span class="stub-notch bottom"></span>
      </div>
      <div class="ticket-body">
        <div class="ticket-meta-top">
          <span class="category-badge cat-<?= e($catSlug) ?>"><?= e($cat) ?></span>
          <?= $urgencyBadge ?>
        </div>
        <h3 class="ticket-title">
          <a href="<?= BASE_URL ?>/event.php?id=<?= (int)$ev['event_id'] ?>"><?= e($ev['event_name']) ?></a>
        </h3>
        <p class="meta">
          <svg class="icon" viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
          <?= e(fmt_time($ev['event_time'])) ?> &bull; 
          <svg class="icon" viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2a8 8 0 0 0-8 8c0 5.25 8 12 8 12s8-6.75 8-12a8 8 0 0 0-8-8z"/><circle cx="12" cy="10" r="3"/></svg>
          <?= e($ev['venue']) ?>
        </p>
        <p class="desc"><?= e($desc) ?></p>
        <div class="ticket-foot">
          <span class="organizer">By <?= e($ev['organizer']) ?></span>
          <div class="ticket-actions">
            <button type="button" class="btn btn-tiny btn-ghost js-quick-view" data-event='<?= $eventJson ?>' title="Quick View & Share">
              <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
              Quick View
            </button>
            <?php if ($past): ?>
              <span class="badge badge-muted">Completed</span>
            <?php elseif ($mine): ?>
              <a href="<?= BASE_URL ?>/student/my_events.php" class="badge badge-ok">Registered &check;</a>
            <?php else: ?>
              <a class="btn btn-tiny btn-gold" href="<?= BASE_URL ?>/event.php?id=<?= (int)$ev['event_id'] ?>">Register &rarr;</a>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </article>
    <?php return ob_get_clean();
}
