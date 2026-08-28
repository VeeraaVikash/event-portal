<?php
session_start();
require_once 'includes/db.php';

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !isset($_GET['id'])){
    http_response_code(403);
    echo json_encode(["error" => "Unauthorized"]);
    exit;
}

$id = intval($_GET['id']);
$user_id = $_SESSION["id"];

// Permission check
$role = strtoupper($_SESSION["role"] ?? '');
if ($role === 'HOD' || $role === 'COORDINATOR') {
    $dq = "SELECT department FROM users WHERE id = ?";
    $dst = $conn->prepare($dq);
    $dst->bind_param("i", $user_id);
    $dst->execute();
    $dst->bind_result($viewer_dept);
    $dst->fetch();
    $dst->close();

    $query = "SELECT p.*, pf.university_fund, pf.registration_fund, pf.sponsorship_fund, pf.other_sources 
              FROM proposals p 
              LEFT JOIN proposal_financials pf ON p.id = pf.proposal_id 
              JOIN users u ON p.user_id = u.id 
              WHERE p.id = ? AND u.department = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("is", $id, $viewer_dept);
} else {
    $query = "SELECT p.*, pf.university_fund, pf.registration_fund, pf.sponsorship_fund, pf.other_sources 
              FROM proposals p 
              LEFT JOIN proposal_financials pf ON p.id = pf.proposal_id 
              WHERE p.id = ? AND p.user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $id, $user_id);
}

$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows > 0) {
    $proposal = $result->fetch_assoc();
    
    // Fetch guests
    $guests = [];
    $g_q = $conn->query("SELECT * FROM proposal_guests WHERE proposal_id = $id");
    while($r = $g_q->fetch_assoc()) $guests[] = $r;
    $proposal['guests'] = $guests;
    
    // Fetch travel
    $travel = [];
    $t_q = $conn->query("SELECT * FROM proposal_travel_accomm WHERE proposal_id = $id");
    while($r = $t_q->fetch_assoc()) $travel[] = $r;
    $proposal['travel'] = $travel;
    
    // Fetch budgets
    $budgets = [];
    $b_q = $conn->query("SELECT * FROM proposal_budgets WHERE proposal_id = $id");
    while($r = $b_q->fetch_assoc()) $budgets[] = $r;
    $proposal['budgets'] = $budgets;
    
    // Fetch sponsors
    $sponsors = [];
    $s_q = $conn->query("SELECT * FROM proposal_sponsors WHERE proposal_id = $id");
    while($r = $s_q->fetch_assoc()) $sponsors[] = $r;
    $proposal['sponsors'] = $sponsors;

    // Fetch messages
    $messages = [];
    $msg_query = "SELECT m.id, m.sender_id, m.message, m.created_at, u.full_name, u.role FROM proposal_messages m JOIN users u ON m.sender_id = u.id WHERE m.proposal_id = ? ORDER BY m.created_at ASC";
    $msg_stmt = $conn->prepare($msg_query);
    $msg_stmt->bind_param("i", $id);
    $msg_stmt->execute();
    $msg_res = $msg_stmt->get_result();
    while($row = $msg_res->fetch_assoc()) {
        $messages[] = $row;
    }
    $msg_stmt->close();
    
    $proposal['messages'] = $messages;
    $proposal['my_id'] = $user_id;

    echo json_encode($proposal);
} else {
    http_response_code(404);
    echo json_encode(["error" => "Proposal not found"]);
}
$stmt->close();
?>
