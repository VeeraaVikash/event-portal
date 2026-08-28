<?php
session_start();
require_once 'includes/db.php';

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: index.php?modal=login");
    exit;
}

$user_id = $_SESSION["id"];
$role = $_SESSION["role"] ?? 'Convener';

if (strtoupper($role) === 'HOD') {
    header("location: dashboard_hod.php");
    exit;
}

// Fetch Stats
$stats = [
    'total' => 0,
    'approved' => 0,
    'rejected' => 0,
    'review' => 0
];

$query = "SELECT status, COUNT(*) as count FROM proposals WHERE user_id = ? GROUP BY status";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $stats['total'] += $row['count'];
    if ($row['status'] == 'Approved') $stats['approved'] += $row['count'];
    if ($row['status'] == 'Rejected') $stats['rejected'] += $row['count'];
    if ($row['status'] == 'Review') $stats['review'] += $row['count'];
    if ($row['status'] == 'Pending') {
        // Depending on definition, Pending might also be grouped. 
        // We'll just rely on what we have, total covers everything.
    }
}
$stmt->close();

// Fetch Proposals List
$proposals = [];
$prop_query = "SELECT id, title, category, start_date, end_date, status, created_at FROM proposals WHERE user_id = ? ORDER BY created_at DESC";
$stmt2 = $conn->prepare($prop_query);
$stmt2->bind_param("i", $user_id);
$stmt2->execute();
$prop_result = $stmt2->get_result();
while ($row = $prop_result->fetch_assoc()) {
    $proposals[] = $row;
}
$stmt2->close();

// Fetch Approved Events for Calendar
$approved_events = [];
$cal_query = "SELECT id, title, start_date, end_date FROM proposals WHERE user_id = ? AND status = 'Approved'";
$stmt3 = $conn->prepare($cal_query);
$stmt3->bind_param("i", $user_id);
$stmt3->execute();
$cal_result = $stmt3->get_result();
while ($row = $cal_result->fetch_assoc()) {
    $approved_events[] = [
        'title' => 'PRO-' . sprintf('%04d', $row['id']),
        'start' => $row['start_date'],
        'end' => date('Y-m-d', strtotime($row['end_date'] . ' +1 day')), // FullCalendar exclusive end date logic
        'color' => '#10B981', // green-500 binding explicitly
        'extendedProps' => [
            'proposal_id' => $row['id']
        ]
    ];
}
$stmt3->close();

require_once 'views/dashboard.view.php';
?>
