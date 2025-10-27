<?php
session_start();

// Check if user is already logged in
if (isset($_SESSION['user_email'])) {
    // Redirect to Laundry dashboard if logged in
    header('Location: Laundry/index.php');
    exit;
} else {
    // Redirect to login if not logged in
    header('Location: auth/login.php');
    exit;
}
?>