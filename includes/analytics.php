<?php
/**
 * Department analysis over a rolling period.
 *
 * Used by three callers that must agree on every number: the dashboard panel
 * (api_analytics.php), the CSV files inside the archive, and the archive's
 * SUMMARY.txt (download_report_archive.php).
 *
 * An event belongs to a period by its END date - that is when it happened and
 * when its report became due. Periods roll back from today rather than snapping
 * to calendar months, so "past 3 months" on 13 September means 13 June onwards.
 */

require_once __DIR__ . '/workflow.php';
require_once __DIR__ . '/media.php';

/** The periods the dashboards offer, longest key first in the UI. */
function ec_periods(): array
{
    return [
        '1w'  => ['label' => 'Past week',      'modifier' => '-1 week'],
        '1m'  => ['label' => 'Past month',     'modifier' => '-1 month'],
        '2m'  => ['label' => 'Past 2 months',  'modifier' => '-2 months'],
        '3m'  => ['label' => 'Past 3 months',  'modifier' => '-3 months'],
        '6m'  => ['label' => 'Past 6 months',  'modifier' => '-6 months'],
        '12m' => ['label' => 'Past year',      'modifier' => '-12 months'],
    ];
}

/**
 * Resolves a period key to a date range.
 *
 * Unknown keys fall back to three months rather than erroring: the value
 * arrives from a query string and a bad one should not break a dashboard.
 */
function ec_period_range(string $key): array
{
    $periods = ec_periods();
    if (!isset($periods[$key])) {
        $key = '3m';
    }
    $to   = date('Y-m-d');
    $from = date('Y-m-d', strtotime($periods[$key]['modifier']));

    return [
        'key'   => $key,
        'label' => $periods[$key]['label'],
        'from'  => $from,
        'to'    => $to,
    ];
}

/** The department a viewer belongs to, or null if they have none. */
function ec_viewer_department(mysqli $conn, int $userId): ?string
{
    $stmt = $conn->prepare('SELECT department FROM users WHERE id = ?');
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $stmt->bind_result($department);
    $stmt->fetch();
    $stmt->close();

    return ($department === null || $department === '') ? null : $department;
}

/** True when the role may see department-wide figures. */
function ec_may_view_analytics(string $role): bool
{
    $role = strtoupper($role);
    return $role === 'HOD' || $role === 'COORDINATOR';
}

/**
 * Everything the panel, the CSVs and the summary need, in one pass.
 *
 * Aggregation happens in PHP rather than in six GROUP BY queries: the row set
 * is one department's events for at most a year, and doing it here keeps the
 * totals and the per-faculty table guaranteed consistent with the event list
 * they are derived from.
 */
function ec_analytics(mysqli $conn, string $department, array $period): array
{
    $sql = 'SELECT p.id, p.title, p.category, p.start_date, p.end_date, p.status,
                   p.total_expected_participants, p.actual_participants,
                   p.report_path, p.report_generated_at,
                   u.id AS convener_id, u.full_name AS convener_name, u.email AS convener_email,
                   (SELECT COALESCE(SUM(b.total), 0) FROM proposal_budgets b
                     WHERE b.proposal_id = p.id) AS budget_total,
                   (SELECT COUNT(*) FROM proposal_media m
                     WHERE m.proposal_id = p.id) AS media_count
              FROM proposals p
              JOIN users u ON p.user_id = u.id
             WHERE u.department = ?
               AND p.end_date IS NOT NULL
               AND p.end_date BETWEEN ? AND ?
             ORDER BY p.end_date DESC, p.id DESC';

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('sss', $department, $period['from'], $period['to']);
    $stmt->execute();
    $res = $stmt->get_result();

    $events   = [];
    $faculty  = [];
    $category = [];
    $months   = [];
    $status   = [];

    $totals = [
        'events'                => 0,
        'events_held'           => 0,
        'events_cancelled'      => 0,
        'attended'              => 0,
        'expected'              => 0,
        'attendance_recorded'   => 0,
        'budget'                => 0.0,
        'reports_filed'         => 0,
        'reports_pending'       => 0,
        'attachments'           => 0,
    ];

    $today = date('Y-m-d');

    while ($row = $res->fetch_assoc()) {
        $held = ($row['status'] === 'Approved' && $row['end_date'] <= $today);

        $expected = (int) $row['total_expected_participants'];
        $recorded = ($row['actual_participants'] === null) ? null : (int) $row['actual_participants'];
        // Fall back to the expected figure for events reported before the
        // attendance field existed, so a year-long view is not skewed to zero.
        $attended = $recorded ?? $expected;
        $budget   = (float) $row['budget_total'];
        $hasReport = !empty($row['report_path']);

        $event = [
            'id'            => (int) $row['id'],
            'ref'           => 'PRO-' . sprintf('%04d', (int) $row['id']),
            'title'         => $row['title'],
            'category'      => $row['category'],
            'start_date'    => $row['start_date'],
            'end_date'      => $row['end_date'],
            'status'        => $row['status'],
            'held'          => $held,
            'convener'      => $row['convener_name'],
            'convener_email' => $row['convener_email'],
            'expected'      => $expected,
            'attended'      => $held ? $attended : null,
            'attendance_recorded' => $recorded !== null,
            'budget'        => $budget,
            'report_filed'  => $hasReport,
            'report_path'   => $row['report_path'],
            'attachments'   => (int) $row['media_count'],
        ];
        $events[] = $event;

        $totals['events']++;
        $statusKey = $row['status'];
        $status[$statusKey] = ($status[$statusKey] ?? 0) + 1;
        if ($statusKey === 'Cancelled') {
            $totals['events_cancelled']++;
        }

        // Only events that actually took place feed the headline figures.
        if (!$held) {
            continue;
        }

        $totals['events_held']++;
        $totals['attended']    += $attended;
        $totals['expected']    += $expected;
        $totals['budget']      += $budget;
        $totals['attachments'] += (int) $row['media_count'];
        if ($recorded !== null) {
            $totals['attendance_recorded']++;
        }
        $hasReport ? $totals['reports_filed']++ : $totals['reports_pending']++;

        $key = $row['category'] ?: 'uncategorised';
        $category[$key] = ($category[$key] ?? 0) + 1;

        $month = substr((string) $row['end_date'], 0, 7);
        if (!isset($months[$month])) {
            $months[$month] = ['events' => 0, 'attended' => 0];
        }
        $months[$month]['events']++;
        $months[$month]['attended'] += $attended;

        $fid = (int) $row['convener_id'];
        if (!isset($faculty[$fid])) {
            $faculty[$fid] = [
                'name'            => $row['convener_name'],
                'email'           => $row['convener_email'],
                'events'          => 0,
                'attended'        => 0,
                'budget'          => 0.0,
                'reports_filed'   => 0,
                'reports_pending' => 0,
            ];
        }
        $faculty[$fid]['events']++;
        $faculty[$fid]['attended'] += $attended;
        $faculty[$fid]['budget']   += $budget;
        $hasReport ? $faculty[$fid]['reports_filed']++ : $faculty[$fid]['reports_pending']++;
    }
    $stmt->close();

    // Busiest first; a tie goes to whoever reached more participants.
    $faculty = array_values($faculty);
    usort($faculty, function ($a, $b) {
        return [$b['events'], $b['attended']] <=> [$a['events'], $a['attended']];
    });

    arsort($category);

    // Every month in the window appears, so a gap reads as a quiet month
    // rather than vanishing from the chart.
    $timeline = [];
    $cursor = new DateTime(substr($period['from'], 0, 8) . '01');
    $last   = new DateTime(substr($period['to'], 0, 8) . '01');
    while ($cursor <= $last) {
        $m = $cursor->format('Y-m');
        $timeline[] = [
            'month'    => $m,
            'label'    => $cursor->format('M Y'),
            'events'   => $months[$m]['events'] ?? 0,
            'attended' => $months[$m]['attended'] ?? 0,
        ];
        $cursor->modify('+1 month');
    }

    $totals['reports_percent'] = $totals['events_held'] > 0
        ? (int) round(($totals['reports_filed'] / $totals['events_held']) * 100)
        : 0;
    // How much of the expected turnout actually showed up.
    $totals['attendance_percent'] = $totals['expected'] > 0
        ? (int) round(($totals['attended'] / $totals['expected']) * 100)
        : 0;

    return [
        'department' => $department,
        'period'     => $period,
        'generated'  => date('Y-m-d H:i'),
        'totals'     => $totals,
        // Deliberately not 'status': these responses are wrapped in an envelope
        // whose own status field says whether the call succeeded.
        'by_status'  => $status,
        'categories' => $category,
        'timeline'   => $timeline,
        'faculty'    => $faculty,
        'events'     => $events,
    ];
}

/** Turns a stored category key into something printable. */
function ec_category_label(string $key): string
{
    return ucwords(str_replace('_', ' ', $key));
}

/** Indian-style grouping, matching the figures printed in the reports. */
function ec_money(float $amount): string
{
    return number_format($amount, 2);
}
