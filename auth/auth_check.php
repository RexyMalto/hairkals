<?php
// auth/auth_check.php

// Ensure session is started before accessing session variables
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Ensure BASE_URL is defined
if (!defined('BASE_URL')) {
    require_once '../includes/config.php'; // Adjust path if necessary
}

// Function to check if a user is logged in
function check_login() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: " . BASE_URL . "login.php");
        exit();
    }
}

// Function to check if a user is an administrator
function check_admin() {
    check_login(); // First, ensure they are logged in
    if ($_SESSION['role'] !== 'admin') {
        // Redirect to user dashboard if not an admin
        header("Location: " . BASE_URL . "user/dashboard.php");
        exit();
    }
}

// Function to check if a user is a regular user
function check_user() {
    check_login(); // First, ensure they are logged in
    if ($_SESSION['role'] !== 'user') {
        // Redirect to admin dashboard if not a regular user (i.e., an admin trying to access user page)
        header("Location: " . BASE_URL . "admin/dashboard.php");
        exit();
    }
}
?>
