<?php
session_start(); // Start the session at the very beginning

// Define the path to your authorizedUsers.json file
// This assumes 'php' folder is at the same level as 'json' folder.
$jsonFile = __DIR__ . '/../json/authorizedUsers.json';

// Check if the request method is POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get username and password from the form submission
    $submittedUsername = isset($_POST['username']) ? trim($_POST['username']) : '';
    $submittedPassword = isset($_POST['password']) ? $_POST['password'] : '';

    // Basic validation to ensure fields are not empty
    if (empty($submittedUsername) || empty($submittedPassword)) {
        echo "<!DOCTYPE html>
        <html lang='en'>
        <head><meta charset='UTF-8'><title>Login Failed</title>
        <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css' rel='stylesheet'>
        <style>body { font-family: sans-serif; display: flex; justify-content: center; align-items: center; min-height: 100vh; background-color: #f8f9fa; } .card { width: 400px; } </style>
        </head>
        <body>
            <div class='card text-center shadow-lg'>
                <div class='card-header bg-danger text-white'>Authentication Failed</div>
                <div class='card-body'>
                    <p class='card-text'>Username and password cannot be empty.</p>
                    <a href='../login.html' class='btn btn-primary'>Try Again</a>
                </div>
            </div>
        </body>
        </html>";
        exit;
    }

    // Read the authorized users from the JSON file
    $authorizedUsers = [];
    if (file_exists($jsonFile) && is_readable($jsonFile)) {
        $fileContent = file_get_contents($jsonFile);
        if (!empty($fileContent)) {
            $decodedContent = json_decode($fileContent, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decodedContent)) {
                $authorizedUsers = $decodedContent;
            } else {
                error_log("Error: authorizedUsers.json contains invalid JSON or is not an array.");
            }
        }
    } else {
        error_log("Warning: authorizedUsers.json not found or not readable.");
    }

    $authenticated = false;
    foreach ($authorizedUsers as $user) {
        // IMPORTANT: In a real application, you would use password_verify($submittedPassword, $user['password'])
        // assuming passwords in JSON are hashed. For this exercise, we compare plain text.
        if (isset($user['username']) && $user['username'] === $submittedUsername && 
            isset($user['password']) && $user['password'] === $submittedPassword) {
            $authenticated = true;
            break;
        }
    }

    if ($authenticated) {
        // Set session variable to indicate user is logged in
        $_SESSION['loggedin'] = true;
        $_SESSION['username'] = $submittedUsername; // Store username in session

        // Redirect to the members-only page
        
        header("Location: members.php"); 
        exit;
    } else {
        // Credentials do not match
        echo "<!DOCTYPE html>
        <html lang='en'>
        <head><meta charset='UTF-8'><title>Login Failed</title>
        <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css' rel='stylesheet'>
        <style>body { font-family: sans-serif; display: flex; justify-content: center; align-items: center; min-height: 100vh; background-color: #f8f9fa; } .card { width: 400px; } </style>
        </head>
        <body>
            <div class='card text-center shadow-lg'>
                <div class='card-header bg-danger text-white'>Authentication Failed</div>
                <div class='card-body'>
                    <p class='card-text'>You are not authenticated. Please check your username and password.</p>
                    <a href='../login.html' class='btn btn-primary'>Try Again</a>
                </div>
            </div>
        </body>
        </html>";
        exit;
    }
} else {
    // If accessed directly without a POST request
    header("Location: ../login.html");
    exit;
}
?>
