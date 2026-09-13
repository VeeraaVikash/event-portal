<?php
/**
 * Builds a ZIP of a department's event reports for a rolling period.
 *
 * Reports are stored under reports/, which the web server does not serve, so
 * this is the only way to collect a period's worth of them. The same
 * department boundary as every other view applies: an HOD or coordinator gets
 * their own department and nothing else.
 *
 *   download_report_archive.php?period=3m
 *   download_report_archive.php?period=12m&include_media=1
 *
 * Attachments are photographs, bills and attendance proof. Legacy video rows
 * from the window when video was accepted are never bundled - a year's worth
 * would be unusable as a download - and index.csv carries the URL that serves
 * each one instead.
 */

require_once 'includes/workflow.php';
require_once 'includes/db.php';
require_once 'includes/analytics.php';

/** Plain-text failure, since the browser expected a file download. */
function ec_archive_deny(int $code, string $message): void
{
    http_response_code($code);
    header('Content-Type: text/plain; charset=utf-8');
    echo $message;
    exit;
}

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    ec_archive_deny(401, 'You must be signed in to download the archive.');
}
if (!ec_may_view_analytics((string) ($_SESSION['role'] ?? ''))) {
    ec_archive_deny(403, 'The report archive is for HODs and coordinators.');
}
if (!class_exists('ZipArchive')) {
    ec_archive_deny(500, 'This server has no ZIP support (php-zip is not installed).');
}

$department = ec_viewer_department($conn, (int) $_SESSION['id']);
if ($department === null) {
    ec_archive_deny(409, 'Your account has no department set.');
}

$period       = ec_period_range((string) ($_GET['period'] ?? '3m'));
$includeMedia = !empty($_GET['include_media']);

/** Total bytes the archive may draw from disk before it gives up. */
const EC_ARCHIVE_MAX_BYTES = 512 * 1024 * 1024;

$data = ec_analytics($conn, $department, $period);

/** Makes a filename that survives Windows, macOS and e-mail. */
function ec_archive_slug(string $text, int $max = 60): string
{
    $slug = preg_replace('/[^A-Za-z0-9]+/', '-', $text) ?? '';
    $slug = trim($slug, '-');
    return $slug === '' ? 'untitled' : substr($slug, 0, $max);
}

/** A CSV file as a string, for writing straight into the archive. */
function ec_archive_csv(array $header, array $rows): string
{
    $handle = fopen('php://temp', 'r+');
    fputcsv($handle, $header);
    foreach ($rows as $row) {
        fputcsv($handle, $row);
    }
    rewind($handle);
    $csv = stream_get_contents($handle);
    fclose($handle);
    // Excel opens UTF-8 CSVs as the system codepage unless it sees a BOM.
    return "\xEF\xBB\xBF" . $csv;
}

/**
 * A scratch file the archive can be built in.
 *
 * sys_get_temp_dir() is the obvious choice and is wrong often enough to matter:
 * on this XAMPP it resolves to the launching user's private macOS temp
 * directory, which the Apache user cannot write. PHP's own upload directory is
 * writable by definition - uploads land there - so it is tried too, and
 * uploads/tmp last for hosts that lock both down.
 */
function ec_archive_tempfile(): ?string
{
    $candidates = [
        sys_get_temp_dir(),
        rtrim((string) ini_get('upload_tmp_dir'), '/\\'),
        __DIR__ . '/uploads/tmp',
    ];

    foreach ($candidates as $dir) {
        if ($dir === '') {
            continue;
        }
        if (!is_dir($dir) && !@mkdir($dir, 0755, true) && !is_dir($dir)) {
            continue;
        }
        $tmp = @tempnam($dir, 'ecarchive');
        if ($tmp !== false) {
            return $tmp;
        }
    }
    return null;
}

$base = ec_archive_slug($department, 40) . '_' . $period['from'] . '_to_' . $period['to'];
$tmp  = ec_archive_tempfile();
if ($tmp === null) {
    ec_archive_deny(500,
        "Could not create a temporary file for the archive. The web server user needs write "
        . "access to one of: the system temp directory, PHP's upload_tmp_dir, or uploads/tmp.");
}

$zip = new ZipArchive();
if ($zip->open($tmp, ZipArchive::OVERWRITE) !== true) {
    @unlink($tmp);
    ec_archive_deny(500, 'Could not open the archive for writing.');
}

$t = $data['totals'];

/* ------------------------------------------------------------- SUMMARY */

$lines = [];
$lines[] = 'SRM EVENT CONNECT - DEPARTMENT REPORT ARCHIVE';
$lines[] = str_repeat('=', 62);
$lines[] = '';
$lines[] = 'Department : ' . $department;
$lines[] = 'Period     : ' . $period['label'] . ' (' . $period['from'] . ' to ' . $period['to'] . ')';
$lines[] = 'Generated  : ' . $data['generated'];
$lines[] = 'By         : ' . ($_SESSION['full_name'] ?? 'unknown') . ' (' . ($_SESSION['role'] ?? '') . ')';
$lines[] = '';
$lines[] = 'HEADLINE';
$lines[] = str_repeat('-', 62);
$lines[] = sprintf('  Events held                  %d', $t['events_held']);
$lines[] = sprintf('  Participants attended        %s', number_format($t['attended']));
$lines[] = sprintf('  Against expected             %s (%d%%)', number_format($t['expected']), $t['attendance_percent']);
$lines[] = sprintf('    Students  internal %s / external %s',
    number_format($data['groups']['att_internal_students']),
    number_format($data['groups']['att_external_students']));
$lines[] = sprintf('    Faculty   internal %s / external %s',
    number_format($data['groups']['att_internal_faculty']),
    number_format($data['groups']['att_external_faculty']));
$lines[] = sprintf('  Budget across those events   %s', ec_money($t['budget']));
$lines[] = sprintf('  Reports filed                %d of %d (%d%%)', $t['reports_filed'], $t['events_held'], $t['reports_percent']);
$lines[] = sprintf('  Reports still outstanding    %d', $t['reports_pending']);
$lines[] = sprintf('  Attachments on file          %d', $t['attachments']);
if ($t['events_held'] > 0 && $t['attendance_recorded'] < $t['events_held']) {
    $lines[] = '';
    $lines[] = sprintf('  Note: %d of %d events have a recorded attendance figure. For the',
        $t['attendance_recorded'], $t['events_held']);
    $lines[] = '  rest the expected count is used, as no turnout was entered when the';
    $lines[] = '  report was generated.';
}
$lines[] = '';
$lines[] = 'BY FACULTY';
$lines[] = str_repeat('-', 62);
$lines[] = sprintf('  %-28s %6s %12s %10s', 'Convener', 'Events', 'Attended', 'Reports');
foreach ($data['faculty'] as $f) {
    $lines[] = sprintf('  %-28s %6d %12s %6d/%d',
        substr($f['name'], 0, 28), $f['events'], number_format($f['attended']),
        $f['reports_filed'], $f['events']);
}
if (!$data['faculty']) {
    $lines[] = '  (no events held in this period)';
}
$lines[] = '';
$lines[] = 'BY CATEGORY';
$lines[] = str_repeat('-', 62);
foreach ($data['categories'] as $key => $count) {
    $lines[] = sprintf('  %-40s %d', ec_category_label((string) $key), $count);
}
if (!$data['categories']) {
    $lines[] = '  (none)';
}
$lines[] = '';
$lines[] = 'CONTENTS';
$lines[] = str_repeat('-', 62);
$lines[] = '  index.csv     one row per event in this period';
$lines[] = '  analysis.csv  the figures above, for a spreadsheet';
$lines[] = '  reports/      the generated report PDF for each event that has one';
if ($includeMedia) {
    $lines[] = '  attachments/  photographs and documents, by event';
}
$lines[] = '';
$lines[] = '  Opening anything linked from index.csv requires a signed-in session.';

$zip->addFromString('SUMMARY.txt', implode("\n", $lines) . "\n");

/* --------------------------------------------------------------- index */

$baseUrl = ec_base_url();
$indexRows = [];
foreach ($data['events'] as $e) {
    $indexRows[] = [
        $e['ref'],
        $e['title'],
        ec_category_label((string) $e['category']),
        $e['convener'],
        $e['convener_email'],
        $e['start_date'],
        $e['end_date'],
        $e['status'],
        $e['held'] ? 'yes' : 'no',
        $e['expected'],
        $e['attended'] ?? '',
        $e['breakdown']['att_internal_students'] ?? '',
        $e['breakdown']['att_external_students'] ?? '',
        $e['breakdown']['att_internal_faculty'] ?? '',
        $e['breakdown']['att_external_faculty'] ?? '',
        $e['attendance_recorded'] ? 'recorded' : 'expected figure used',
        number_format($e['budget'], 2, '.', ''),
        $e['report_filed'] ? 'filed' : 'not filed',
        $e['attachments'],
        $baseUrl . '/download_report.php?id=' . $e['id'],
    ];
}
$zip->addFromString('index.csv', ec_archive_csv([
    'Reference', 'Title', 'Category', 'Convener', 'Convener e-mail',
    'Start date', 'End date', 'Status', 'Held', 'Expected participants',
    'Attended', 'Internal students', 'External students', 'Internal faculty',
    'External faculty', 'Attendance source', 'Budget', 'Report', 'Attachments',
    'Report link',
], $indexRows));

/* ------------------------------------------------------------ analysis */

$analysisRows = [
    ['Department', $department],
    ['Period', $period['label']],
    ['From', $period['from']],
    ['To', $period['to']],
    ['Generated', $data['generated']],
    [],
    ['HEADLINE', ''],
    ['Events in period', $t['events']],
    ['Events held', $t['events_held']],
    ['Events cancelled', $t['events_cancelled']],
    ['Participants attended', $t['attended']],
    ['  Internal students', $data['groups']['att_internal_students']],
    ['  External students', $data['groups']['att_external_students']],
    ['  Internal faculty', $data['groups']['att_internal_faculty']],
    ['  External faculty', $data['groups']['att_external_faculty']],
    ['Participants expected', $t['expected']],
    ['Attendance vs expected (%)', $t['attendance_percent']],
    ['Budget', number_format($t['budget'], 2, '.', '')],
    ['Reports filed', $t['reports_filed']],
    ['Reports outstanding', $t['reports_pending']],
    ['Reports filed (%)', $t['reports_percent']],
    [],
    ['BY FACULTY', ''],
    ['Convener', 'E-mail', 'Events', 'Attended', 'Budget', 'Reports filed', 'Reports outstanding'],
];
foreach ($data['faculty'] as $f) {
    $analysisRows[] = [
        $f['name'], $f['email'], $f['events'], $f['attended'],
        number_format($f['budget'], 2, '.', ''), $f['reports_filed'], $f['reports_pending'],
    ];
}
$analysisRows[] = [];
$analysisRows[] = ['BY CATEGORY', ''];
$analysisRows[] = ['Category', 'Events held'];
foreach ($data['categories'] as $key => $count) {
    $analysisRows[] = [ec_category_label((string) $key), $count];
}
$analysisRows[] = [];
$analysisRows[] = ['BY MONTH', ''];
$analysisRows[] = ['Month', 'Events held', 'Participants attended'];
foreach ($data['timeline'] as $m) {
    $analysisRows[] = [$m['label'], $m['events'], $m['attended']];
}
$zip->addFromString('analysis.csv', ec_archive_csv(['Metric', 'Value'], $analysisRows));

/* ---------------------------------------------------- reports and media */

$bytes    = 0;
$added    = 0;
$missing  = [];
$skipped  = [];

foreach ($data['events'] as $e) {
    $folder = $e['ref'] . '_' . ec_archive_slug((string) $e['title']);

    if ($e['report_filed']) {
        $abs = realpath(__DIR__ . '/' . ltrim((string) $e['report_path'], '/\\'));
        $reportsRoot = realpath(__DIR__ . '/reports');
        $inside = $abs !== false && $reportsRoot !== false
            && strncmp($abs, $reportsRoot . DIRECTORY_SEPARATOR, strlen($reportsRoot) + 1) === 0;

        if ($inside && is_file($abs)) {
            $size = filesize($abs);
            if ($bytes + $size > EC_ARCHIVE_MAX_BYTES) {
                $skipped[] = $e['ref'] . ' (archive size limit)';
            } else {
                $zip->addFile($abs, 'reports/' . $e['ref'] . '_' . $e['end_date'] . '_'
                    . ec_archive_slug((string) $e['title']) . '.pdf');
                $bytes += $size;
                $added++;
            }
        } else {
            $missing[] = $e['ref'];
        }
    }

    if (!$includeMedia) {
        continue;
    }

    // Photographs, bills and attendance proof. Legacy videos stay on the server.
    $stmt = $conn->prepare(
        "SELECT kind, original_name, stored_path FROM proposal_media
          WHERE proposal_id = ? AND kind IN ('photo','document') ORDER BY kind, id"
    );
    $stmt->bind_param('i', $e['id']);
    $stmt->execute();
    $res = $stmt->get_result();
    $seen = [];
    while ($m = $res->fetch_assoc()) {
        $abs = realpath(__DIR__ . '/' . ltrim((string) $m['stored_path'], '/\\'));
        $root = realpath(__DIR__ . '/uploads');
        if ($abs === false || $root === false
            || strncmp($abs, $root . DIRECTORY_SEPARATOR, strlen($root) + 1) !== 0
            || !is_file($abs)) {
            continue;
        }
        $size = filesize($abs);
        if ($bytes + $size > EC_ARCHIVE_MAX_BYTES) {
            $skipped[] = $e['ref'] . ' attachments (archive size limit)';
            break;
        }
        // Two uploads can share an original name; keep both.
        $name = $m['original_name'];
        $n = 1;
        while (isset($seen[$name])) {
            $name = pathinfo($m['original_name'], PATHINFO_FILENAME) . '-' . (++$n)
                . '.' . pathinfo($m['original_name'], PATHINFO_EXTENSION);
        }
        $seen[$name] = true;

        $zip->addFile($abs, 'attachments/' . $folder . '/'
            . ($m['kind'] === 'photo' ? 'photos/' : 'documents/') . $name);
        $bytes += $size;
    }
    $stmt->close();
}

if ($missing || $skipped) {
    $note = "Some files could not be included.\n\n";
    if ($missing) {
        $note .= "Referenced by the database but missing from storage:\n  "
            . implode("\n  ", $missing) . "\n\n";
    }
    if ($skipped) {
        $note .= "Left out to keep the archive within "
            . round(EC_ARCHIVE_MAX_BYTES / 1048576) . " MB:\n  "
            . implode("\n  ", $skipped) . "\n\nNarrow the period, or download without attachments.\n";
    }
    $zip->addFromString('NOTES.txt', $note);
}

$zip->close();

if (!is_file($tmp) || filesize($tmp) === 0) {
    @unlink($tmp);
    ec_archive_deny(500, 'The archive came out empty.');
}

header('Content-Type: application/zip');
header('Content-Length: ' . filesize($tmp));
header('Content-Disposition: attachment; filename="eventconnect-reports_' . $base . '.zip"');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: private, no-store');

readfile($tmp);
@unlink($tmp);
