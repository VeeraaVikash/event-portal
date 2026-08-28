<?php
session_start();
require_once 'includes/db.php';

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || strtoupper($_SESSION["role"]) !== 'COORDINATOR'){
    header("location: index.php?modal=login");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
    $file = $_FILES['csv_file']['tmp_name'];
    if (is_uploaded_file($file)) {
        $handle = fopen($file, 'r');
        $header = fgetcsv($handle); // Skip header

        // Clear existing preloaded events if needed? Assumed appending for now. 
        // $conn->query('TRUNCATE TABLE preloaded_events');

        $stmt = $conn->prepare('INSERT INTO preloaded_events (sl_no, event_date, event_month, activity, budget, university_contribution, convener) VALUES (?, ?, ?, ?, ?, ?, ?)');
        
        while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
            if(count($data) >= 7) {
                $stmt->bind_param('sssssss', $data[0], $data[1], $data[2], $data[3], $data[4], $data[5], $data[6]);
                $stmt->execute();
            }
        }
        fclose($handle);
        $stmt->close();
    }
}
header('location: dashboard_coordinator.php?success=1');
exit;
?>
