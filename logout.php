<?php
// logout.php
require_once 'includes/config.php'; // Required for BASE_URL
session_start();      // Start the session if not already
session_unset();      // Unset all session variables
session_destroy();    // Destroy the session

// Redirect to login page after logout
header("Location: " . BASE_URL . "index.php");
exit();
?>
