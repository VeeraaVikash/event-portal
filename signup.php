<?php
session_start();
require_once 'includes/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = htmlspecialchars($_POST['full_name']);
    $email = htmlspecialchars($_POST['email']);
    $password = $_POST['password'];
    $phone_number = htmlspecialchars($_POST['phone_number'] ?? '');
    $designation = htmlspecialchars($_POST['designation']);
    $department = htmlspecialchars($_POST['department'] ?? 'Computing Technologies');
    $role = 'Convener'; // Enforced to Convener

    if (!preg_match('/@srmist\.edu\.in$/', $email)) {
        $_SESSION['error_signup'] = "Email must end with @srmist.edu.in";
        header("location: index.php?modal=signup");
        exit;
    } else {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            $_SESSION['error_signup'] = "An account with this email already exists.";
            header("location: index.php?modal=signup");
            exit;
        } else {
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            $stmt->close();
            
            $stmt = $conn->prepare("INSERT INTO users (full_name, email, password, phone_number, designation, department, role) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssss", $full_name, $email, $hashed_password, $phone_number, $designation, $department, $role);
            
            if ($stmt->execute()) {
                $_SESSION['success_signup'] = "Registration successful! You can now sign in.";
                header("location: index.php?modal=login");
                exit;
            } else {
                $_SESSION['error_signup'] = "Registration failed. Please try again.";
                header("location: index.php?modal=signup");
                exit;
            }
        }
        $stmt->close();
    }
} else {
    header("location: index.php?modal=signup");
    exit;
}
?>
