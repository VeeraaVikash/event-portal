<?php
require_once 'includes/workflow.php';
require_once 'includes/db.php';

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    ec_json(["status" => "error", "message" => "Unauthorized"], 401);
    exit;
}

if($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ec_json(["status" => "error", "message" => "Invalid request method"], 405);
    exit;
}

if(!ec_csrf_valid()) {
    ec_json(["status" => "error", "message" => "Invalid or missing security token. Please reload the page."], 403);
    exit;
}

$user_id = $_SESSION["id"];
$prop_id = intval($_POST['proposal_id'] ?? 0);

if(!$prop_id) {
    ec_json(["status" => "error", "message" => "Missing proposal ID"], 400);
    exit;
}

// Ensure the proposal belongs to user
$q = "SELECT status FROM proposals WHERE id = ? AND user_id = ?";
$st = $conn->prepare($q);
$st->bind_param("ii", $prop_id, $user_id);
$st->execute();
$res = $st->get_result();

if($res->num_rows === 0) {
    ec_json(["status" => "error", "message" => "Proposal not found or unauthorized"], 404);
    exit;
}
$st->close();

if(!isset($_FILES['report_pdf'])) {
    ec_json(["status" => "error", "message" => "No report file was received"], 400);
    exit;
}

// Translate PHP's upload error codes into something actionable.
$uploadErr = $_FILES['report_pdf']['error'];
if($uploadErr !== UPLOAD_ERR_OK) {
    $map = [
        UPLOAD_ERR_INI_SIZE   => 'The report is larger than the server allows',
        UPLOAD_ERR_FORM_SIZE  => 'The report is larger than the form allows',
        UPLOAD_ERR_PARTIAL    => 'The upload was interrupted; please retry',
        UPLOAD_ERR_NO_FILE    => 'No report file was received',
        UPLOAD_ERR_NO_TMP_DIR => 'Server storage is unavailable',
        UPLOAD_ERR_CANT_WRITE => 'Server storage is unavailable',
        UPLOAD_ERR_EXTENSION  => 'The upload was blocked by the server',
    ];
    ec_json(["status" => "error", "message" => $map[$uploadErr] ?? 'Upload failed'], 400);
    exit;
}

$tmp = $_FILES['report_pdf']['tmp_name'];
if(!is_uploaded_file($tmp)) {
    ec_json(["status" => "error", "message" => "Invalid upload"], 400);
    exit;
}

// Size ceiling (20 MB) - these are generated event reports, not archives.
const EC_MAX_REPORT_BYTES = 20 * 1024 * 1024;
if($_FILES['report_pdf']['size'] > EC_MAX_REPORT_BYTES) {
    ec_json(["status" => "error", "message" => "Report exceeds the 20 MB limit"], 400);
    exit;
}
if($_FILES['report_pdf']['size'] === 0) {
    ec_json(["status" => "error", "message" => "The report file is empty"], 400);
    exit;
}

// Confirm it really is a PDF rather than trusting the client-supplied type.
$handle = fopen($tmp, 'rb');
$magic = $handle ? fread($handle, 5) : '';
if($handle) { fclose($handle); }
if($magic !== '%PDF-') {
    ec_json(["status" => "error", "message" => "Only PDF reports are accepted"], 400);
    exit;
}

$upload_dir = 'reports/';
if(!is_dir($upload_dir) && !mkdir($upload_dir, 0755, true) && !is_dir($upload_dir)) {
    $ref = ec_log_exception(new RuntimeException("Cannot create {$upload_dir}"), 'save_report');
    ec_json(["status" => "error", "message" => "Report storage is unavailable. Reference: {$ref}"], 500);
    exit;
}

$file_name = 'Report_PRO_' . sprintf('%04d', $prop_id) . '_' . time() . '.pdf';
$path = $upload_dir . $file_name;

if(!move_uploaded_file($tmp, $path)) {
    ec_json(["status" => "error", "message" => "Failed to store the uploaded report"], 500);
    exit;
}

// Record the path; if the DB write fails, remove the orphaned file so storage
// and database stay consistent.
try {
    $uQ = "UPDATE proposals SET report_path = ? WHERE id = ? AND user_id = ?";
    $stU = $conn->prepare($uQ);
    $stU->bind_param("sii", $path, $prop_id, $user_id);
    $stU->execute();
    $stU->close();
    ec_json(["status" => "success", "report_path" => $path]);
} catch (Throwable $e) {
    if(is_file($path)) { @unlink($path); }
    $ref = ec_log_exception($e, 'save_report');
    ec_json(["status" => "error", "message" => "Could not save the report. Reference: {$ref}"], 500);
}
