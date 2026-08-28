<?php
session_start();
require_once 'includes/db.php';

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || strtoupper($_SESSION["role"]) !== 'COORDINATOR'){
    header('location: index.php?modal=login');
    exit;
}

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="proposals_export_' . date('Y-m-d_H-i') . '.csv"');
header('Pragma: no-cache');
header('Expires: 0');

$output = fopen('php://output', 'w');

// Resolve coordinator department
$user_id = $_SESSION['id'];
$dept_stmt = $conn->prepare("SELECT department FROM users WHERE id = ?");
$dept_stmt->bind_param("i", $user_id);
$dept_stmt->execute();
$dept_stmt->bind_result($coordinator_department);
$dept_stmt->fetch();
$dept_stmt->close();

$query = "SELECT p.*, u.full_name as convener_name, u.department, u.email as convener_email
          FROM proposals p
          LEFT JOIN users u ON p.user_id = u.id
          WHERE u.department = ?
          ORDER BY p.id ASC";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $coordinator_department);
$stmt->execute();
$result = $stmt->get_result();

function fetch_related_rows($conn, $table, $proposal_id) {
    $sql = "SELECT * FROM {$table} WHERE proposal_id = ?";
    $s = $conn->prepare($sql);
    $s->bind_param("i", $proposal_id);
    $s->execute();
    $res = $s->get_result();
    $rows = [];
    while ($r = $res->fetch_assoc()) {
        $rows[] = $r;
    }
    $s->close();
    return $rows;
}

if ($result && $result->num_rows > 0) {
    $first_row = $result->fetch_assoc();
    $first_row['proposal_financials_json'] = json_encode(fetch_related_rows($conn, 'proposal_financials', (int)$first_row['id']), JSON_UNESCAPED_UNICODE);
    $first_row['proposal_guests_json'] = json_encode(fetch_related_rows($conn, 'proposal_guests', (int)$first_row['id']), JSON_UNESCAPED_UNICODE);
    $first_row['proposal_travel_accomm_json'] = json_encode(fetch_related_rows($conn, 'proposal_travel_accomm', (int)$first_row['id']), JSON_UNESCAPED_UNICODE);
    $first_row['proposal_budgets_json'] = json_encode(fetch_related_rows($conn, 'proposal_budgets', (int)$first_row['id']), JSON_UNESCAPED_UNICODE);
    $first_row['proposal_sponsors_json'] = json_encode(fetch_related_rows($conn, 'proposal_sponsors', (int)$first_row['id']), JSON_UNESCAPED_UNICODE);
    $first_row['proposal_messages_json'] = json_encode(fetch_related_rows($conn, 'proposal_messages', (int)$first_row['id']), JSON_UNESCAPED_UNICODE);

    fputcsv($output, array_keys($first_row));
    fputcsv($output, $first_row);

    while ($row = $result->fetch_assoc()) {
        $row['proposal_financials_json'] = json_encode(fetch_related_rows($conn, 'proposal_financials', (int)$row['id']), JSON_UNESCAPED_UNICODE);
        $row['proposal_guests_json'] = json_encode(fetch_related_rows($conn, 'proposal_guests', (int)$row['id']), JSON_UNESCAPED_UNICODE);
        $row['proposal_travel_accomm_json'] = json_encode(fetch_related_rows($conn, 'proposal_travel_accomm', (int)$row['id']), JSON_UNESCAPED_UNICODE);
        $row['proposal_budgets_json'] = json_encode(fetch_related_rows($conn, 'proposal_budgets', (int)$row['id']), JSON_UNESCAPED_UNICODE);
        $row['proposal_sponsors_json'] = json_encode(fetch_related_rows($conn, 'proposal_sponsors', (int)$row['id']), JSON_UNESCAPED_UNICODE);
        $row['proposal_messages_json'] = json_encode(fetch_related_rows($conn, 'proposal_messages', (int)$row['id']), JSON_UNESCAPED_UNICODE);
        fputcsv($output, $row);
    }
}
$stmt->close();
fclose($output);
exit;
?>