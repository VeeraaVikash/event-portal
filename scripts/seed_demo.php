<?php
/**
 * Loads demo data for trying the event-report workflow end to end. CLI only.
 *
 * Creates three proposals for one convener, positioned in time so that each
 * report control is visible somewhere:
 *
 *   ended 2 days ago    Create Event Report - attach photographs, videos and
 *                       documents, preview, then generate the final PDF
 *   starts in 2 days    Print Draft - the pre-event copy for the HOD, and the
 *                       proposal the "2 days before" reminder picks up
 *   ended 9 days ago    inside the "report overdue" reminder window
 *
 * It then lays down a year of finished events across three conveners, most with
 * a report filed and a recorded attendance, so the department analysis panel
 * and the period archive have something real to show.
 *
 * It also sets a known password on the two demo accounts, which is why it
 * demands --yes and refuses to run over the web. Never run this on a server
 * anyone else can reach - see "Known gaps" in README.md.
 *
 *   php scripts/seed_demo.php --yes
 *   php scripts/seed_demo.php --yes --password='something else'
 *   php scripts/seed_demo.php --remove      delete the demo proposals again
 *
 * Re-running replaces the demo proposals, including any files attached to
 * them. Nothing outside the "[DEMO]" proposals is touched apart from the two
 * passwords.
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("This script is CLI-only.\n");
}

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/media.php';
/** @var mysqli $conn */

const DEMO_PREFIX      = '[DEMO]';
const DEMO_CONVENER    = 'vs7645@srmist.edu.in';
const DEMO_HOD         = 'hod@srmist.edu.in';

$remove   = in_array('--remove', $argv, true);
$password = 'test';
foreach ($argv as $arg) {
    if (str_starts_with($arg, '--password=')) {
        $password = substr($arg, 11);
    }
}

if (!$remove && !in_array('--yes', $argv, true)) {
    echo "This writes demo proposals and resets the password on two accounts.\n";
    echo "Re-run with --yes if that is what you want.\n";
    exit(1);
}

/** Looks up a user by e-mail. */
function demo_user(mysqli $conn, string $email): ?array
{
    $stmt = $conn->prepare('SELECT id, full_name, department FROM users WHERE email = ?');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $row ?: null;
}

$convener = demo_user($conn, DEMO_CONVENER);
if (!$convener) {
    exit('No account found for ' . DEMO_CONVENER . " - nothing to attach demo proposals to.\n");
}
$conveperId = (int) $convener['id'];

/* ------------------------------------------- clear any previous demo run */

// Every demo proposal, whoever it belongs to - the history set is spread over
// several conveners in the department.
$stmt = $conn->prepare('SELECT id, report_path FROM proposals WHERE title LIKE ?');
$like = DEMO_PREFIX . '%';
$stmt->bind_param('s', $like);
$stmt->execute();
$old = [];
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $old[(int) $row['id']] = (string) ($row['report_path'] ?? '');
}
$stmt->close();

foreach ($old as $id => $reportPath) {
    // Demo report PDFs are seeded files, so they go with the proposal.
    if ($reportPath !== '') {
        $absReport = realpath(__DIR__ . '/../' . ltrim($reportPath, '/'));
        $reportsRoot = realpath(__DIR__ . '/../reports');
        if ($absReport && $reportsRoot
            && strncmp($absReport, $reportsRoot . DIRECTORY_SEPARATOR, strlen($reportsRoot) + 1) === 0
            && is_file($absReport)) {
            @unlink($absReport);
        }
    }

    // Attachments first: the files on disk have no database cascade.
    foreach (ec_media_list($conn, $id) as $m) {
        $stmtM = $conn->prepare('SELECT stored_path FROM proposal_media WHERE id = ?');
        $stmtM->bind_param('i', $m['id']);
        $stmtM->execute();
        $path = $stmtM->get_result()->fetch_assoc()['stored_path'] ?? '';
        $stmtM->close();
        $abs = realpath(__DIR__ . '/../' . ltrim($path, '/'));
        if ($abs && is_file($abs)) {
            @unlink($abs);
        }
    }
    $conn->query('DELETE FROM proposal_media WHERE proposal_id = ' . $id);
    $dir = __DIR__ . '/../' . EC_MEDIA_DIR . '/' . $id;
    if (is_dir($dir)) {
        @rmdir($dir);
    }
    $conn->query('DELETE FROM reminder_log WHERE proposal_id = ' . $id);

    // The child tables cascade from proposals.
    $conn->query('DELETE FROM proposals WHERE id = ' . $id);
    echo "Removed previous demo proposal PRO-" . sprintf('%04d', $id) . "\n";
}

if ($remove) {
    echo "Demo proposals removed. Passwords were left alone.\n";
    exit(0);
}

/* ------------------------------------------------------- the proposals */

$demos = [
    [
        'title'       => DEMO_PREFIX . ' National Workshop on Applied AI in Engineering',
        'description' => 'A three-day hands-on workshop covering applied machine learning for engineering '
            . 'problems, delivered with industry speakers and a closing project showcase. '
            . 'This proposal is seeded with its dates in the past, so the post-event report '
            . 'workspace is open: attach photographs, videos and documents, preview the PDF, '
            . 'then generate the final report.',
        'category'    => 'workshop',
        'start'       => '-5 days',
        'end'         => '-2 days',
        'pax'         => 80,
        'audience'    => 'Students,Faculty',
        'student_cat' => 'B.Tech CSE, M.Tech CSE',
        'note'        => 'ended 2 days ago -> "Create Event Report"',
    ],
    [
        'title'       => DEMO_PREFIX . ' Industry Conclave 2026',
        'description' => 'A one-day industry conclave with panel discussions on hiring trends and a '
            . 'recruiter interaction. Seeded to start in two days, so the pre-event draft can '
            . 'be printed for the HOD - and so the "2 days before" reminder has something to '
            . 'pick up on the next sweep.',
        'category'    => 'industrial_conclave',
        'start'       => '+2 days',
        'end'         => '+2 days',
        'pax'         => 150,
        'audience'    => 'Students,Industry',
        'student_cat' => 'Final year B.Tech',
        'note'        => 'starts in 2 days -> "Print Draft" + reminder',
    ],
    [
        'title'       => DEMO_PREFIX . ' Faculty Development Programme on IoT',
        'description' => 'A five-day faculty development programme on IoT system design. Seeded to have '
            . 'ended nine days ago with no report filed, which is the state the "report '
            . 'overdue" reminder looks for.',
        'category'    => 'fdp',
        'start'       => '-13 days',
        'end'         => '-9 days',
        'pax'         => 40,
        'audience'    => 'Faculty',
        'student_cat' => null,
        'note'        => 'ended 9 days ago, no report -> overdue reminder',
    ],
];

$created = [];

/**
 * Writes one proposal and its child rows.
 *
 * $demo takes the same shape in both passes; the history pass adds 'attended',
 * 'report' and 'budget_scale'.
 */
function demo_create(mysqli $conn, int $conveperId, array $demo): ?int
{
    $start = date('Y-m-d', strtotime($demo['start']));
    $end   = date('Y-m-d', strtotime($demo['end']));

    $conn->begin_transaction();
    try {
        $stmt = $conn->prepare(
            'INSERT INTO proposals
               (user_id, title, description, category, start_date, end_date,
                total_expected_participants, actual_participants,
                participant_categories, student_categories, status, hod_status)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $status   = $demo['status'] ?? 'Approved';
        $hodStatus = ($status === 'Cancelled') ? 'Approved' : $status;
        $attended = $demo['attended'] ?? null;
        $stmt->bind_param('isssssiissss',
            $conveperId, $demo['title'], $demo['description'], $demo['category'],
            $start, $end, $demo['pax'], $attended, $demo['audience'], $demo['student_cat'],
            $status, $hodStatus);
        $stmt->execute();
        $proposalId = (int) $conn->insert_id;
        $stmt->close();

        // Chief guests - these print in the report's guest table.
        $guests = [
            ['Dr. A. Ramesh', 'Principal Scientist', 'Bharat Research Labs',
             '4th Cross, Electronic City, Bengaluru 560100', '9840012345', 'ABCDE1234F',
             'Leads the applied machine learning group and will deliver the keynote.'],
            ['Ms. K. Priya', 'Engineering Manager', 'Northwind Technologies',
             'Tidel Park, Taramani, Chennai 600113', '9884567890', 'FGHIJ5678K',
             'Industry perspective on deploying models in production.'],
        ];
        $stmt = $conn->prepare(
            'INSERT INTO proposal_guests
               (proposal_id, name, designation, organization, address, contact_number, pan_number, reason_for_inviting)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
        );
        foreach ($guests as $g) {
            $stmt->bind_param('isssssss', $proposalId, $g[0], $g[1], $g[2], $g[3], $g[4], $g[5], $g[6]);
            $stmt->execute();
        }
        $stmt->close();

        // Travel and accommodation.
        $stmt = $conn->prepare(
            'INSERT INTO proposal_travel_accomm
               (proposal_id, guest_name, hotel_name_address, accommodation_days, who_arranges,
                mode, number_of_trips, who_provides, travel_address)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $rows = [
            ['Dr. A. Ramesh', 'SRM Guest House, Kattankulathur', 2, 'University', null, null, null, null],
            ['Ms. K. Priya', null, null, null, 'Flight (Economy)', 2, 'University', 'Chennai - Bengaluru - Chennai'],
        ];
        foreach ($rows as $r) {
            $stmt->bind_param('issisisis', $proposalId, $r[0], $r[1], $r[2], $r[3], $r[4], $r[5], $r[6], $r[7]);
            $stmt->execute();
        }
        $stmt->close();

        // Budget lines.
        $stmt = $conn->prepare(
            'INSERT INTO proposal_budgets (proposal_id, item, category, type, quantity, cost_per_unit, total, amount)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $scale = $demo['budget_scale'] ?? 1.0;
        $budget = [
            ['Refreshments', 'Refreshments', 'Recurring', 3, round(8000.00 * $scale, 2)],
            ['Certificates and printing', 'Printing', 'Recurring', 1, round(8500.00 * $scale, 2)],
            ['Chief guest honorarium', 'Honorarium', 'Recurring', 2, round(10000.00 * $scale, 2)],
            ['Stage backdrop and banners', 'Publicity', 'Non-Recurring', 1, round(6500.00 * $scale, 2)],
        ];
        foreach ($budget as $b) {
            $total = $b[3] * $b[4];
            $stmt->bind_param('isssiddd', $proposalId, $b[0], $b[1], $b[2], $b[3], $b[4], $total, $total);
            $stmt->execute();
        }
        $stmt->close();

        // One sponsor.
        $stmt = $conn->prepare(
            'INSERT INTO proposal_sponsors
               (proposal_id, sponsor_name, sponsor_category, amount_contributed, amount, reward_perk, mode, about, benefits)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $sponsorName = 'Northwind Technologies';
        $sponsorCat  = 'Industry Partner';
        $amount      = 25000.00;
        $reward      = 'Logo on banners and certificates';
        $mode        = 'Bank transfer';
        $about       = 'Chennai-based product engineering firm, hiring partner of the department.';
        $benefits    = 'Branding at the venue and a recruiter slot in the closing session.';
        $stmt->bind_param('issddssss', $proposalId, $sponsorName, $sponsorCat, $amount, $amount,
            $reward, $mode, $about, $benefits);
        $stmt->execute();
        $stmt->close();

        // Funding split.
        $stmt = $conn->prepare(
            'INSERT INTO proposal_financials (proposal_id, university_fund, registration_fund, sponsorship_fund, other_sources)
             VALUES (?, ?, ?, ?, ?)'
        );
        $uni = 30000.00; $reg = 16000.00; $spo = 25000.00; $oth = 0.00;
        $stmt->bind_param('idddd', $proposalId, $uni, $reg, $spo, $oth);
        $stmt->execute();
        $stmt->close();

        $conn->commit();
        return $proposalId;
    } catch (Throwable $e) {
        $conn->rollback();
        echo 'FAILED to create "' . $demo['title'] . '": ' . $e->getMessage() . "\n";
        return null;
    }
}

foreach ($demos as $demo) {
    $id = demo_create($conn, $conveperId, $demo);
    if ($id !== null) {
        $created[] = $id;
        printf("Created PRO-%04d  %s\n", $id, $demo['note']);
    }
}

/* ------------------------------------------------- a year of finished events */

/** Writes a small but valid one-page PDF standing in for a filed report. */
function demo_report_pdf(string $path, string $ref, string $title, string $when, int $attended): void
{
    $lines = [
        'SRM Institute of Science and Technology',
        'Department event report (seeded demo file)',
        '',
        'Reference : ' . $ref,
        'Event     : ' . $title,
        'Held      : ' . $when,
        'Attended  : ' . $attended . ' participants',
        '',
        'This placeholder stands in for a report generated from the event',
        'report workspace. Generate a real one to see the full layout.',
    ];

    $content = '';
    $y = 780;
    foreach ($lines as $i => $line) {
        $size = $i === 0 ? 16 : 11;
        $content .= "BT /F1 {$size} Tf 60 {$y} Td (" . str_replace(['(', ')'], ['\\(', '\\)'], $line) . ") Tj ET\n";
        $y -= ($i === 0 ? 30 : 20);
    }

    $objects = [
        '<< /Type /Catalog /Pages 2 0 R >>',
        '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
        '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 5 0 R >> >> /Contents 4 0 R >>',
        '<< /Length ' . strlen($content) . " >>\nstream\n" . $content . 'endstream',
        '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>',
    ];

    $pdf = "%PDF-1.4\n";
    $offsets = [];
    foreach ($objects as $i => $body) {
        $offsets[] = strlen($pdf);
        $pdf .= ($i + 1) . " 0 obj\n" . $body . "\nendobj\n";
    }
    $xref = strlen($pdf);
    $pdf .= 'xref' . "\n0 " . (count($objects) + 1) . "\n0000000000 65535 f \n";
    foreach ($offsets as $off) {
        $pdf .= sprintf("%010d 00000 n \n", $off);
    }
    $pdf .= "trailer\n<< /Size " . (count($objects) + 1) . " /Root 1 0 R >>\nstartxref\n{$xref}\n%%EOF\n";

    file_put_contents($path, $pdf);
}

// Other conveners in the same department, so "by faculty" has more than one row.
$conveners = [$conveperId];
$stmt = $conn->prepare(
    "SELECT id FROM users WHERE department = ? AND LOWER(role) IN ('faculty','convener') AND id <> ? ORDER BY id"
);
$stmt->bind_param('si', $convener['department'], $conveperId);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $conveners[] = (int) $row['id'];
}
$stmt->close();

/**
 * [title, category, days ago the event ended, length in days, expected,
 *  attended (null = never recorded), report filed?, budget scale, status]
 */
$history = [
    ['Workshop on Cloud Native Development', 'workshop',              20,  2,  70,  64, true,  1.0,  'Approved'],
    ['Guest Lecture: Semiconductor Careers', 'lecture_series_industry_expert', 34, 1, 120, 103, true, 0.5, 'Approved'],
    ['Alumni Interaction Meet',              'alumni_programme',      48,  1, 200, 165, true,  0.8,  'Approved'],
    ['Hackathon: Build for Bharat',          'student_programme',     62,  2, 250, 228, true,  1.6,  'Approved'],
    ['FDP on Outcome Based Education',       'fdp',                   79,  5,  45,  41, true,  1.2,  'Approved'],
    ['Value Added Course: Data Engineering', 'value_added_course',    96,  6,  60,  52, true,  0.9,  'Approved'],
    ['Industry Conclave (Spring)',           'industrial_conclave',  118,  1, 180, null, true, 1.4,  'Approved'],
    ['National Conference on Computing',     'conference_national',  140,  3, 300, 274, true,  2.4,  'Approved'],
    ['Counselling Session for First Years',  'counselling_activity', 165,  1, 150, 141, false, 0.3,  'Approved'],
    ['Outreach Programme at Govt School',    'outreach_programme',   190,  1,  90,  86, true,  0.6,  'Approved'],
    ['Winter School on Robotics',            'winter_summer_school', 232,  5,  50,  44, true,  1.5,  'Approved'],
    ['Upskilling for Non-Teaching Staff',    'upskilling_non_teaching', 268, 2, 35,  33, true,  0.4,  'Approved'],
    ['MDP on Project Management',            'mdp_pdp',              300,  3,  40,  36, false, 1.1,  'Approved'],
    ['Association Activity: Tech Quiz',      'association_activity', 330,  1, 110,  97, true,  0.2,  'Approved'],
    ['Symposium that was called off',        'student_programme',     55,  1, 100, null, false, 0.7, 'Cancelled'],
];

$reportsDir = __DIR__ . '/../reports';
if (!is_dir($reportsDir)) {
    @mkdir($reportsDir, 0755, true);
}

$historyCount = 0;
$filedCount = 0;

foreach ($history as $i => [$title, $category, $endAgo, $length, $pax, $attended, $filed, $scale, $status]) {
    $owner = $conveners[$i % count($conveners)];
    $end   = '-' . $endAgo . ' days';
    $start = '-' . ($endAgo + $length) . ' days';

    $id = demo_create($conn, $owner, [
        'title'        => DEMO_PREFIX . ' ' . $title,
        'description'  => 'Seeded demo event, used to populate the department analysis and the period archive.',
        'category'     => $category,
        'start'        => $start,
        'end'          => $end,
        'pax'          => $pax,
        'attended'     => $attended,
        'audience'     => 'Students,Faculty',
        'student_cat'  => null,
        'status'       => $status,
        'budget_scale' => $scale,
    ]);

    if ($id === null) {
        continue;
    }
    $historyCount++;

    if (!$filed) {
        continue;
    }

    $ref      = 'PRO-' . sprintf('%04d', $id);
    $endDate  = date('Y-m-d', strtotime($end));
    $relative = 'reports/Report_' . str_replace('-', '_', $ref) . '_' . strtotime($end) . '.pdf';
    demo_report_pdf(__DIR__ . '/../' . $relative, $ref, $title, $endDate, (int) ($attended ?? $pax));

    $stmt = $conn->prepare(
        'UPDATE proposals SET report_path = ?, report_generated_at = ? WHERE id = ?'
    );
    $generated = date('Y-m-d H:i:s', strtotime($end . ' +2 days'));
    $stmt->bind_param('ssi', $relative, $generated, $id);
    $stmt->execute();
    $stmt->close();
    $filedCount++;
}

printf("Created %d finished events across %d convener(s); %d have a report on file\n",
    $historyCount, count($conveners), $filedCount);

/* ---------------------------------------------------------- passwords */

$hash = password_hash($password, PASSWORD_DEFAULT);
foreach ([DEMO_CONVENER, DEMO_HOD] as $email) {
    $stmt = $conn->prepare('UPDATE users SET password = ? WHERE email = ?');
    $stmt->bind_param('ss', $hash, $email);
    $stmt->execute();
    $changed = $stmt->affected_rows;
    $stmt->close();
    echo $changed > 0
        ? "Password set for {$email}\n"
        : "No account for {$email} - password unchanged\n";
}

/* ------------------------------------------------------------ summary */

echo "\nSign in at " . ec_base_url() . "/index.php?modal=login\n\n";
printf("  %-28s %-12s %s\n", 'Account', 'Password', 'Sees');
printf("  %-28s %-12s %s\n", DEMO_CONVENER, $password, 'convener dashboard - report buttons');
printf("  %-28s %-12s %s\n", DEMO_HOD, $password, 'HOD dashboard - can open the filed report');

echo "\nSample files to attach: demo_assets/\n";
echo "  inauguration.jpg, keynote_session.jpg, valedictory.jpg   photographs\n";
echo "  keynote_clip.mp4                                         video (annexure + QR)\n";
echo "  attendance_sheet.pdf, expense_bills.pdf                  documents (annexure + QR)\n";
