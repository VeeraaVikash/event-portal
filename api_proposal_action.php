<?php
session_start();
require_once 'includes/db.php';

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    echo json_encode(["status" => "error", "message" => "Unauthorized"]);
    exit;
}

$user_id = $_SESSION["id"];

$data = json_decode(file_get_contents('php://input'), true);

if(!$data || !isset($data['id']) || !isset($data['action']) || !isset($data['reason'])) {
    echo json_encode(["status" => "error", "message" => "Missing data"]);
    exit;
}

$prop_id = intval($data['id']);
$action = $data['action']; // 'cancel' or 'reschedule'
$reason = trim($data['reason']);
$new_start = $data['new_start'] ?? null;
$new_end = $data['new_end'] ?? null;

// Ensure the proposal belongs to user
$q = "SELECT status, start_date, end_date FROM proposals WHERE id = ? AND user_id = ?";
$st = $conn->prepare($q);
$st->bind_param("ii", $prop_id, $user_id);
$st->execute();
$res = $st->get_result();

if($res->num_rows === 0) {
    echo json_encode(["status" => "error", "message" => "Proposal not found or unauthorized"]);
    exit;
}
$row = $res->fetch_assoc();
$st->close();

$uQ = "UPDATE proposals SET status = ? WHERE id = ?";
if($action === 'cancel') {
    $new_status = 'Cancelled';
    $log_msg = "CANCELLED BY CONVENER: " . $reason;
} elseif($action === 'reschedule') {
    if(!$new_start || !$new_end) {
        echo json_encode(["status" => "error", "message" => "Missing new dates for rescheduling"]);
        exit;
    }
    if($row['status'] !== 'Approved') {
        echo json_encode(["status" => "error", "message" => "Only Approved events can be explicitly rescheduled"]);
        exit;
    }
    $new_status = 'Rescheduled';
    $log_msg = "RESCHEDULE REQUESTED (From: {$row['start_date']} to $new_start): " . $reason;
    $uQ = "UPDATE proposals SET status = ?, start_date = '{$new_start}', end_date = '{$new_end}' WHERE id = ?";
} else {
    echo json_encode(["status" => "error", "message" => "Invalid action"]);
    exit;
}

$conn->begin_transaction();
try {
    // Update status & possibly dates
    $stU = $conn->prepare($uQ);
    $stU->bind_param("si", $new_status, $prop_id);
    $stU->execute();
    $stU->close();

    // Log the reason
    $msgQ = "INSERT INTO proposal_messages (proposal_id, sender_id, message) VALUES (?, ?, ?)";
    $stMsg = $conn->prepare($msgQ);
    $stMsg->bind_param("iis", $prop_id, $user_id, $log_msg);
    $stMsg->execute();
    $stMsg->close();

    $conn->commit();
    echo json_encode(["status" => "success", "newStatus" => $new_status]);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(["status" => "error", "message" => "Database error"]);
}

