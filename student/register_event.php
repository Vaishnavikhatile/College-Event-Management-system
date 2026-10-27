<?php
require dirname(__DIR__) . '/config.php';
require_student();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect(BASE_URL . '/events.php');
csrf_verify();
$id = (int)($_POST['event_id'] ?? 0);
$ev = fetch_one("SELECT event_id, event_name, event_date FROM events WHERE event_id = ?", 'i', [$id]);
if (!$ev) { flash('That event does not exist.', 'error'); redirect(BASE_URL . '/events.php'); }
if ($ev['event_date'] < date('Y-m-d')) { flash('Registration is closed. This event is already over.', 'error'); redirect(BASE_URL . '/event.php?id=' . $id); }
if (fetch_one("SELECT registration_id FROM registrations WHERE student_id = ? AND event_id = ?", 'ii', [$_SESSION['student_id'], $id])) {
    flash('You are already registered for this event.', 'error');
} else {
    q("INSERT INTO registrations (student_id, event_id) VALUES (?,?)", 'ii', [$_SESSION['student_id'], $id]);
    flash('You are registered for ' . $ev['event_name'] . '.');
}
redirect(BASE_URL . '/student/my_events.php');
