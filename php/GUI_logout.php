<?php
// =========================================
// FILE: GUI_logout.php
// AUTHORS: Alan Hosseinpour / Kyle Dick
// PROJECT VI – Elevator Control System
//
// === PURPOSE ===
// Terminates the current user session, effectively logging the user out.
// Redirects the user to the login page (GUI_login.html).
// Used in conjunction with GUI_login.php and session-based access control.
// =========================================

// === 1. START SESSION ===
// Required to access and destroy session data
session_start();

// === 2. DESTROY SESSION ===
// Removes all session variables and ends the session
session_destroy();

// === 3. REDIRECT TO LOGIN PAGE ===
// After logging out, user is returned to the login screen
header("Location: ../GUI_login.html");
exit;
?>
