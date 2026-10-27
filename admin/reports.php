<?php
require dirname(__DIR__) . '/config.php';
require_admin();
$eid = (int)($_GET['event_id'] ?? 0);
$ev  = $eid ? fetch_one("SELECT * FROM events WHERE event_id = ?", 'i', [$eid]) : null;

function csv_out(string $name, array $header, array $rows): void {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $name . '"');
    $f = fopen('php://output', 'w');
    fwrite($f, "\xEF\xBB\xBF");   // BOM so Excel reads UTF-8 correctly
    fputcsv($f, $header);
    foreach ($rows as $r) fputcsv($f, $r);
    fclose($f); exit;
}

if ($ev) {
    $rows = fetch_all("SELECT s.name, s.email, s.phone, r.registration_date, a.status FROM registrations r
        JOIN student s ON s.student_id = r.student_id LEFT JOIN attendance a ON a.registration_id = r.registration_id
        WHERE r.event_id = ? ORDER BY s.name", 'i', [$eid]);
    if (isset($_GET['export'])) {
        csv_out('participants_event_' . $eid . '.csv', ['Name', 'Email', 'Phone', 'Registered on', 'Attendance'],
            array_map(fn($r) => [$r['name'], $r['email'], $r['phone'], $r['registration_date'], $r['status'] ?? 'Not marked'], $rows));
    }
} else {
    $sum = fetch_all("SELECT e.event_id, e.event_name, e.event_date, COUNT(r.registration_id) AS registered,
        SUM(a.status = 'Present') AS present, SUM(a.status = 'Absent') AS absent
        FROM events e LEFT JOIN registrations r ON r.event_id = e.event_id
        LEFT JOIN attendance a ON a.registration_id = r.registration_id
        GROUP BY e.event_id, e.event_name, e.event_date ORDER BY e.event_date DESC");
    if (isset($_GET['export'])) {
        csv_out('event_summary.csv', ['Event', 'Date', 'Registered', 'Present', 'Absent', 'Not marked'],
            array_map(fn($r) => [$r['event_name'], $r['event_date'], $r['registered'], (int)$r['present'], (int)$r['absent'], $r['registered'] - $r['present'] - $r['absent']], $sum));
    }
}
$pageTitle = 'Reports';
include ROOT_PATH . '/includes/header.php';
?>
<div class="section-head"><h1 style="font-size:2.2rem;margin:0"><?= $ev ? 'Participation: ' . e($ev['event_name']) : 'Event reports' ?></h1>
  <div class="no-print">
    <?php if ($ev): ?><a class="btn btn-ghost" href="<?= BASE_URL ?>/admin/reports.php">All events</a><?php endif; ?>
    <a class="btn btn-ghost" href="?<?= $ev ? 'event_id=' . $eid . '&' : '' ?>export=csv">Download CSV</a>
    <button class="btn" onclick="window.print()">Print</button>
  </div></div>

<?php if ($ev): ?>
  <?php $p = count(array_filter($rows, fn($r) => $r['status'] === 'Present')); $a = count(array_filter($rows, fn($r) => $r['status'] === 'Absent')); ?>
  <div class="stats">
    <div class="stat"><b><?= count($rows) ?></b><span>Registered</span></div>
    <div class="stat gold"><b><?= $p ?></b><span>Present</span></div>
    <div class="stat"><b><?= $a ?></b><span>Absent</span></div>
    <div class="stat gold"><b><?= count($rows) - $p - $a ?></b><span>Not marked</span></div>
  </div>
  <?php if (!$rows): ?><div class="empty">Nobody registered for this event.</div><?php else: ?>
  <div class="table-wrap"><table><thead><tr><th>#</th><th>Student</th><th>Email</th><th>Phone</th><th>Attendance</th></tr></thead><tbody>
    <?php foreach ($rows as $i => $r): ?><tr><td><?= $i + 1 ?></td><td><?= e($r['name']) ?></td><td><?= e($r['email']) ?></td><td><?= e($r['phone']) ?></td><td><?= e($r['status'] ?? 'Not marked') ?></td></tr><?php endforeach; ?>
  </tbody></table></div><?php endif; ?>
<?php elseif (!$sum): ?>
  <div class="empty">No events to report on yet.</div>
<?php else: ?>
  <div class="table-wrap"><table><thead><tr><th>Event</th><th>Date</th><th>Registered</th><th>Present</th><th>Absent</th><th>Not marked</th><th>Turnout</th><th class="no-print"></th></tr></thead><tbody>
  <?php foreach ($sum as $r): $reg = (int)$r['registered']; $pr = (int)$r['present']; $ab = (int)$r['absent']; ?>
    <tr><td><strong><?= e($r['event_name']) ?></strong></td><td><?= e(fmt_date($r['event_date'])) ?></td><td><?= $reg ?></td><td><?= $pr ?></td><td><?= $ab ?></td><td><?= $reg - $pr - $ab ?></td>
      <td><?= ($pr + $ab) > 0 ? round($pr / ($pr + $ab) * 100) . '%' : '-' ?></td>
      <td class="actions no-print"><a class="btn btn-small btn-ghost" href="?event_id=<?= (int)$r['event_id'] ?>">Details</a></td></tr>
  <?php endforeach; ?></tbody></table></div>
  <p class="hint">Turnout = present / (present + absent), counting only students whose attendance is marked.</p>
<?php endif; ?>
<?php include ROOT_PATH . '/includes/footer.php'; ?>
