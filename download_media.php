<?php
/**
 * Authenticated download for report attachments.
 *
 * uploads/ is blocked at the web server (see .htaccess and web.config), so this
 * is the only way to read one of these files. The permission model is the one
 * download_report.php and api_proposal_details.php use: the owning convener, or
 * an HOD/coordinator in the same department.
 *
 * The report's annexure prints a link and a QR code pointing here for every
 * video and document, so these URLs are opened long after the report is made -
 * from a phone, by someone who must still be signed in.
 */

session_start();
require_once 'includes/db.php';

/** Answers without revealing whether the attachment exists. */
function ec_media_deny(int $code, string $message): void
{
    http_response_code($code);
    header('Content-Type: text/plain; charset=utf-8');
    echo $message;
    exit;
}

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    ec_media_deny(401, 'You must be signed in to open this attachment.');
}

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id <= 0) {
    ec_media_deny(400, 'Missing or invalid attachment id.');
}

$userId = (int) $_SESSION['id'];
$role   = strtoupper($_SESSION['role'] ?? '');

// Authorization is part of the query, so a caller who may not see the proposal
// simply gets no row back.
if ($role === 'HOD' || $role === 'COORDINATOR') {
    $stmt = $conn->prepare(
        'SELECT m.stored_path, m.original_name, m.mime_type, m.kind
           FROM proposal_media m
           JOIN proposals p ON m.proposal_id = p.id
           JOIN users u ON p.user_id = u.id
          WHERE m.id = ? AND u.department = (SELECT department FROM users WHERE id = ?)'
    );
} else {
    $stmt = $conn->prepare(
        'SELECT m.stored_path, m.original_name, m.mime_type, m.kind
           FROM proposal_media m
           JOIN proposals p ON m.proposal_id = p.id
          WHERE m.id = ? AND p.user_id = ?'
    );
}
$stmt->bind_param('ii', $id, $userId);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$row) {
    ec_media_deny(404, 'Attachment not found.');
}

// The path comes from the database, never from the request. Confirm it still
// resolves inside uploads/ in case a legacy row holds something unexpected.
$mediaRoot = realpath(__DIR__ . '/uploads');
$absolute  = realpath(__DIR__ . '/' . ltrim((string) $row['stored_path'], '/\\'));

if ($mediaRoot === false || $absolute === false
    || strncmp($absolute, $mediaRoot . DIRECTORY_SEPARATOR, strlen($mediaRoot) + 1) !== 0
    || !is_file($absolute)) {
    ec_media_deny(404, 'The file is missing from storage.');
}

$size = filesize($absolute);
$mime = $row['mime_type'] ?: 'application/octet-stream';

// Photographs, videos, PDFs and text open in the browser; anything else is a
// download, since the browser would only offer to save it anyway.
$inline = ($row['kind'] === 'photo' || $row['kind'] === 'video'
    || $mime === 'application/pdf' || str_starts_with($mime, 'text/'));

$filename = str_replace(['"', "\r", "\n"], '', (string) $row['original_name']);

header('Content-Type: ' . $mime);
header('Content-Disposition: ' . ($inline ? 'inline' : 'attachment') . '; filename="' . $filename . '"');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: private, no-store');
header('Accept-Ranges: bytes');

// Byte ranges, so a video can be scrubbed instead of downloaded whole.
$start = 0;
$end   = $size - 1;
if (!empty($_SERVER['HTTP_RANGE']) && preg_match('/bytes=(\d*)-(\d*)/', $_SERVER['HTTP_RANGE'], $m)) {
    $rangeStart = ($m[1] === '') ? null : (int) $m[1];
    $rangeEnd   = ($m[2] === '') ? null : (int) $m[2];

    if ($rangeStart === null && $rangeEnd !== null) {
        // "last N bytes"
        $start = max(0, $size - $rangeEnd);
    } else {
        $start = $rangeStart ?? 0;
        if ($rangeEnd !== null) {
            $end = min($rangeEnd, $size - 1);
        }
    }

    if ($start > $end || $start >= $size) {
        http_response_code(416);
        header('Content-Range: bytes */' . $size);
        exit;
    }

    http_response_code(206);
    header('Content-Range: bytes ' . $start . '-' . $end . '/' . $size);
}

header('Content-Length: ' . ($end - $start + 1));

$handle = fopen($absolute, 'rb');
if ($handle === false) {
    ec_media_deny(404, 'The file could not be read.');
}
fseek($handle, $start);

$remaining = $end - $start + 1;
while ($remaining > 0 && !feof($handle)) {
    $chunk = fread($handle, (int) min(262144, $remaining));
    if ($chunk === false) {
        break;
    }
    echo $chunk;
    $remaining -= strlen($chunk);
    flush();
}
fclose($handle);
