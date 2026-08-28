<?php
require_once 'includes/workflow.php';
require_once 'includes/db.php';

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || strtoupper($_SESSION["role"] ?? '') !== 'COORDINATOR'){
    header("location: index.php?modal=login");
    exit;
}

/** Sends the coordinator back to their dashboard with a short status code. */
function ec_preloaded_redirect(string $key, string $value = '1'): never {
    header('location: dashboard_coordinator.php?' . urlencode($key) . '=' . urlencode($value));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ec_preloaded_redirect('error', 'method');
}

if (!ec_csrf_valid()) {
    ec_preloaded_redirect('error', 'token');
}

if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
    ec_preloaded_redirect('error', 'upload');
}

$file = $_FILES['csv_file']['tmp_name'];
if (!is_uploaded_file($file)) {
    ec_preloaded_redirect('error', 'upload');
}

// 5 MB ceiling for a schedule CSV.
if ($_FILES['csv_file']['size'] > 5 * 1024 * 1024) {
    ec_preloaded_redirect('error', 'toolarge');
}

$handle = fopen($file, 'r');
if ($handle === false) {
    ec_preloaded_redirect('error', 'unreadable');
}

$imported = 0;
$skipped  = 0;

// The whole import succeeds or none of it does, so a malformed row partway
// through cannot leave a half-loaded schedule.
$conn->begin_transaction();
try {
    fgetcsv($handle); // Skip header

    $stmt = $conn->prepare('INSERT INTO preloaded_events (sl_no, event_date, event_month, activity, budget, university_contribution, convener) VALUES (?, ?, ?, ?, ?, ?, ?)');

    while (($data = fgetcsv($handle, 4096, ',')) !== FALSE) {
        // Ignore blank trailing lines.
        if ($data === [null] || (count($data) === 1 && trim((string)$data[0]) === '')) {
            continue;
        }
        if (count($data) < 7) {
            $skipped++;
            continue;
        }
        $sl      = (int) $data[0];
        $edate   = mb_substr(trim((string)$data[1]), 0, 100);
        $emonth  = mb_substr(trim((string)$data[2]), 0, 50);
        $act     = trim((string)$data[3]);
        $budget  = (float) str_replace([',', ' '], '', (string)$data[4]);
        $unicont = (float) str_replace([',', ' '], '', (string)$data[5]);
        $conv    = mb_substr(trim((string)$data[6]), 0, 255);

        $stmt->bind_param('isssdds', $sl, $edate, $emonth, $act, $budget, $unicont, $conv);
        $stmt->execute();
        $imported++;
    }
    $stmt->close();
    fclose($handle);
    $conn->commit();
} catch (Throwable $e) {
    $conn->rollback();
    if (is_resource($handle)) { fclose($handle); }
    ec_log_exception($e, 'upload_preloaded');
    ec_preloaded_redirect('error', 'import');
}

header('location: dashboard_coordinator.php?success=1&imported=' . $imported . '&skipped=' . $skipped);
exit;
