<?php
/**
 * Migration: attendance breakdown and the event summary.
 *
 * WHY
 *   A single headcount does not answer the question the department is actually
 *   asked - who came. Attendance is now recorded as four figures: internal and
 *   external students, internal and external faculty. actual_participants stays
 *   as their total, so everything already reading it keeps working.
 *
 *   report_summary holds the convener's account of what happened, written when
 *   the report is generated and printed at the top of it.
 *
 * SAFETY
 *   Five nullable columns. Additive, idempotent, CLI only. Reports filed before
 *   this have no breakdown and no summary; both render as "not recorded".
 *
 * USAGE
 *   php migrations/2026_09_13_attendance_breakdown.php --dry-run
 *   php migrations/2026_09_13_attendance_breakdown.php
 *
 * ROLLBACK
 *   ALTER TABLE proposals
 *     DROP COLUMN att_internal_students, DROP COLUMN att_external_students,
 *     DROP COLUMN att_internal_faculty,  DROP COLUMN att_external_faculty,
 *     DROP COLUMN report_summary;
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

$columns = [
    'att_internal_students' => "ADD COLUMN `att_internal_students` INT NULL AFTER `actual_participants`",
    'att_external_students' => "ADD COLUMN `att_external_students` INT NULL AFTER `att_internal_students`",
    'att_internal_faculty'  => "ADD COLUMN `att_internal_faculty` INT NULL AFTER `att_external_students`",
    'att_external_faculty'  => "ADD COLUMN `att_external_faculty` INT NULL AFTER `att_internal_faculty`",
    'report_summary'        => "ADD COLUMN `report_summary` TEXT NULL AFTER `att_external_faculty`",
];

$columnExists = function (string $column) use ($conn, $dbName): bool {
    $s = $conn->prepare(
        "SELECT 1 FROM information_schema.COLUMNS
          WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'proposals' AND COLUMN_NAME = ?"
    );
    $s->bind_param('ss', $dbName, $column);
    $s->execute();
    $found = $s->get_result()->num_rows > 0;
    $s->close();
    return $found;
};

$applied = 0;
$skipped = 0;
$failed  = 0;

foreach ($columns as $column => $clause) {
    if ($columnExists($column)) {
        printf("  proposals.%-24s already present\n", $column);
        $skipped++;
        continue;
    }
    if ($dryRun) {
        printf("  proposals.%-24s WOULD ADD\n", $column);
        $applied++;
        continue;
    }
    try {
        $conn->query("ALTER TABLE `proposals` {$clause}");
        printf("  proposals.%-24s ADDED\n", $column);
        $applied++;
    } catch (mysqli_sql_exception $e) {
        printf("  proposals.%-24s FAILED: %s\n", $column, $e->getMessage());
        $failed++;
    }
}

echo "\n";
printf("Summary: %d applied, %d already present, %d failed\n", $applied, $skipped, $failed);
if ($dryRun) {
    echo "Dry run only - nothing was changed.\n";
}
exit($failed > 0 ? 1 : 0);
