<?php
/**
 * Sends the event reminders that are currently due. CLI only.
 *
 * Run once a day. Everything it sends is recorded in reminder_log, and every
 * send claims its row first, so running it twice - or running it while the
 * dashboard catch-up is also active - cannot mail anyone twice.
 *
 *   php scripts/send_reminders.php --dry-run     show what would go out
 *   php scripts/send_reminders.php               send
 *   php scripts/send_reminders.php --retry-failed  re-attempt earlier failures
 *   php scripts/send_reminders.php --limit=100   raise the per-run cap
 *
 * macOS / Linux (crontab -e), every day at 07:00:
 *   0 7 * * * /Applications/XAMPP/xamppfiles/bin/php /Applications/XAMPP/xamppfiles/htdocs/eventconnect/scripts/send_reminders.php >> /var/log/eventconnect-reminders.log 2>&1
 *
 * Windows / IIS, Task Scheduler daily at 07:00:
 *   Program:   C:\PHP\php.exe
 *   Arguments: C:\inetpub\wwwroot\eventconnect\scripts\send_reminders.php
 *
 * Exit code is 0 when nothing failed, 1 otherwise, so a scheduler can alert.
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("This script is CLI-only.\n");
}

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/reminders.php';
/** @var mysqli $conn */

$dryRun      = in_array('--dry-run', $argv, true);
$retryFailed = in_array('--retry-failed', $argv, true);

$limit = EC_REMINDER_BATCH;
foreach ($argv as $arg) {
    if (str_starts_with($arg, '--limit=')) {
        $limit = max(1, (int) substr($arg, 8));
    }
}

echo "SRM Event Connect - reminder sweep " . date('Y-m-d H:i:s') . "\n";
echo $dryRun ? "Mode: DRY RUN (nothing will be sent)\n" : "Mode: SEND\n";

if (!ec_mail_configured()) {
    echo "SMTP is not configured. Add an 'smtp' block to includes/config.local.php\n";
    echo "(see includes/config.local.example.php) or set SMTP_HOST / SMTP_FROM.\n";
    if (!$dryRun) {
        exit(1);
    }
}

$result = ec_reminders_run($conn, $limit, $dryRun, $retryFailed);

foreach ($result['lines'] as $line) {
    echo '  ' . $line . "\n";
}

printf("Summary: %d sent, %d failed, %d already handled\n",
    $result['sent'], $result['failed'], $result['skipped']);

exit($result['failed'] > 0 ? 1 : 0);
