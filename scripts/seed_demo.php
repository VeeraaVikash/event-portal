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

$stmt = $conn->prepare('SELECT id FROM proposals WHERE user_id = ? AND title LIKE ?');
$like = DEMO_PREFIX . '%';
$stmt->bind_param('is', $conveperId, $like);
$stmt->execute();
$old = [];
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $old[] = (int) $row['id'];
}
$stmt->close();

foreach ($old as $id) {
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

foreach ($demos as $demo) {
    $start = date('Y-m-d', strtotime($demo['start']));
    $end   = date('Y-m-d', strtotime($demo['end']));

    $conn->begin_transaction();
    try {
        $stmt = $conn->prepare(
            'INSERT INTO proposals
               (user_id, title, description, category, start_date, end_date,
                total_expected_participants, participant_categories, student_categories,
                status, hod_status)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $status = 'Approved';
        $stmt->bind_param('isssssissss',
            $conveperId, $demo['title'], $demo['description'], $demo['category'],
            $start, $end, $demo['pax'], $demo['audience'], $demo['student_cat'],
            $status, $status);
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
        $budget = [
            ['Refreshments', 'Refreshments', 'Recurring', 3, 8000.00],
            ['Certificates and printing', 'Printing', 'Recurring', 1, 8500.00],
            ['Chief guest honorarium', 'Honorarium', 'Recurring', 2, 10000.00],
            ['Stage backdrop and banners', 'Publicity', 'Non-Recurring', 1, 6500.00],
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
        $created[] = ['id' => $proposalId, 'title' => $demo['title'], 'note' => $demo['note'],
                      'start' => $start, 'end' => $end];
        printf("Created PRO-%04d  %s\n", $proposalId, $demo['note']);
    } catch (Throwable $e) {
        $conn->rollback();
        echo 'FAILED to create "' . $demo['title'] . '": ' . $e->getMessage() . "\n";
    }
}

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
