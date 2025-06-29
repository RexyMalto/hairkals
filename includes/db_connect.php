<?php
// includes/db_connect.php

require_once 'config.php'; // Include the new config file

$servername = DB_SERVER;
$username = DB_USERNAME;
$password = DB_PASSWORD;
$dbname = DB_NAME;

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
// For debugging, uncomment the line below to confirm connection:
// echo "Connected successfully";
?>
