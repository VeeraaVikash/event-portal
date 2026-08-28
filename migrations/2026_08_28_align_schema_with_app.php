<?php
/**
 * Migration: align the database schema with the application code.
 *
 * WHY
 *   The deployed schema predates the current application code. Several columns
 *   the code writes do not exist, and proposals.status is missing three of the
 *   status values the workflow uses. Under STRICT_TRANS_TABLES those writes
 *   fail outright, which is why "request changes", cancel, reschedule, report
 *   upload and signup do not work.
 *
 * SAFETY
 *   - Purely ADDITIVE. Nothing is renamed, dropped, narrowed or back-filled.
 *   - Existing enum members (including the unused 'Revision') are preserved.
 *   - Every legacy column is left untouched; all of them are nullable, so rows
 *     written by the new code simply leave them NULL.
 *   - Idempotent: each step checks information_schema first, so re-running is a
 *     no-op. Safe to run again after a partial failure.
 *   - CLI only.
 *
 * USAGE
 *   php migrations/2026_08_28_align_schema_with_app.php --dry-run
 *   php migrations/2026_08_28_align_schema_with_app.php
 *
 * ROLLBACK
 *   See README.md ("Rollback"). Restoring the pre-migration mysqldump is the
 *   supported path. Narrowing the status enum back only works if no row has
 *   yet been given one of the new statuses.
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

/** Columns the application code writes but the legacy schema may lack. */
$columns = [
    'proposals' => [
        'report_path' => "ADD COLUMN `report_path` VARCHAR(500) NULL AFTER `student_categories`",
    ],
    'users' => [
        'phone_number' => "ADD COLUMN `phone_number` VARCHAR(50) NULL AFTER `password`",
    ],
    'proposal_guests' => [
        'address'             => "ADD COLUMN `address` TEXT NULL",
        'contact_number'      => "ADD COLUMN `contact_number` VARCHAR(50) NULL",
        'pan_number'          => "ADD COLUMN `pan_number` VARCHAR(50) NULL",
        'reason_for_inviting' => "ADD COLUMN `reason_for_inviting` TEXT NULL",
    ],
    'proposal_travel_accomm' => [
        'hotel_name_address' => "ADD COLUMN `hotel_name_address` TEXT NULL",
        'accommodation_days' => "ADD COLUMN `accommodation_days` INT NULL",
        'who_arranges'       => "ADD COLUMN `who_arranges` VARCHAR(50) NULL",
        'mode'               => "ADD COLUMN `mode` VARCHAR(255) NULL",
        'number_of_trips'    => "ADD COLUMN `number_of_trips` INT NULL",
        'who_provides'       => "ADD COLUMN `who_provides` VARCHAR(50) NULL",
        'travel_address'     => "ADD COLUMN `travel_address` TEXT NULL",
    ],
    'proposal_budgets' => [
        'category'      => "ADD COLUMN `category` VARCHAR(255) NULL",
        'type'          => "ADD COLUMN `type` VARCHAR(100) NULL",
        'quantity'      => "ADD COLUMN `quantity` INT NULL",
        'cost_per_unit' => "ADD COLUMN `cost_per_unit` DECIMAL(10,2) NULL",
        'total'         => "ADD COLUMN `total` DECIMAL(10,2) NULL",
    ],
    'proposal_sponsors' => [
        'sponsor_category'   => "ADD COLUMN `sponsor_category` VARCHAR(255) NULL",
        'amount_contributed' => "ADD COLUMN `amount_contributed` DECIMAL(10,2) NULL",
        'reward_perk'        => "ADD COLUMN `reward_perk` TEXT NULL",
        'mode'               => "ADD COLUMN `mode` VARCHAR(100) NULL",
        'about'              => "ADD COLUMN `about` TEXT NULL",
        'benefits'           => "ADD COLUMN `benefits` TEXT NULL",
    ],
];

/** Enum widenings: existing members are always preserved. */
$enums = [
    ['table' => 'proposals', 'column' => 'status',
     'need'  => ['Review', 'Cancelled', 'Rescheduled'],
     'definition' => "ENUM('Pending','Approved','Rejected','Revision','Review','Cancelled','Rescheduled') COLLATE utf8mb4_unicode_ci DEFAULT 'Pending'"],
    ['table' => 'users', 'column' => 'role',
     'need'  => ['Convener'],
     'definition' => "ENUM('faculty','coordinator','hod','admin','Convener') COLLATE utf8mb4_unicode_ci DEFAULT 'faculty'"],
];

$applied = 0;
$skipped = 0;
$failed  = 0;

$columnExists = function (string $table, string $column) use ($conn, $dbName): bool {
    $s = $conn->prepare(
        "SELECT 1 FROM information_schema.COLUMNS
          WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ?"
    );
    $s->bind_param('sss', $dbName, $table, $column);
    $s->execute();
    $found = $s->get_result()->num_rows > 0;
    $s->close();
    return $found;
};

$columnType = function (string $table, string $column) use ($conn, $dbName): ?string {
    $s = $conn->prepare(
        "SELECT COLUMN_TYPE FROM information_schema.COLUMNS
          WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ?"
    );
    $s->bind_param('sss', $dbName, $table, $column);
    $s->execute();
    $row = $s->get_result()->fetch_assoc();
    $s->close();
    return $row['COLUMN_TYPE'] ?? null;
};

$tableExists = function (string $table) use ($conn, $dbName): bool {
    $s = $conn->prepare(
        "SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?"
    );
    $s->bind_param('ss', $dbName, $table);
    $s->execute();
    $found = $s->get_result()->num_rows > 0;
    $s->close();
    return $found;
};

echo "--- Adding missing columns ---\n";
foreach ($columns as $table => $defs) {
    if (!$tableExists($table)) {
        printf("  %-24s %-22s MISSING TABLE - skipped\n", $table, '');
        $skipped += count($defs);
        continue;
    }
    foreach ($defs as $column => $clause) {
        if ($columnExists($table, $column)) {
            printf("  %-24s %-22s already present\n", $table, $column);
            $skipped++;
            continue;
        }
        $sql = "ALTER TABLE `{$table}` {$clause}";
        if ($dryRun) {
            printf("  %-24s %-22s WOULD ADD\n", $table, $column);
            $applied++;
            continue;
        }
        try {
            $conn->query($sql);
            printf("  %-24s %-22s ADDED\n", $table, $column);
            $applied++;
        } catch (mysqli_sql_exception $e) {
            printf("  %-24s %-22s FAILED: %s\n", $table, $column, $e->getMessage());
            $failed++;
        }
    }
}

echo "\n--- Widening enums (existing values preserved) ---\n";
foreach ($enums as $e) {
    $current = $columnType($e['table'], $e['column']);
    if ($current === null) {
        printf("  %-24s %-22s MISSING - skipped\n", $e['table'], $e['column']);
        $skipped++;
        continue;
    }
    $missing = array_values(array_filter(
        $e['need'],
        fn($v) => stripos($current, "'" . $v . "'") === false
    ));
    if (!$missing) {
        printf("  %-24s %-22s already has %s\n", $e['table'], $e['column'], implode(',', $e['need']));
        $skipped++;
        continue;
    }
    $sql = "ALTER TABLE `{$e['table']}` MODIFY `{$e['column']}` {$e['definition']}";
    if ($dryRun) {
        printf("  %-24s %-22s WOULD ADD %s\n", $e['table'], $e['column'], implode(',', $missing));
        $applied++;
        continue;
    }
    try {
        $conn->query($sql);
        printf("  %-24s %-22s ADDED %s\n", $e['table'], $e['column'], implode(',', $missing));
        $applied++;
    } catch (mysqli_sql_exception $ex) {
        printf("  %-24s %-22s FAILED: %s\n", $e['table'], $e['column'], $ex->getMessage());
        $failed++;
    }
}

echo "\n";
printf("Summary: %d applied, %d already present, %d failed\n", $applied, $skipped, $failed);
if ($dryRun) {
    echo "Dry run only - nothing was changed.\n";
}
exit($failed > 0 ? 1 : 0);
