<?php
/**
 * Migration: post-event report attachments and e-mail reminders.
 *
 * WHY
 *   Two features need storage the schema does not have yet:
 *
 *   1. The post-event report may carry photographs, videos and documents. A
 *      video cannot live inside a PDF, so the files are stored on disk and the
 *      report prints an annexure that links to them. proposal_media is that
 *      index.
 *   2. Reminders go out 2 days before an event, on the day itself, and a week
 *      after it ends if no report has been filed. reminder_log records every
 *      send so a second run - cron and the page-load catch-up both fire - can
 *      never mail the same person twice. app_state throttles that catch-up.
 *
 * SAFETY
 *   - Purely ADDITIVE: two new tables, one new table for state, one nullable
 *     column. Nothing existing is renamed, dropped or narrowed.
 *   - Idempotent: every step checks information_schema first, so re-running is
 *     a no-op and a partial failure can simply be re-run.
 *   - CLI only.
 *
 * USAGE
 *   php migrations/2026_09_13_report_media_and_reminders.php --dry-run
 *   php migrations/2026_09_13_report_media_and_reminders.php
 *
 * ROLLBACK
 *   DROP TABLE proposal_media, reminder_log, app_state;
 *   ALTER TABLE proposals DROP COLUMN report_generated_at;
 *   Dropping proposal_media does not delete the files under uploads/media.
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

/**
 * Attachments for a proposal's post-event report.
 *
 * stored_path is relative to the application root and always lives under
 * uploads/media/. original_name is what the convener uploaded and is only ever
 * printed, never used to build a path.
 */
$tables = [
    'proposal_media' => "
        CREATE TABLE `proposal_media` (
          `id`            INT(11) NOT NULL AUTO_INCREMENT,
          `proposal_id`   INT(11) NOT NULL,
          `kind`          ENUM('photo','video','document') NOT NULL,
          `original_name` VARCHAR(255) NOT NULL,
          `stored_path`   VARCHAR(500) NOT NULL,
          `mime_type`     VARCHAR(150) NULL,
          `size_bytes`    BIGINT NOT NULL DEFAULT 0,
          `uploaded_by`   INT(11) NOT NULL,
          `created_at`    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`),
          KEY `idx_media_proposal` (`proposal_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    // One row per (proposal, reminder kind, recipient). The unique key is what
    // makes cron and the page-load catch-up safe to run in any order.
    'reminder_log' => "
        CREATE TABLE `reminder_log` (
          `id`          INT(11) NOT NULL AUTO_INCREMENT,
          `proposal_id` INT(11) NOT NULL,
          `kind`        VARCHAR(32) NOT NULL,
          `recipient`   VARCHAR(255) NOT NULL,
          `status`      ENUM('sent','failed') NOT NULL DEFAULT 'sent',
          `error`       VARCHAR(500) NULL,
          `sent_at`     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`),
          UNIQUE KEY `uniq_reminder` (`proposal_id`, `kind`, `recipient`),
          KEY `idx_reminder_sent` (`sent_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    // Small key/value store. Currently holds only the timestamp of the last
    // reminder sweep, so dashboard loads do not each try to send mail.
    'app_state' => "
        CREATE TABLE `app_state` (
          `name`       VARCHAR(64) NOT NULL,
          `value`      VARCHAR(255) NULL,
          `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
          PRIMARY KEY (`name`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
];

$columns = [
    'proposals' => [
        'report_generated_at' => "ADD COLUMN `report_generated_at` DATETIME NULL AFTER `report_path`",
    ],
];

$applied = 0;
$skipped = 0;
$failed  = 0;

echo "--- Creating tables ---\n";
foreach ($tables as $table => $sql) {
    if ($tableExists($table)) {
        printf("  %-24s already present\n", $table);
        $skipped++;
        continue;
    }
    if ($dryRun) {
        printf("  %-24s WOULD CREATE\n", $table);
        $applied++;
        continue;
    }
    try {
        $conn->query($sql);
        printf("  %-24s CREATED\n", $table);
        $applied++;
    } catch (mysqli_sql_exception $e) {
        printf("  %-24s FAILED: %s\n", $table, $e->getMessage());
        $failed++;
    }
}

echo "\n--- Adding missing columns ---\n";
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
        if ($dryRun) {
            printf("  %-24s %-22s WOULD ADD\n", $table, $column);
            $applied++;
            continue;
        }
        try {
            $conn->query("ALTER TABLE `{$table}` {$clause}");
            printf("  %-24s %-22s ADDED\n", $table, $column);
            $applied++;
        } catch (mysqli_sql_exception $e) {
            printf("  %-24s %-22s FAILED: %s\n", $table, $column, $e->getMessage());
            $failed++;
        }
    }
}

// Back-fill report_generated_at for reports that already exist, so the "a week
// after the event" reminder does not chase conveners who have already filed.
if (!$dryRun && $failed === 0 && $columnExists('proposals', 'report_generated_at')) {
    echo "\n--- Back-filling report_generated_at for existing reports ---\n";
    try {
        $conn->query(
            "UPDATE proposals SET report_generated_at = COALESCE(updated_at, created_at)
              WHERE report_path IS NOT NULL AND report_path <> '' AND report_generated_at IS NULL"
        );
        printf("  %d row(s) back-filled\n", $conn->affected_rows);
    } catch (mysqli_sql_exception $e) {
        printf("  FAILED: %s\n", $e->getMessage());
        $failed++;
    }
}

echo "\n";
printf("Summary: %d applied, %d already present, %d failed\n", $applied, $skipped, $failed);
if ($dryRun) {
    echo "Dry run only - nothing was changed.\n";
}
exit($failed > 0 ? 1 : 0);
