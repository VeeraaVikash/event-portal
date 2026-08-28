<?php
session_start();
require_once 'includes/db.php';

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || strtoupper($_SESSION["role"]) !== 'COORDINATOR'){
    header("location: index.php?modal=login");
    exit;
}

$user_id = $_SESSION["id"];

// Get coordinator department
$coordinator_department = null;
$dept_query = "SELECT department FROM users WHERE id = ?";
$dept_stmt = $conn->prepare($dept_query);
$dept_stmt->bind_param("i", $user_id);
$dept_stmt->execute();
$dept_stmt->bind_result($coordinator_department);
$dept_stmt->fetch();
$dept_stmt->close();

// Fetch Stats
$stats = [
    'total' => 0,
    'approved' => 0,
    'rejected' => 0,
    'review' => 0,
    'pending' => 0,
    'rescheduled' => 0,
    'cancelled' => 0
];

// Stats for coordinator department only
$query = "SELECT p.status, COUNT(*) as count
          FROM proposals p
          JOIN users u ON p.user_id = u.id
          WHERE u.department = ?
          GROUP BY p.status";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $coordinator_department);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $stats['total'] += $row['count'];
    if ($row['status'] == 'Approved') $stats['approved'] += $row['count'];
    elseif ($row['status'] == 'Rejected') $stats['rejected'] += $row['count'];
    elseif ($row['status'] == 'Review') $stats['review'] += $row['count'];
    elseif ($row['status'] == 'Pending') $stats['pending'] += $row['count'];
    elseif ($row['status'] == 'Rescheduled') $stats['rescheduled'] += $row['count'];
    elseif ($row['status'] == 'Cancelled') $stats['cancelled'] += $row['count'];
}
$stmt->close();

// Yearly Data for Graph
$monthly_stats = array_fill(1, 12, 0);
$year_query = "SELECT MONTH(p.created_at) as month, COUNT(*) as count
               FROM proposals p
               JOIN users u ON p.user_id = u.id
               WHERE u.department = ? AND YEAR(p.created_at) = YEAR(CURRENT_DATE)
               GROUP BY MONTH(p.created_at)";
$stmt = $conn->prepare($year_query);
$stmt->bind_param("s", $coordinator_department);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $monthly_stats[$row['month']] = $row['count'];
}
$stmt->close();

$monthly_data_json = json_encode(array_values($monthly_stats));
$status_data_json = json_encode([$stats['approved'], $stats['rejected'], $stats['review'], $stats['pending'], $stats['rescheduled'], $stats['cancelled']]);

// Fetch Proposals List (department only)
$proposals = [];
$prop_query = "SELECT p.id, p.title, p.category, p.start_date, p.end_date, p.status, p.created_at, u.full_name as convener_name 
               FROM proposals p 
               JOIN users u ON p.user_id = u.id 
               WHERE u.department = ?
               ORDER BY p.created_at DESC";
$stmt2 = $conn->prepare($prop_query);
$stmt2->bind_param("s", $coordinator_department);
$stmt2->execute();
$prop_result = $stmt2->get_result();
while ($row = $prop_result->fetch_assoc()) {
    $proposals[] = $row;
}
$stmt2->close();

// Fetch Approved Events for Calendar (department only)
$approved_events = [];
$cal_query = "SELECT p.id, p.title, p.start_date, p.end_date
              FROM proposals p
              JOIN users u ON p.user_id = u.id
              WHERE u.department = ? AND p.status = 'Approved'";
$stmt3 = $conn->prepare($cal_query);
$stmt3->bind_param("s", $coordinator_department);
$stmt3->execute();
$cal_result = $stmt3->get_result();
while ($row = $cal_result->fetch_assoc()) {
    $approved_events[] = [
        'title' => 'PRO-' . sprintf('%04d', $row['id']),
        'start' => $row['start_date'],
        'end' => date('Y-m-d', strtotime($row['end_date'] . ' +1 day')),
        'color' => '#10B981',
        'extendedProps' => [
            'proposal_id' => $row['id']
        ]
    ];
}
$stmt3->close();

// Fetch Pre Approved Events
$preloaded_events = [];
$preloaded_query = "SELECT * FROM preloaded_events ORDER BY id DESC";
$stmt4 = $conn->prepare($preloaded_query);
$stmt4->execute();
$preloaded_result = $stmt4->get_result();
while ($row = $preloaded_result->fetch_assoc()) {
    $preloaded_events[] = $row;
}
$stmt4->close();

require_once 'views/dashboard_coordinator.view.php';
?>
