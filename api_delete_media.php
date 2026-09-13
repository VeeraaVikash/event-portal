<?php
/**
 * Removes one attachment from a post-event report.
 *
 * Same window as uploading: the owning convener only, and only until the final
 * report has been generated. The row and the file go together, so storage does
 * not accumulate files nothing points at.
 */

require_once 'includes/workflow.php';
require_once 'includes/db.php';
require_once 'includes/media.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    ec_json(['status' => 'error', 'message' => 'Unauthorized'], 401);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ec_json(['status' => 'error', 'message' => 'Invalid request method'], 405);
    exit;
}

$body = json_decode(file_get_contents('php://input'), true) ?: [];

if (!ec_csrf_valid($body)) {
    ec_json(['status' => 'error', 'message' => 'Invalid or missing security token. Please reload the page.'], 403);
    exit;
}

$mediaId = (int) ($body['media_id'] ?? $_POST['media_id'] ?? 0);
if ($mediaId <= 0) {
    ec_json(['status' => 'error', 'message' => 'Missing attachment id'], 400);
    exit;
}

// Read the attachment first so its proposal can be permission-checked.
$stmt = $conn->prepare('SELECT proposal_id, stored_path FROM proposal_media WHERE id = ?');
$stmt->bind_param('i', $mediaId);
$stmt->execute();
$media = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$media) {
    ec_json(['status' => 'error', 'message' => 'Attachment not found'], 404);
    exit;
}

$proposalId = (int) $media['proposal_id'];
$proposal = ec_report_context($conn, $proposalId, (int) $_SESSION['id'], (string) ($_SESSION['role'] ?? ''));

if (!$proposal) {
    ec_json(['status' => 'error', 'message' => 'Attachment not found'], 404);
    exit;
}
if (!$proposal['is_owner']) {
    ec_json(['status' => 'error', 'message' => 'Only the convener of this event can remove attachments.'], 403);
    exit;
}
if (ec_report_finalised($proposal)) {
    ec_json([
        'status'  => 'error',
        'message' => 'The report for this event has already been generated and cannot be changed.',
    ], 409);
    exit;
}

try {
    $del = $conn->prepare('DELETE FROM proposal_media WHERE id = ?');
    $del->bind_param('i', $mediaId);
    $del->execute();
    $del->close();
} catch (Throwable $e) {
    $ref = ec_log_exception($e, 'delete_media');
    ec_json(['status' => 'error', 'message' => "Could not remove the attachment. Reference: {$ref}"], 500);
    exit;
}

// Only unlink once the row is gone, and only inside uploads/.
$mediaRoot = realpath(__DIR__ . '/uploads');
$absolute  = realpath(__DIR__ . '/' . ltrim((string) $media['stored_path'], '/\\'));
if ($mediaRoot !== false && $absolute !== false
    && strncmp($absolute, $mediaRoot . DIRECTORY_SEPARATOR, strlen($mediaRoot) + 1) === 0
    && is_file($absolute)) {
    @unlink($absolute);
}

ec_json([
    'status' => 'success',
    'media'  => ec_media_list($conn, $proposalId),
]);
