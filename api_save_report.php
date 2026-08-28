<?php
session_start();
require_once 'includes/db.php';

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    echo json_encode(["status" => "error", "message" => "Unauthorized"]);
    exit;
}

if($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["status" => "error", "message" => "Invalid request method"]);
    exit;
}

$user_id = $_SESSION["id"];
$prop_id = intval($_POST['proposal_id'] ?? 0);

if(!$prop_id) {
    echo json_encode(["status" => "error", "message" => "Missing proposal ID"]);
    exit;
}

// Ensure the proposal belongs to user
$q = "SELECT status FROM proposals WHERE id = ? AND user_id = ?";
$st = $conn->prepare($q);
$st->bind_param("ii", $prop_id, $user_id);
$st->execute();
$res = $st->get_result();

if($res->num_rows === 0) {
    echo json_encode(["status" => "error", "message" => "Proposal not found or unauthorized"]);
    exit;
}
$st->close();

if(!isset($_FILES['report_pdf']) || $_FILES['report_pdf']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(["status" => "error", "message" => "Failed to upload generated PDF"]);
    exit;
}

$upload_dir = 'reports/';
if(!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

$file_name = 'Report_PRO_' . sprintf('%04d', $prop_id) . '_' . time() . '.pdf';
$path = $upload_dir . $file_name;

if(move_uploaded_file($_FILES['report_pdf']['tmp_name'], $path)) {
    // Save to DB
    $uQ = "UPDATE proposals SET report_path = ? WHERE id = ?";
    $stU = $conn->prepare($uQ);
    $stU->bind_param("si", $path, $prop_id);
    if($stU->execute()){
         echo json_encode(["status" => "success", "report_path" => $path]);
    } else {
         echo json_encode(["status" => "error", "message" => "Database link failed"]);
    }
    $stU->close();
} else {
    echo json_encode(["status" => "error", "message" => "Failed to move uploaded file correctly"]);
}
