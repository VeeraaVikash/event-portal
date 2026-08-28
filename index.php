<?php
session_start();

// Handle modal triggers from URL
if (isset($_GET['modal'])) {
    $active_modal = htmlspecialchars($_GET['modal']);
} else {
    $active_modal = null;
}

require_once 'views/index.view.php';

// Clear flash messages after view is rendered
if(isset($_SESSION['error_login'])) unset($_SESSION['error_login']);
if(isset($_SESSION['error_signup'])) unset($_SESSION['error_signup']);
if(isset($_SESSION['success_signup'])) unset($_SESSION['success_signup']);
?>
