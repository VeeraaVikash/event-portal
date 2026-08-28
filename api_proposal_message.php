<?php
require_once 'includes/workflow.php';
require_once 'includes/db.php';

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    http_response_code(403);
    echo json_encode(["error" => "Unauthorized"]);
    exit;
}

if($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["error" => "Invalid request method"]);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
if(!$data || !isset($data['proposal_id']) || !isset($data['message'])) {
    http_response_code(400);
    echo json_encode(["error" => "Missing data"]);
    exit;
}

if(!ec_csrf_valid($data)) {
    http_response_code(403);
    echo json_encode(["error" => "Invalid or missing security token. Please reload the page."]);
    exit;
}

$proposal_id = intval($data['proposal_id']);
$message = trim($data['message']);
$user_id = $_SESSION["id"];
$role = strtoupper($_SESSION["role"] ?? '');

if($message === '') {
    http_response_code(400);
    echo json_encode(["error" => "Message cannot be empty"]);
    exit;
}
if(mb_strlen($message) > 5000) {
    http_response_code(400);
    echo json_encode(["error" => "Message is too long (5000 characters maximum)"]);
    exit;
}

if ($role === 'COORDINATOR') {
    http_response_code(403);
    echo json_encode(["error" => "Coordinator is read-only and cannot communicate"]);
    exit;
}

$query = "SELECT p.status, p.user_id, u.department as owner_dept FROM proposals p JOIN users u ON p.user_id = u.id WHERE p.id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $proposal_id);
$stmt->execute();
$res = $stmt->get_result();
if($res->num_rows === 0) {
    http_response_code(403);
    echo json_encode(["error" => "Access denied"]);
    exit;
}
$prop = $res->fetch_assoc();

// Verify user can post
$canAccess = false;
if ($role === 'HOD') {
    $dq = "SELECT department FROM users WHERE id = ?";
    $dst = $conn->prepare($dq);
    $dst->bind_param("i", $user_id);
    $dst->execute();
    $dst->bind_result($hod_dept);
    $dst->fetch();
    $dst->close();
    if ($hod_dept === $prop['owner_dept']) {
        $canAccess = true;
    }
} else {
    // Must be owner
    if ($prop['user_id'] == $user_id) {
        $canAccess = true;
    }
}

if (!$canAccess) {
    http_response_code(403);
    echo json_encode(["error" => "Access denied"]);
    exit;
}

if($prop['status'] !== 'Review') {
    http_response_code(403);
    echo json_encode(["error" => "Chat is only enabled when under Review"]);
    exit;
}
$stmt->close();

try {
    $insert = "INSERT INTO proposal_messages (proposal_id, sender_id, message) VALUES (?, ?, ?)";
    $stmt2 = $conn->prepare($insert);
    $stmt2->bind_param("iis", $proposal_id, $user_id, $message);
    $stmt2->execute();
    $id = $stmt2->insert_id;
    $stmt2->close();

    echo json_encode(["success" => true, "id" => $id]);
} catch (Throwable $e) {
    $ref = ec_log_exception($e, 'proposal_message');
    http_response_code(500);
    echo json_encode(["error" => "Could not send the message. Reference: {$ref}"]);
}
