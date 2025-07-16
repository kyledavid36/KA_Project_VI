<?php
// Start the session
session_start();

// Database connection details (REPLACE WITH YOUR ACTUAL CREDENTIALS)
$servername = "localhost";
$username_db = "your_db_username";
$password_db = "your_db_password";
$dbname = "your_database_name";

// Create connection
$conn = new mysqli($servername, $username_db, $password_db, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Prepare a SQL statement to prevent SQL injection
    // In a real application, you should HASH your passwords and use password_verify()
    $stmt = $conn->prepare("SELECT id, username, password FROM members WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        // **IMPORTANT**: In a real application, replace this with password_verify($password, $user['password'])
        // assuming passwords in your database are hashed.
        if ($password === $user['password']) { // THIS IS INSECURE FOR PRODUCTION. USE password_verify()!
            // Login successful
            $_SESSION['loggedin'] = true;
            $_SESSION['username'] = $username; // Store username in session if needed

            // Redirect to the member-only page
            header('Location: ../GUI/member.php'); // Adjust path if necessary
            exit;
        } else {
            // Incorrect password
            $_SESSION['login_error'] = "Invalid username or password.";
            header('Location: ../login.html');
            exit;
        }
    } else {
        // User not found
        $_SESSION['login_error'] = "Invalid username or password.";
        header('Location: ../login.html');
        exit;
    }

    $stmt->close();
}

$conn->close();
?>