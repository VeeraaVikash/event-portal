<?php
/**
 * Event e-mail reminders.
 *
 * Three kinds go out:
 *
 *   before_2days  two days before an approved event starts - convener,
 *                 coordinator(s) and HOD of the convener's department
 *   day_of        on the morning of the start date - same recipients
 *   report_due    a week after the event ends, if no report has been filed -
 *                 convener only, asking for the final report
 *
 * Two things can trigger a sweep: scripts/send_reminders.php from cron, and a
 * throttled catch-up when someone loads a dashboard. reminder_log has a unique
 * key on (proposal, kind, recipient) and every send claims its row before the
 * message leaves, so the two paths can overlap without anyone being mailed
 * twice.
 */

require_once __DIR__ . '/workflow.php';
require_once __DIR__ . '/mailer.php';
require_once __DIR__ . '/media.php';

/**
 * Timing rules.
 *
 * The two event reminders fire on an exact date - a "your event is in two
 * days" mail sent four days late is worse than no mail. The report reminder
 * uses a window instead, so a cron run that was down for a few days still
 * catches it, while events that ended long ago are left alone.
 */
const EC_REMINDER_REPORT_MIN_DAYS = 7;
const EC_REMINDER_REPORT_MAX_DAYS = 14;

/** How many messages one sweep may send. Keeps a page-load catch-up short. */
const EC_REMINDER_BATCH = 25;

/**
 * Proposals needing a reminder, with their recipients resolved.
 *
 * @return array<int, array{kind:string, proposal:array, recipients:array}>
 */
function ec_reminders_due(mysqli $conn): array
{
    $select = 'SELECT p.id, p.title, p.start_date, p.end_date, p.status, p.report_path,
                      u.id AS convener_id, u.full_name AS convener_name,
                      u.email AS convener_email, u.department AS department
                 FROM proposals p
                 JOIN users u ON p.user_id = u.id ';

    $queries = [
        // Two days out.
        'before_2days' => $select .
            "WHERE p.status = 'Approved' AND p.start_date = DATE_ADD(CURDATE(), INTERVAL 2 DAY)",

        // The day itself.
        'day_of' => $select .
            "WHERE p.status = 'Approved' AND p.start_date = CURDATE()",

        // A week after the end date, only while the report is still missing.
        'report_due' => $select .
            "WHERE p.status = 'Approved'
               AND (p.report_path IS NULL OR p.report_path = '')
               AND p.end_date BETWEEN DATE_SUB(CURDATE(), INTERVAL " . EC_REMINDER_REPORT_MAX_DAYS . " DAY)
                                  AND DATE_SUB(CURDATE(), INTERVAL " . EC_REMINDER_REPORT_MIN_DAYS . " DAY)",
    ];

    $due = [];
    foreach ($queries as $kind => $sql) {
        $res = $conn->query($sql);
        while ($row = $res->fetch_assoc()) {
            $recipients = ec_reminder_recipients($conn, $row, $kind);
            if ($recipients) {
                $due[] = ['kind' => $kind, 'proposal' => $row, 'recipients' => $recipients];
            }
        }
        $res->free();
    }
    return $due;
}

/**
 * Who hears about this reminder.
 *
 * The report reminder is the convener's job alone; the other two also go to
 * the coordinator(s) and HOD of the convener's department, who need to be
 * ready for the event. Duplicates are collapsed - one person can hold two
 * roles, and an HOD can be their own convener.
 */
function ec_reminder_recipients(mysqli $conn, array $proposal, string $kind): array
{
    $people = [];

    $convenerEmail = trim((string) $proposal['convener_email']);
    if ($convenerEmail !== '') {
        $people[strtolower($convenerEmail)] = [
            'email' => $convenerEmail,
            'name'  => $proposal['convener_name'],
            'role'  => 'Convener',
        ];
    }

    if ($kind !== 'report_due' && !empty($proposal['department'])) {
        $stmt = $conn->prepare(
            "SELECT full_name, email, role FROM users
              WHERE department = ? AND LOWER(role) IN ('coordinator','hod') AND email <> ''"
        );
        $stmt->bind_param('s', $proposal['department']);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($r = $res->fetch_assoc()) {
            $key = strtolower(trim($r['email']));
            if ($key !== '' && !isset($people[$key])) {
                $people[$key] = [
                    'email' => trim($r['email']),
                    'name'  => $r['full_name'],
                    'role'  => ucfirst(strtolower($r['role'])),
                ];
            }
        }
        $stmt->close();
    }

    return array_values($people);
}

/** Subject, HTML body and plain-text body for one reminder. */
function ec_reminder_message(string $kind, array $proposal, array $recipient): array
{
    $ref    = 'PRO-' . sprintf('%04d', (int) $proposal['id']);
    $title  = (string) $proposal['title'];
    $start  = date('d M Y', strtotime((string) $proposal['start_date']));
    $end    = date('d M Y', strtotime((string) $proposal['end_date']));
    $dates  = ($start === $end) ? $start : ($start . ' to ' . $end);
    $link   = ec_base_url() . '/dashboard.php';
    $isSelf = ($recipient['role'] === 'Convener');

    switch ($kind) {
        case 'before_2days':
            $subject = "Reminder: {$title} starts in 2 days ({$start})";
            $lead    = $isSelf
                ? "Your event starts in two days."
                : "An approved event in your department starts in two days.";
            $action  = "Please confirm the arrangements - venue, chief guest travel and participant communication.";
            break;

        case 'day_of':
            $subject = "Today: {$title} ({$ref})";
            $lead    = $isSelf
                ? "Your event starts today."
                : "An approved event in your department starts today.";
            $action  = "Remember to collect photographs, attendance and bills during the event - they are needed for the post-event report.";
            break;

        case 'report_due':
        default:
            $subject = "Action needed: submit the event report for {$title} ({$ref})";
            $lead    = "Your event finished on {$end} and its report has not been generated yet.";
            $action  = "Open the proposal on your dashboard and use Create Event Report to attach photographs, videos and documents, then generate the final PDF.";
            break;
    }

    $e = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');

    $html = '<div style="font-family:Arial,Helvetica,sans-serif;font-size:14px;color:#222;line-height:1.5;">'
        . '<p style="margin:0 0 12px;">Dear ' . $e($recipient['name']) . ',</p>'
        . '<p style="margin:0 0 12px;">' . $e($lead) . '</p>'
        . '<table cellpadding="6" cellspacing="0" border="0" style="border-collapse:collapse;margin:0 0 12px;border:1px solid #dde3ea;">'
        . '<tr><td style="background:#f5f8fb;font-weight:bold;">Event</td><td>' . $e($title) . '</td></tr>'
        . '<tr><td style="background:#f5f8fb;font-weight:bold;">Reference</td><td>' . $e($ref) . '</td></tr>'
        . '<tr><td style="background:#f5f8fb;font-weight:bold;">Dates</td><td>' . $e($dates) . '</td></tr>'
        . '</table>'
        . '<p style="margin:0 0 16px;">' . $e($action) . '</p>'
        . '<p style="margin:0 0 20px;"><a href="' . $e($link) . '" style="background:#004289;color:#ffffff;padding:10px 18px;border-radius:6px;text-decoration:none;">Open SRM Event Connect</a></p>'
        . '<p style="margin:0;color:#777;font-size:12px;">This is an automated reminder from SRM Event Connect. Please do not reply.</p>'
        . '</div>';

    $text = "Dear {$recipient['name']},\n\n{$lead}\n\n"
        . "Event:     {$title}\n"
        . "Reference: {$ref}\n"
        . "Dates:     {$dates}\n\n"
        . "{$action}\n\n{$link}\n\n"
        . "This is an automated reminder from SRM Event Connect. Please do not reply.\n";

    return [$subject, $html, $text];
}

/**
 * Claims one (proposal, kind, recipient) slot.
 *
 * The row is written before the message is sent, so two sweeps running at once
 * cannot both take the same slot: the loser's INSERT hits the unique key and
 * it moves on. Returns false when the slot is already taken.
 */
function ec_reminder_claim(mysqli $conn, int $proposalId, string $kind, string $recipient): bool
{
    try {
        $stmt = $conn->prepare(
            "INSERT INTO reminder_log (proposal_id, kind, recipient, status, error)
             VALUES (?, ?, ?, 'failed', 'sending')"
        );
        $stmt->bind_param('iss', $proposalId, $kind, $recipient);
        $stmt->execute();
        $stmt->close();
        return true;
    } catch (mysqli_sql_exception $e) {
        // Duplicate key: somebody else already has this one.
        return false;
    }
}

/** Records the outcome against the claimed row. */
function ec_reminder_settle(mysqli $conn, int $proposalId, string $kind, string $recipient, bool $sent, string $error): void
{
    $status = $sent ? 'sent' : 'failed';
    $error  = $sent ? null : mb_substr($error, 0, 500);
    $stmt = $conn->prepare(
        'UPDATE reminder_log SET status = ?, error = ?, sent_at = NOW()
          WHERE proposal_id = ? AND kind = ? AND recipient = ?'
    );
    $stmt->bind_param('ssiss', $status, $error, $proposalId, $kind, $recipient);
    $stmt->execute();
    $stmt->close();
}

/**
 * Sends everything currently due.
 *
 * @param bool $dryRun      list what would be sent, send nothing, claim nothing
 * @param bool $retryFailed clear earlier failures first so they are attempted again
 * @return array{sent:int, failed:int, skipped:int, lines:string[]}
 */
function ec_reminders_run(mysqli $conn, int $limit = EC_REMINDER_BATCH, bool $dryRun = false, bool $retryFailed = false): array
{
    $result = ['sent' => 0, 'failed' => 0, 'skipped' => 0, 'lines' => []];

    if (!$dryRun && !ec_mail_configured()) {
        $result['lines'][] = 'SMTP is not configured - nothing sent. See includes/config.local.example.php.';
        return $result;
    }

    if ($retryFailed && !$dryRun) {
        $conn->query("DELETE FROM reminder_log WHERE status = 'failed'");
        $result['lines'][] = sprintf('Cleared %d earlier failure(s) for retry.', $conn->affected_rows);
    }

    foreach (ec_reminders_due($conn) as $item) {
        foreach ($item['recipients'] as $recipient) {
            if (($result['sent'] + $result['failed']) >= $limit) {
                $result['lines'][] = 'Batch limit reached; the rest will go out on the next run.';
                return $result;
            }

            $proposalId = (int) $item['proposal']['id'];
            $label = sprintf('%-13s PRO-%04d -> %s', $item['kind'], $proposalId, $recipient['email']);

            if ($dryRun) {
                $already = ec_reminder_already_sent($conn, $proposalId, $item['kind'], $recipient['email']);
                if ($already) {
                    $result['skipped']++;
                    $result['lines'][] = $label . '  (already sent)';
                } else {
                    $result['lines'][] = $label . '  WOULD SEND';
                }
                continue;
            }

            if (!ec_reminder_claim($conn, $proposalId, $item['kind'], $recipient['email'])) {
                $result['skipped']++;
                continue;
            }

            [$subject, $html, $text] = ec_reminder_message($item['kind'], $item['proposal'], $recipient);
            [$sent, $error] = ec_mail_send($recipient['email'], $recipient['name'], $subject, $html, $text);

            ec_reminder_settle($conn, $proposalId, $item['kind'], $recipient['email'], $sent, $error);

            if ($sent) {
                $result['sent']++;
                $result['lines'][] = $label . '  sent';
            } else {
                $result['failed']++;
                $result['lines'][] = $label . '  FAILED: ' . $error;
            }
        }
    }

    return $result;
}

/** True when this slot already has a log row. Used by the dry run only. */
function ec_reminder_already_sent(mysqli $conn, int $proposalId, string $kind, string $recipient): bool
{
    $stmt = $conn->prepare(
        'SELECT 1 FROM reminder_log WHERE proposal_id = ? AND kind = ? AND recipient = ?'
    );
    $stmt->bind_param('iss', $proposalId, $kind, $recipient);
    $stmt->execute();
    $found = $stmt->get_result()->num_rows > 0;
    $stmt->close();
    return $found;
}

/**
 * Catch-up sweep for installations with no cron.
 *
 * Claims an hourly slot in app_state - so concurrent dashboard loads do not
 * each start sending - and does the work after the response has been flushed,
 * so nobody waits on SMTP. Every failure is swallowed: a broken mail server
 * must never break a dashboard.
 */
function ec_reminders_maybe_run(mysqli $conn): void
{
    if (!ec_mail_configured()) {
        return;
    }

    try {
        $conn->query(
            "INSERT INTO app_state (name, value) VALUES ('reminders_last_run', NOW())
             ON DUPLICATE KEY UPDATE value =
                IF(value IS NULL OR CAST(value AS DATETIME) < NOW() - INTERVAL 1 HOUR, NOW(), value)"
        );
        // 1 = inserted, 2 = updated, 0 = another request holds this hour's slot.
        if ($conn->affected_rows === 0) {
            return;
        }
    } catch (Throwable $e) {
        // Most likely the migration has not been run yet.
        return;
    }

    register_shutdown_function(function () use ($conn) {
        // Hand the page back to the browser before talking to the mail server.
        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        }
        try {
            ec_reminders_run($conn, 10);
        } catch (Throwable $e) {
            ec_log_exception($e, 'reminders_catchup');
        }
    });
}
