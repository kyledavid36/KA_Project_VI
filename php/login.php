<?php
// === PURPOSE ===
// Authenticates a user from the login form using credentials stored in the database.

// === 1. START SESSION TO TRACK USER LOGIN ===
session_start();

// === 2. DATABASE CONNECTION INFO ===
// NOTE: Replace these placeholder values with your actual credentials.
$servername = "127.0.0.1";
$username_db = "ese_group4";      // username on Mysql
$password_db = "ESEgroup4!";      // Password on MySQL username
$dbname = "elevator";         // 
// === 3. CONNECT TO DATABASE ===
$conn = new mysqli($servername, $username_db, $password_db, $dbname);

// Check for connection errors
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// === 4. HANDLE POST REQUEST FROM LOGIN FORM ===
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // === 5. USE PREPARED STATEMENT TO AVOID SQL INJECTION ===
    $stmt = $conn->prepare("SELECT id, username, password FROM members WHERE username = ?");
    $stmt->bind_param("s", $username); // Bind user input
    $stmt->execute();
    $result = $stmt->get_result();

    // === 6. CHECK IF USER EXISTS ===
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();

        // === 7. VERIFY PASSWORD ===
        // ❌ CURRENT METHOD IS INSECURE: This compares plain text passwords.
        // ✅ RECOMMENDED: Use password_verify() instead if passwords are hashed
        if (password_verify($password, $user['password'])) { // <-- Insecure for production

            // === 8. SET SESSION VARIABLES ON SUCCESSFUL LOGIN ===
            $_SESSION['loggedin'] = true;
            $_SESSION['username'] = $username;

            // Redirect user to member page
            header('Location: ../GUI/member.php'); // Adjust path as needed
            exit;

        } else {
            // Password is incorrect
            $_SESSION['login_error'] = "Invalid username or password.";
            header('Location: ../login.html');
            exit;
        }

    } else {
        // No user found
        $_SESSION['login_error'] = "Invalid username or password.";
        header('Location: ../login.html');
        exit;
    }

    $stmt->close(); // Close statement
}

$conn->close(); // Close DB connection
?>
