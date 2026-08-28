<?php
// Authenticated download for event report PDFs.
//
// Reports live in reports/, which on a real deployment must NOT be served
// directly by the web server (see web.config). Filenames are predictable -
// Report_PRO_0007_1776167030.pdf - so a directly reachable reports/ directory
// lets anyone enumerate and download every report without logging in. This
// script is the only supported way to read one.
//
// The permission model matches api_proposal_details.php exactly: the owning
// convener, or an HOD/coordinator in the same department.

session_start();
require_once 'includes/db.php';

// Send a status without leaking whether the proposal exists.
function ec_report_deny(int $code, string $message): void {
    http_response_code($code);
    header('Content-Type: text/plain; charset=utf-8');
    echo $message;
    exit;
}

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    ec_report_deny(401, 'You must be signed in to download a report.');
}

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id <= 0) {
    ec_report_deny(400, 'Missing or invalid proposal id.');
}

$user_id = (int) $_SESSION['id'];
$role    = strtoupper($_SESSION['role'] ?? '');

// Resolve the stored path through an authorization-scoped query, so a caller
// who may not see the proposal simply gets no row.
if ($role === 'HOD' || $role === 'COORDINATOR') {
    $stmt = $conn->prepare(
        'SELECT p.report_path FROM proposals p
         JOIN users u ON p.user_id = u.id
         WHERE p.id = ? AND u.department = (SELECT department FROM users WHERE id = ?)'
    );
    $stmt->bind_param('ii', $id, $user_id);
} else {
    $stmt = $conn->prepare(
        'SELECT report_path FROM proposals WHERE id = ? AND user_id = ?'
    );
    $stmt->bind_param('ii', $id, $user_id);
}
$stmt->execute();
$stmt->bind_result($reportPath);
$found = $stmt->fetch();
$stmt->close();

if (!$found) {
    ec_report_deny(404, 'Report not found.');
}
if ($reportPath === null || $reportPath === '') {
    ec_report_deny(404, 'No report has been uploaded for this proposal yet.');
}

// The path comes from the database, never from the request, so traversal is
// already impossible. Confirm it resolves inside reports/ anyway, in case a
// legacy row holds something unexpected.
$reportsDir = realpath(__DIR__ . '/reports');
$absolute   = realpath(__DIR__ . '/' . ltrim($reportPath, '/\\'));

if ($reportsDir === false || $absolute === false
    || strncmp($absolute, $reportsDir . DIRECTORY_SEPARATOR, strlen($reportsDir) + 1) !== 0
    || !is_file($absolute)) {
    ec_report_deny(404, 'The report file is missing from storage.');
}

// Inline so the browser's PDF viewer opens it, as the dashboards expect.
header('Content-Type: application/pdf');
header('Content-Length: ' . filesize($absolute));
header('Content-Disposition: inline; filename="' . basename($absolute) . '"');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: private, no-store');

readfile($absolute);
