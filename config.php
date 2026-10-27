<?php
/* Database settings: change these to match your MySQL server (XAMPP/WAMP defaults shown). */
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'college_events');

if (session_status() === PHP_SESSION_NONE) { session_start(); }
date_default_timezone_set('Asia/Kolkata');

define('ROOT_PATH', __DIR__);
$docRoot = rtrim(str_replace('\\', '/', (string)realpath($_SERVER['DOCUMENT_ROOT'] ?? '')), '/');
$appRoot = str_replace('\\', '/', __DIR__);
define('BASE_URL', ($docRoot !== '' && strpos($appRoot, $docRoot) === 0) ? substr($appRoot, strlen($docRoot)) : '');

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $conn->set_charset('utf8mb4');
} catch (mysqli_sql_exception $ex) {
    die('Could not connect to the database. Import database.sql and check the settings in config.php.');
}

/* ---------- helpers ---------- */
function e($v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function redirect(string $url): void { header('Location: ' . $url); exit; }
function fmt_date(string $d): string { return date('d M Y', strtotime($d)); }
function fmt_time(string $t): string { return date('h:i A', strtotime($t)); }

function q(string $sql, string $types = '', array $params = []): mysqli_stmt {
    global $conn;
    $st = $conn->prepare($sql);
    if ($types !== '') { $st->bind_param($types, ...$params); }
    $st->execute();
    return $st;
}
function fetch_all(string $sql, string $types = '', array $params = []): array {
    return q($sql, $types, $params)->get_result()->fetch_all(MYSQLI_ASSOC);
}
function fetch_one(string $sql, string $types = '', array $params = []): ?array {
    $rows = fetch_all($sql, $types, $params);
    return $rows[0] ?? null;
}

function csrf_field(): string {
    if (empty($_SESSION['csrf'])) { $_SESSION['csrf'] = bin2hex(random_bytes(32)); }
    return '<input type="hidden" name="csrf" value="' . e($_SESSION['csrf']) . '">';
}
function csrf_verify(): void {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $ok = !empty($_POST['csrf']) && !empty($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $_POST['csrf']);
        if (!$ok) { http_response_code(400); die('Your session expired. Go back, refresh the page and try again.'); }
    }
}

function flash(string $msg, string $type = 'success'): void { $_SESSION['flash'] = [$msg, $type]; }
function show_flash(): void {
    if (!empty($_SESSION['flash'])) {
        [$m, $t] = $_SESSION['flash'];
        echo '<div class="flash flash-' . e($t) . '" role="status">' . e($m) . '</div>';
        unset($_SESSION['flash']);
    }
}

function require_student(): void {
    if (empty($_SESSION['student_id'])) { flash('Log in to continue.', 'error'); redirect(BASE_URL . '/login.php'); }
}
function require_admin(): void {
    if (empty($_SESSION['admin_id'])) { flash('Admin login required.', 'error'); redirect(BASE_URL . '/admin/login.php'); }
}
