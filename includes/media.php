<?php
/**
 * Shared rules for post-event report attachments.
 *
 * A report carries two kinds of evidence: photographs of the event, which are
 * printed into the PDF, and documents - bills, attendance proof - which cannot
 * be merged into it by the browser generator and so are stored on disk and
 * listed in an annexure linking back to download_media.php.
 *
 * Photographs are capped at two per day of the event, so a five-day event may
 * carry ten. The cap is what stops a report becoming a photo album, and it is
 * enforced here as well as being stated in the workspace.
 *
 * Video was accepted briefly and is not any more: a PDF cannot play one, and
 * bills and attendance proof are what the report is actually for. Rows for any
 * video uploaded in that window still serve and still list in the annexure.
 *
 * Nothing under uploads/ may be served directly by the web server; .htaccess
 * and web.config both block the segment. download_media.php is the only
 * supported way to read one of these files.
 */

require_once __DIR__ . '/workflow.php';

/** Where attachments live, relative to the application root. */
const EC_MEDIA_DIR = 'uploads/media';

/**
 * Per-kind limits.
 *
 * The ceilings here are the application's own. PHP enforces its own limits
 * first (upload_max_filesize / post_max_size in php.ini), so those must be at
 * least as large or the convener sees PHP's error instead of ours. See README.
 */
function ec_media_rules(): array
{
    return [
        'photo' => [
            'label'     => 'Photograph',
            'max_bytes' => 10 * 1024 * 1024,
            'ext'       => ['jpg', 'jpeg', 'png', 'webp', 'gif'],
            'mime'      => ['image/jpeg', 'image/png', 'image/webp', 'image/gif'],
        ],
        'document' => [
            'label'     => 'Document',
            'max_bytes' => 25 * 1024 * 1024,
            'ext'       => ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'csv'],
            'mime'      => [
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'application/vnd.ms-powerpoint',
                'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                'text/plain',
                'text/csv',
                // Office files are zip containers and plain CSV/TXT are sniffed
                // inconsistently across platforms; accept the generic readings
                // rather than rejecting a legitimate attendance sheet.
                'application/zip',
                'application/octet-stream',
            ],
        ],
    ];
}

/** Photographs allowed per day of the event. */
const EC_PHOTOS_PER_DAY = 2;

/** Bounds on the convener's account of what happened. */
const EC_SUMMARY_MIN_WORDS = 50;
const EC_SUMMARY_MAX_WORDS = 400;

/** How many days the event ran, counting both end days. */
function ec_event_days(array $proposal): int
{
    if (empty($proposal['start_date']) || empty($proposal['end_date'])) {
        return 1;
    }
    $start = strtotime((string) $proposal['start_date']);
    $end   = strtotime((string) $proposal['end_date']);
    if ($start === false || $end === false || $end < $start) {
        return 1;
    }
    return (int) floor(($end - $start) / 86400) + 1;
}

/** Photographs this event may carry in total. */
function ec_photo_allowance(array $proposal): int
{
    return ec_event_days($proposal) * EC_PHOTOS_PER_DAY;
}

/** How many attachments of one kind a proposal already has. */
function ec_media_count(mysqli $conn, int $proposalId, string $kind): int
{
    $stmt = $conn->prepare('SELECT COUNT(*) FROM proposal_media WHERE proposal_id = ? AND kind = ?');
    $stmt->bind_param('is', $proposalId, $kind);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();
    return (int) $count;
}

/**
 * The attendance figures a report records.
 *
 * A headcount alone does not answer what the department is asked - who came -
 * so the four groups are counted separately and totalled.
 */
function ec_attendance_fields(): array
{
    return [
        'att_internal_students' => 'Internal students',
        'att_external_students' => 'External students',
        'att_internal_faculty'  => 'Internal faculty',
        'att_external_faculty'  => 'External faculty',
    ];
}

/** Words in a summary, for the minimum length the report asks for. */
function ec_word_count(string $text): int
{
    $trimmed = trim($text);
    if ($trimmed === '') {
        return 0;
    }
    return count(preg_split('/\s+/u', $trimmed) ?: []);
}

/** True when $kind is one the application accepts. */
function ec_media_kind_valid(string $kind): bool
{
    return array_key_exists($kind, ec_media_rules());
}

/** Human-readable size, used in the annexure and in the upload list. */
function ec_media_human_size(int $bytes): string
{
    if ($bytes >= 1048576) {
        return round($bytes / 1048576, 1) . ' MB';
    }
    if ($bytes >= 1024) {
        return round($bytes / 1024) . ' KB';
    }
    return $bytes . ' B';
}

/**
 * Loads a proposal if - and only if - the caller may see it.
 *
 * The permission model is the one api_proposal_details.php and
 * download_report.php already use: the owning convener, or an HOD/coordinator
 * in the same department. A caller who may not see the proposal gets null, so
 * callers cannot tell "not yours" apart from "does not exist".
 *
 * @return array|null id, user_id, title, status, start_date, end_date,
 *                    report_path and is_owner
 */
function ec_report_context(mysqli $conn, int $proposalId, int $userId, string $role): ?array
{
    $role = strtoupper($role);

    if ($role === 'HOD' || $role === 'COORDINATOR') {
        $stmt = $conn->prepare(
            'SELECT p.id, p.user_id, p.title, p.status, p.start_date, p.end_date, p.report_path
               FROM proposals p
               JOIN users u ON p.user_id = u.id
              WHERE p.id = ? AND u.department = (SELECT department FROM users WHERE id = ?)'
        );
    } else {
        $stmt = $conn->prepare(
            'SELECT id, user_id, title, status, start_date, end_date, report_path
               FROM proposals WHERE id = ? AND user_id = ?'
        );
    }
    $stmt->bind_param('ii', $proposalId, $userId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$row) {
        return null;
    }
    $row['is_owner'] = ((int) $row['user_id'] === $userId);
    return $row;
}

/**
 * True once the event has finished, which is what unlocks the report section.
 *
 * Mirrors the isCompleted rule the dashboards apply in JavaScript: an approved
 * event whose end date has passed.
 */
function ec_event_completed(array $proposal): bool
{
    if (($proposal['status'] ?? '') !== 'Approved' || empty($proposal['end_date'])) {
        return false;
    }
    return strtotime($proposal['end_date'] . ' 23:59:59') < time();
}

/**
 * True once a final report exists.
 *
 * The report modal tells the convener the report cannot be edited after it is
 * generated, so attachments freeze at that point and a second generation is
 * refused.
 */
function ec_report_finalised(array $proposal): bool
{
    return !empty($proposal['report_path']);
}

/** Every attachment on a proposal, oldest first, shaped for JSON. */
function ec_media_list(mysqli $conn, int $proposalId): array
{
    $stmt = $conn->prepare(
        'SELECT id, kind, original_name, mime_type, size_bytes, created_at
           FROM proposal_media WHERE proposal_id = ? ORDER BY kind, id'
    );
    $stmt->bind_param('i', $proposalId);
    $stmt->execute();
    $res = $stmt->get_result();

    $rows = [];
    while ($r = $res->fetch_assoc()) {
        $rows[] = [
            'id'            => (int) $r['id'],
            'kind'          => $r['kind'],
            'original_name' => $r['original_name'],
            'mime_type'     => $r['mime_type'],
            'size_bytes'    => (int) $r['size_bytes'],
            'size_human'    => ec_media_human_size((int) $r['size_bytes']),
            'url'           => 'download_media.php?id=' . (int) $r['id'],
            'created_at'    => $r['created_at'],
        ];
    }
    $stmt->close();
    return $rows;
}

/**
 * Absolute base URL of the installation.
 *
 * The annexure prints QR codes and the reminder e-mails carry links, both of
 * which are read away from the browser that produced them, so a relative path
 * is useless. Configured explicitly for CLI (cron has no request to infer
 * from); derived from the request otherwise.
 */
function ec_base_url(): string
{
    $configured = getenv('APP_URL');
    if ($configured === false || $configured === '') {
        $configFile = __DIR__ . '/config.local.php';
        $local = is_file($configFile) ? require $configFile : [];
        $configured = $local['app_url'] ?? '';
    }
    if ($configured !== '') {
        return rtrim($configured, '/');
    }

    if (PHP_SAPI === 'cli' || empty($_SERVER['HTTP_HOST'])) {
        return 'http://localhost';
    }

    $https  = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    $scheme = $https ? 'https' : 'http';
    // Directory the application is deployed under, e.g. /eventconnect.
    $dir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
    return $scheme . '://' . $_SERVER['HTTP_HOST'] . $dir;
}
