<?php
/**
 * Department analysis for the dashboard panel.
 *
 * Read-only, and scoped to the caller's own department - the same boundary the
 * rest of the application applies. Conveners have no department-wide view, so
 * they are refused here rather than being given a filtered one.
 */

require_once 'includes/workflow.php';
require_once 'includes/db.php';
require_once 'includes/analytics.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    ec_json(['status' => 'error', 'message' => 'Unauthorized'], 401);
    exit;
}

$role = (string) ($_SESSION['role'] ?? '');
if (!ec_may_view_analytics($role)) {
    ec_json(['status' => 'error', 'message' => 'This view is for HODs and coordinators.'], 403);
    exit;
}

$department = ec_viewer_department($conn, (int) $_SESSION['id']);
if ($department === null) {
    ec_json(['status' => 'error', 'message' => 'Your account has no department set.'], 409);
    exit;
}

$period = ec_period_range((string) ($_GET['period'] ?? '3m'));

try {
    $data = ec_analytics($conn, $department, $period);
} catch (Throwable $e) {
    $ref = ec_log_exception($e, 'analytics');
    ec_json(['status' => 'error', 'message' => "Could not build the analysis. Reference: {$ref}"], 500);
    exit;
}

// The event list is only needed by the archive; the panel renders totals,
// the faculty table and the two charts.
$data['events'] = array_slice($data['events'], 0, 200);

ec_json(array_merge(['status' => 'success'], $data));
