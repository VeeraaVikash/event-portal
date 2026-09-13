<?php
/**
 * Stores photographs, videos and documents for a post-event report.
 *
 * Uploads are kept on the server rather than only inside the generated PDF:
 * a PDF cannot carry a video, the convener needs to be able to attach files
 * across several sittings, and the HOD needs the originals afterwards. The
 * report's annexure links back to download_media.php for each one.
 *
 * Only the owning convener may upload, only after the event has ended, and
 * only until the final report is generated - the report modal promises the
 * report cannot be edited afterwards, so the attachments freeze with it.
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

// A body over post_max_size arrives with $_POST and $_FILES both empty, which
// would otherwise look like a missing CSRF token.
if (empty($_POST) && empty($_FILES) && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
    ec_json([
        'status'  => 'error',
        'message' => 'The upload is larger than the server accepts. Send fewer files at a time, or raise post_max_size in php.ini.',
    ], 413);
    exit;
}

if (!ec_csrf_valid()) {
    ec_json(['status' => 'error', 'message' => 'Invalid or missing security token. Please reload the page.'], 403);
    exit;
}

$proposalId = (int) ($_POST['proposal_id'] ?? 0);
$kind       = strtolower(trim((string) ($_POST['kind'] ?? '')));

if ($proposalId <= 0) {
    ec_json(['status' => 'error', 'message' => 'Missing proposal ID'], 400);
    exit;
}
if (!ec_media_kind_valid($kind)) {
    ec_json(['status' => 'error', 'message' => 'Unknown attachment type'], 400);
    exit;
}

$proposal = ec_report_context($conn, $proposalId, (int) $_SESSION['id'], (string) ($_SESSION['role'] ?? ''));
if (!$proposal) {
    ec_json(['status' => 'error', 'message' => 'Proposal not found'], 404);
    exit;
}
if (!$proposal['is_owner']) {
    ec_json(['status' => 'error', 'message' => 'Only the convener of this event can attach files.'], 403);
    exit;
}
if (!ec_event_completed($proposal)) {
    ec_json([
        'status'  => 'error',
        'message' => 'Attachments can only be added once the event has ended.',
    ], 409);
    exit;
}
if (ec_report_finalised($proposal)) {
    ec_json([
        'status'  => 'error',
        'message' => 'The report for this event has already been generated and cannot be changed.',
    ], 409);
    exit;
}

if (!isset($_FILES['files'])) {
    ec_json(['status' => 'error', 'message' => 'No files were received'], 400);
    exit;
}

$rules = ec_media_rules()[$kind];

// Normalise both the single-file and multi-file shapes of $_FILES.
$incoming = [];
if (is_array($_FILES['files']['name'])) {
    foreach (array_keys($_FILES['files']['name']) as $i) {
        $incoming[] = [
            'name'     => $_FILES['files']['name'][$i],
            'tmp_name' => $_FILES['files']['tmp_name'][$i],
            'size'     => (int) $_FILES['files']['size'][$i],
            'error'    => (int) $_FILES['files']['error'][$i],
        ];
    }
} else {
    $incoming[] = [
        'name'     => $_FILES['files']['name'],
        'tmp_name' => $_FILES['files']['tmp_name'],
        'size'     => (int) $_FILES['files']['size'],
        'error'    => (int) $_FILES['files']['error'],
    ];
}

$uploadErrors = [
    UPLOAD_ERR_INI_SIZE   => 'is larger than php.ini allows (upload_max_filesize)',
    UPLOAD_ERR_FORM_SIZE  => 'is larger than the form allows',
    UPLOAD_ERR_PARTIAL    => 'was only partially uploaded; please retry',
    UPLOAD_ERR_NO_FILE    => 'was empty',
    UPLOAD_ERR_NO_TMP_DIR => 'could not be stored (server has no temp directory)',
    UPLOAD_ERR_CANT_WRITE => 'could not be written to disk',
    UPLOAD_ERR_EXTENSION  => 'was blocked by the server',
];

$targetDir = __DIR__ . '/' . EC_MEDIA_DIR . '/' . $proposalId;
if (!is_dir($targetDir) && !mkdir($targetDir, 0755, true) && !is_dir($targetDir)) {
    $ref = ec_log_exception(new RuntimeException("Cannot create {$targetDir}"), 'upload_media');
    ec_json(['status' => 'error', 'message' => "Attachment storage is unavailable. Reference: {$ref}"], 500);
    exit;
}

$finfo    = new finfo(FILEINFO_MIME_TYPE);
$accepted = 0;
$rejected = [];

foreach ($incoming as $file) {
    $displayName = mb_substr((string) $file['name'], 0, 255);

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $rejected[] = $displayName . ' ' . ($uploadErrors[$file['error']] ?? 'could not be uploaded');
        continue;
    }
    if (!is_uploaded_file($file['tmp_name'])) {
        $rejected[] = $displayName . ' was not a valid upload';
        continue;
    }
    if ($file['size'] <= 0) {
        $rejected[] = $displayName . ' is empty';
        continue;
    }
    if ($file['size'] > $rules['max_bytes']) {
        $rejected[] = sprintf('%s is %s; the limit for a %s is %s',
            $displayName,
            ec_media_human_size($file['size']),
            strtolower($rules['label']),
            ec_media_human_size($rules['max_bytes']));
        continue;
    }

    $ext = strtolower(pathinfo($displayName, PATHINFO_EXTENSION));
    if (!in_array($ext, $rules['ext'], true)) {
        $rejected[] = sprintf('%s is not an accepted %s type (%s)',
            $displayName, strtolower($rules['label']), implode(', ', $rules['ext']));
        continue;
    }

    // Trust the file's own bytes rather than the name or the browser's type.
    $mime = $finfo->file($file['tmp_name']) ?: 'application/octet-stream';
    if (!in_array($mime, $rules['mime'], true)) {
        $rejected[] = $displayName . ' does not look like a ' . strtolower($rules['label']);
        continue;
    }
    if ($kind === 'photo' && @getimagesize($file['tmp_name']) === false) {
        $rejected[] = $displayName . ' is not a readable image';
        continue;
    }

    // Stored under a generated name: the original is only ever displayed.
    $storedName = $kind . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
    $relative   = EC_MEDIA_DIR . '/' . $proposalId . '/' . $storedName;

    if (!move_uploaded_file($file['tmp_name'], __DIR__ . '/' . $relative)) {
        $rejected[] = $displayName . ' could not be stored';
        continue;
    }

    try {
        $stmt = $conn->prepare(
            'INSERT INTO proposal_media (proposal_id, kind, original_name, stored_path, mime_type, size_bytes, uploaded_by)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        $size   = (int) $file['size'];
        $userId = (int) $_SESSION['id'];
        $stmt->bind_param('issssii', $proposalId, $kind, $displayName, $relative, $mime, $size, $userId);
        $stmt->execute();
        $stmt->close();
        $accepted++;
    } catch (Throwable $e) {
        // Keep disk and database consistent rather than leaving an orphan.
        @unlink(__DIR__ . '/' . $relative);
        $ref = ec_log_exception($e, 'upload_media');
        $rejected[] = $displayName . " could not be recorded (reference {$ref})";
    }
}

ec_json([
    'status'   => $accepted > 0 ? 'success' : 'error',
    'accepted' => $accepted,
    'message'  => $rejected ? implode('; ', $rejected) : '',
    'media'    => ec_media_list($conn, $proposalId),
], $accepted > 0 ? 200 : 400);
