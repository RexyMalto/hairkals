<?php
$password = 'password123'; // The plain-text password you want to use
$hashed_password = password_hash($password, PASSWORD_DEFAULT);
echo "Plain Text Password: " . $password . "<br>";
echo "Generated Hash: " . $hashed_password;
?>