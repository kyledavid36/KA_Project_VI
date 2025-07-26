<?php
session_start(); // Start the session

// Unset all of the session variables
$_SESSION = array();

// Destroy the session.
session_destroy();

// Redirect to the login page using an absolute path from the web root
// This assumes your project is directly under /KA_Project_VI/SW_Midterm/
header("Location: /KA_Project_VI/SW_Midterm/login.html");
exit;
?>
