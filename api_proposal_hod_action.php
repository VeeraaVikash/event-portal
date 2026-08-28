<?php
session_start();
require_once 'includes/db.php';

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || strtoupper($_SESSION["role"]) !== 'HOD'){
    echo json_encode(["status" => "error", "message" => "Unauthorized"]);
    exit;
}

$user_id = $_SESSION["id"];

$data = json_decode(file_get_contents('php://input'), true);

if(!$data || !isset($data['id']) || !isset($data['action'])) {
    echo json_encode(["status" => "error", "message" => "Missing data"]);
    exit;
}

$prop_id = intval($data['id']);
$action = strtolower($data['action']); // 'approve', 'reject', 'review'
$reason = isset($data['reason']) ? trim($data['reason']) : '';

// Get HOD department
$dq = "SELECT department FROM users WHERE id = ?";
$dst = $conn->prepare($dq);
$dst->bind_param("i", $user_id);
$dst->execute();
$dst->bind_result($hod_dept);
$dst->fetch();
$dst->close();

// Ensure the proposal belongs to a user in the HOD's department
$q = "SELECT p.status FROM proposals p JOIN users u ON p.user_id = u.id WHERE p.id = ? AND u.department = ?";
$st = $conn->prepare($q);
$st->bind_param("is", $prop_id, $hod_dept);
$st->execute();
$res = $st->get_result();

if($res->num_rows === 0) {
    echo json_encode(["status" => "error", "message" => "Proposal not found or unauthorized"]);
    exit;
}
$row = $res->fetch_assoc();
$current_status = $row['status'];
$st->close();

if (strcasecmp($current_status, 'Cancelled') === 0) {
    echo json_encode(["status" => "error", "message" => "Proposal is cancelled and cannot be modified."]);
    exit;
}

$new_status = '';
$log_msg = '';

if ($action === 'approve') {
    $new_status = 'Approved';
    $log_msg = "APPROVED BY HOD.";
    if ($reason !== '') {
        $log_msg .= " Remarks: " . $reason;
    }
} elseif ($action === 'reject') {
    if (empty($reason)) {
        echo json_encode(["status" => "error", "message" => "Reason is required for Rejection"]);
        exit;
    }
    $new_status = 'Rejected';
    $log_msg = "REJECTED BY HOD: " . $reason;
} elseif ($action === 'review') {
    if (empty($reason)) {
        echo json_encode(["status" => "error", "message" => "Reason is required to send back for Review"]);
        exit;
    }
    $new_status = 'Review';
    $log_msg = "REVIEW REQUIRED (HOD): " . $reason;
} else {
    echo json_encode(["status" => "error", "message" => "Invalid action"]);
    exit;
}

$conn->begin_transaction();
try {
    $uQ = "UPDATE proposals SET status = ? WHERE id = ?";
    $stU = $conn->prepare($uQ);
    $stU->bind_param("si", $new_status, $prop_id);
    $stU->execute();
    $stU->close();

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
?>