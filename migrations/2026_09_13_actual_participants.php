<?php
/**
 * Migration: record how many participants actually attended.
 *
 * WHY
 *   proposals.total_expected_participants is what the convener predicted when
 *   the proposal was written. Nothing recorded the real turnout, so the
 *   department analysis could only ever report expectations. The report
 *   workspace now asks for the attendance figure when the post-event report is
 *   generated, and this is where it lands.
 *
 *   Nullable on purpose: events reported before this existed have no figure,
 *   and the analysis falls back to the expected count for those rather than
 *   counting them as zero.
 *
 * SAFETY
 *   One nullable column. Idempotent, additive, CLI only.
 *
 * USAGE
 *   php migrations/2026_09_13_actual_participants.php --dry-run
 *   php migrations/2026_09_13_actual_participants.php
 *
 * ROLLBACK
 *   ALTER TABLE proposals DROP COLUMN actual_participants;
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("This migration is CLI-only.\n");
}

$dryRun = in_array('--dry-run', $argv, true);

require __DIR__ . '/../includes/db.php';
/** @var mysqli $conn */

$dbName = $conn->query('SELECT DATABASE() AS d')->fetch_assoc()['d'];
echo "Database: {$dbName}\n";
echo $dryRun ? "Mode: DRY RUN (no changes will be applied)\n\n" : "Mode: APPLY\n\n";

$stmt = $conn->prepare(
    "SELECT 1 FROM information_schema.COLUMNS
      WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'proposals' AND COLUMN_NAME = 'actual_participants'"
);
$stmt->bind_param('s', $dbName);
$stmt->execute();
$exists = $stmt->get_result()->num_rows > 0;
$stmt->close();

if ($exists) {
    echo "  proposals.actual_participants already present\n\nSummary: 0 applied, 1 already present, 0 failed\n";
    exit(0);
}
if ($dryRun) {
    echo "  proposals.actual_participants WOULD ADD\n\nDry run only - nothing was changed.\n";
    exit(0);
}

try {
    $conn->query(
        'ALTER TABLE `proposals`
         ADD COLUMN `actual_participants` INT NULL AFTER `total_expected_participants`'
    );
    echo "  proposals.actual_participants ADDED\n\nSummary: 1 applied, 0 already present, 0 failed\n";
    exit(0);
} catch (mysqli_sql_exception $e) {
    echo '  proposals.actual_participants FAILED: ' . $e->getMessage() . "\n";
    exit(1);
}
