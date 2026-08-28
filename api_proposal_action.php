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

$user_id = $_SESSION["id"];

$data = json_decode(file_get_contents('php://input'), true);

if(!$data || !isset($data['id']) || !isset($data['action']) || !isset($data['reason'])) {
    ec_json(["status" => "error", "message" => "Missing data"], 400);
    exit;
}

if(!ec_csrf_valid($data)) {
    ec_json(["status" => "error", "message" => "Invalid or missing security token. Please reload the page."], 403);
    exit;
}

$prop_id = intval($data['id']);
$action = $data['action']; // 'cancel' or 'reschedule'
$reason = trim($data['reason']);
$new_start = $data['new_start'] ?? null;
$new_end = $data['new_end'] ?? null;

if($reason === '') {
    ec_json(["status" => "error", "message" => "A reason is required"], 400);
    exit;
}

// Ensure the proposal belongs to user
$q = "SELECT status, start_date, end_date FROM proposals WHERE id = ? AND user_id = ?";
$st = $conn->prepare($q);
$st->bind_param("ii", $prop_id, $user_id);
$st->execute();
$res = $st->get_result();

if($res->num_rows === 0) {
    ec_json(["status" => "error", "message" => "Proposal not found or unauthorized"], 404);
    exit;
}
$row = $res->fetch_assoc();
$st->close();

$current_status = $row['status'];

// Optional stale-update guard: when the client tells us which status it was
// showing, refuse to act if the proposal has moved on since.
if(isset($data['expected_status']) && $data['expected_status'] !== $current_status) {
    ec_json([
        "status" => "error",
        "message" => "This proposal was updated by someone else (now {$current_status}). Please refresh.",
        "currentStatus" => $current_status
    ], 409);
    exit;
}

[$allowed, $why] = ec_convener_transition_allowed($current_status, $action);
if(!$allowed) {
    ec_json(["status" => "error", "message" => $why], 409);
    exit;
}

if($action === 'cancel') {
    $new_status = 'Cancelled';
    $log_msg = "CANCELLED BY CONVENER: " . $reason;
} else { // reschedule - already validated by ec_convener_transition_allowed()
    if(!ec_valid_date($new_start) || !ec_valid_date($new_end)) {
        ec_json(["status" => "error", "message" => "Missing or invalid new dates for rescheduling"], 400);
        exit;
    }
    if($new_end < $new_start) {
        ec_json(["status" => "error", "message" => "End date cannot be before start date"], 400);
        exit;
    }
    $new_status = 'Rescheduled';
    $log_msg = "RESCHEDULE REQUESTED (From: {$row['start_date']} to $new_start): " . $reason;
}

$conn->begin_transaction();
try {
    // Compare-and-swap on status so two simultaneous actions cannot both win.
    if($action === 'reschedule') {
        $uQ = "UPDATE proposals SET status = ?, start_date = ?, end_date = ? WHERE id = ? AND user_id = ? AND status = ?";
        $stU = $conn->prepare($uQ);
        $stU->bind_param("sssiis", $new_status, $new_start, $new_end, $prop_id, $user_id, $current_status);
    } else {
        $uQ = "UPDATE proposals SET status = ? WHERE id = ? AND user_id = ? AND status = ?";
        $stU = $conn->prepare($uQ);
        $stU->bind_param("siis", $new_status, $prop_id, $user_id, $current_status);
    }
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

    // Log the reason
    $msgQ = "INSERT INTO proposal_messages (proposal_id, sender_id, message) VALUES (?, ?, ?)";
    $stMsg = $conn->prepare($msgQ);
    $stMsg->bind_param("iis", $prop_id, $user_id, $log_msg);
    $stMsg->execute();
    $stMsg->close();

    $conn->commit();
    ec_json(["status" => "success", "newStatus" => $new_status]);
} catch (Throwable $e) {
    $conn->rollback();
    $ref = ec_log_exception($e, 'proposal_action');
    ec_json(["status" => "error", "message" => "Could not complete the action. Reference: {$ref}"], 500);
}
