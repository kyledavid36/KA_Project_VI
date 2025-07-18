<?php
/**
 * PHP Login Script for Project VI
 * Handles user authentication by verifying credentials against the 'elevator' database.
 * Redirects to 'GUI/member.php' on success, or back to 'login.html' on failure.
 */

// Start the PHP session to manage user login state across pages.
session_start();

// Database connection parameters.
// Update these if your database server, username, or password are different.
$servername = "localhost"; // Or your database server's IP address (e.g., '192.168.1.100').
$username_db = "Alanhpm";
$password_db = "Alanhpm1382";
$dbname = "elevator";      // The database containing the 'users' table.

// Establish database connection.
$conn = new mysqli($servername, $username_db, $password_db, $dbname);

// Check for database connection errors.
if ($conn->connect_error) {
    error_log("Database connection failed: " . $conn->connect_error); // Log error for debugging.
    die("Connection failed. Please try again later."); // Generic message to user.
}

// Process login attempt if the form was submitted via POST.
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get username and password from the submitted form.
    // Use null coalescing to prevent "undefined index" notices if fields are empty.
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Prepare a SQL statement to query the 'users' table.
    // This uses a prepared statement to prevent SQL injection vulnerabilities.
    $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE username = ?");
    
    // Check if the statement preparation was successful.
    if ($stmt === false) {
        error_log("Failed to prepare statement: " . $conn->error);
        $_SESSION['login_error'] = "An internal error occurred. Please try again.";
        header('Location: ../login.html');
        exit;
    }

    // Bind the username parameter to the prepared statement ( 's' denotes string type).
    $stmt->bind_param("s", $username);
    
    // Execute the query.
    $stmt->execute();
    
    // Get the result set.
    $result = $stmt->get_result();

    // Check if a user with the given username was found.
    if ($result->num_rows == 1) {
        // Fetch the user's data, including their hashed password.
        $user = $result->fetch_assoc();
        
        // Verify the submitted password against the stored hashed password.
        // `password_verify()` is the secure way to check hashed passwords.
        if (password_verify($password, $user['password'])) {
            // Login successful: Set session variables and redirect to the member area.
            $_SESSION['loggedin'] = true;
            $_SESSION['username'] = $username;
            header('Location: ../GUI/member.php'); // Path to the member-only page.
            exit; 
        } else {
            // Password incorrect: Set error message and redirect to login page.
            $_SESSION['login_error'] = "Invalid username or password.";
            header('Location: ../login.html');
            exit;
        }
    } else {
        // User not found: Set error message and redirect to login page.
        // Generic message for security (don't reveal if username exists).
        $_SESSION['login_error'] = "Invalid username or password.";
        header('Location: ../login.html');
        exit;
    }

    // Close the prepared statement.
    $stmt->close();
}

// Close the database connection.
$conn->close();

?>