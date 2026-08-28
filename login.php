<?php
session_start();
require_once 'includes/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = htmlspecialchars($_POST['email']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, full_name, password, designation, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows == 1) {
        $stmt->bind_result($id, $full_name, $hashed_password, $designation, $role);
        $stmt->fetch();
        
        if (password_verify($password, $hashed_password)) {
            $_SESSION["loggedin"] = true;
            $_SESSION["id"] = $id;
            $_SESSION["email"] = $email;
            $_SESSION["full_name"] = $full_name;
            $_SESSION["role"] = $role;
            
            if (strtoupper($role) === 'HOD') {
                header("location: dashboard_hod.php");
            } elseif (strtoupper($role) === 'COORDINATOR') {
                header("location: dashboard_coordinator.php");
            } else {
                header("location: dashboard.php");
            }
            exit;
        } else {
            $_SESSION['error_login'] = "Invalid email or password.";
            header("location: index.php?modal=login");
            exit;
        }
    } else {
        $_SESSION['error_login'] = "Invalid email or password.";
        header("location: index.php?modal=login");
        exit;
    }
    $stmt->close();
} else {
    header("location: index.php?modal=login");
    exit;
}
?>
