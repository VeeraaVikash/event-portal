<?php
require_once 'includes/workflow.php';
require_once 'includes/db.php';

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || strtoupper($_SESSION["role"] ?? '') !== 'HOD'){
    ec_json(["status" => "error", "message" => "Unauthorized"], 401);
    exit;
}

if($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ec_json(["status" => "error", "message" => "Invalid request method"], 405);
    exit;
}

$user_id = $_SESSION["id"];

$data = json_decode(file_get_contents('php://input'), true);

if(!$data || !isset($data['id']) || !isset($data['action'])) {
    ec_json(["status" => "error", "message" => "Missing data"], 400);
    exit;
}

if(!ec_csrf_valid($data)) {
    ec_json(["status" => "error", "message" => "Invalid or missing security token. Please reload the page."], 403);
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
    ec_json(["status" => "error", "message" => "Proposal not found or unauthorized"], 404);
    exit;
}
$row = $res->fetch_assoc();
$current_status = $row['status'];
$st->close();

// Optional stale-update guard for a dashboard that has gone out of date.
if(isset($data['expected_status']) && $data['expected_status'] !== $current_status) {
    ec_json([
        "status" => "error",
        "message" => "This proposal was updated by someone else (now {$current_status}). Please refresh.",
        "currentStatus" => $current_status
    ], 409);
    exit;
}

[$allowed, $why] = ec_hod_transition_allowed($current_status, $action);
if(!$allowed) {
    ec_json(["status" => "error", "message" => $why], 409);
    exit;
}

$new_status = EC_HOD_ACTION_STATUS[$action];

if ($action === 'approve') {
    $log_msg = "APPROVED BY HOD.";
    if ($reason !== '') {
        $log_msg .= " Remarks: " . $reason;
    }
} elseif ($action === 'reject') {
    if ($reason === '') {
        ec_json(["status" => "error", "message" => "Reason is required for Rejection"], 400);
        exit;
    }
    $log_msg = "REJECTED BY HOD: " . $reason;
} else { // review
    if ($reason === '') {
        ec_json(["status" => "error", "message" => "Reason is required to send back for Review"], 400);
        exit;
    }
    $log_msg = "REVIEW REQUIRED (HOD): " . $reason;
}

$conn->begin_transaction();
try {
    // Compare-and-swap: scoped to the department AND the status we just read,
    // so two HODs acting at once cannot both record a decision.
    $uQ = "UPDATE proposals p JOIN users u ON p.user_id = u.id
              SET p.status = ?
            WHERE p.id = ? AND u.department = ? AND p.status = ?";
    $stU = $conn->prepare($uQ);
    $stU->bind_param("siss", $new_status, $prop_id, $hod_dept, $current_status);
    $stU->execute();
    $changed = $stU->affected_rows;
    $stU->close();

    if($changed === 0) {
        $conn->rollback();
        ec_json([
            "status" => "error",
            "message" => "This proposal was updated by someone else. Please refresh and try again."
        ], 409);
        exit;
    }

    $msgQ = "INSERT INTO proposal_messages (proposal_id, sender_id, message) VALUES (?, ?, ?)";
    $stMsg = $conn->prepare($msgQ);
    $stMsg->bind_param("iis", $prop_id, $user_id, $log_msg);
    $stMsg->execute();
    $stMsg->close();

    $conn->commit();
    ec_json(["status" => "success", "newStatus" => $new_status]);
} catch (Throwable $e) {
    $conn->rollback();
    $ref = ec_log_exception($e, 'hod_action');
    ec_json(["status" => "error", "message" => "Could not complete the action. Reference: {$ref}"], 500);
}
