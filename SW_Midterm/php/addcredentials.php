<?php

// Define the path to your JSON file
// This assumes 'php' folder is at the same level as 'json' folder.
// So, from 'php/', go up one level (../), then into 'json/', then find 'authorizedUsers.json'.
$jsonFile = __DIR__ . '/../json/authorizedUsers.json';

// Ensure the json directory exists
$jsonDirectory = dirname($jsonFile);
if (!is_dir($jsonDirectory)) {
    mkdir($jsonDirectory, 0755, true); // Create directory with read/write permissions
}

// 1. Read all of the content from the JSON file
// Initialize an empty array to hold the credentials
$credentials = [];

// Check if the JSON file exists and is readable
if (file_exists($jsonFile) && is_readable($jsonFile)) {
    $fileContent = file_get_contents($jsonFile);

    // 2. Decode it into a temporary array
    // Check if the file content is not empty before decoding
    if (!empty($fileContent)) {
        $decodedContent = json_decode($fileContent, true); // true for associative array

        // Check if decoding was successful and it's an array
        if (json_last_error() === JSON_ERROR_NONE && is_array($decodedContent)) {
            $credentials = $decodedContent;
        } else {
            // Log error if JSON is invalid but file exists
            error_log("Error: authorizedUsers.json contains invalid JSON or is not an array. Starting with empty credentials. JSON Error: " . json_last_error_msg());
            // In a production environment, you might want more robust error handling
        }
    }
} else {
    // If file doesn't exist, it will be created when writing.
    // If it exists but is not readable, it's a permissions issue.
    error_log("Warning: authorizedUsers.json not found or not readable. A new file will be created.");
}


// Check if form data has been submitted via POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect the new user credentials from the POST request
    // Basic sanitization: Use htmlspecialchars to prevent XSS in display if ever displayed directly
    // For password, you should HASH it before storing in a real application!
    $username = isset($_POST['username']) ? htmlspecialchars(trim($_POST['username'])) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : ''; // Password should be hashed, not stored plain

    // Validate input (though client-side JS already does some, server-side is essential)
    if (empty($username) || strlen($username) < 7) {
        echo "<!DOCTYPE html>
        <html lang='en'>
        <head><meta charset='UTF-8'><title>Error</title>
        <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css' rel='stylesheet'>
        <style>body { font-family: sans-serif; display: flex; justify-content: center; align-items: center; min-height: 100vh; background-color: #f8f9fa; } .card { width: 400px; } </style>
        </head>
        <body>
            <div class='card text-center shadow-lg'>
                <div class='card-header bg-danger text-white'>Error</div>
                <div class='card-body'>
                    <p class='card-text'>Username is invalid or less than 7 characters.</p>
                    <a href='../signup.html' class='btn btn-primary'>Go Back to Sign Up</a>
                    <a href='../login.html' class='btn btn-secondary ms-2'>Go to Login</a>
                </div>
            </div>
        </body>
        </html>";
        exit;
    }

    // Hash the password for security before storing
    // IMPORTANT: For a real application, ALWAYS hash passwords.
    // E.g., $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    // For this exercise, we'll store it as is, but be aware this is insecure for production.
    $hashedPassword = $password; // Placeholder: In production, use password_hash()

    // Additional password validation (matching JS requirements)
    if (strlen($password) < 7 || !preg_match('/[A-Z]/', $password) || !preg_match('/\d/', $password)) {
        echo "<!DOCTYPE html>
        <html lang='en'>
        <head><meta charset='UTF-8'><title>Error</title>
        <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css' rel='stylesheet'>
        <style>body { font-family: sans-serif; display: flex; justify-content: center; align-items: center; min-height: 100vh; background-color: #f8f9fa; } .card { width: 400px; } </style>
        </head>
        <body>
            <div class='card text-center shadow-lg'>
                <div class='card-header bg-danger text-white'>Error</div>
                <div class='card-body'>
                    <p class='card-text'>Password must be at least 7 characters long, contain one uppercase letter, and one number.</p>
                    <a href='../signup.html' class='btn btn-primary'>Go Back to Sign Up</a>
                    <a href='../login.html' class='btn btn-secondary ms-2'>Go to Login</a>
                </div>
            </div>
        </body>
        </html>";
        exit;
    }


    // 3. The new user credentials can be stored in a separate array
    $newUserCredentials = [
        'username' => $username,
        'password' => $hashedPassword // Store the hashed password
    ];

    // Check if username already exists to prevent duplicates (optional, but good practice)
    $usernameExists = false;
    foreach ($credentials as $user) {
        if (isset($user['username']) && $user['username'] === $username) {
            $usernameExists = true;
            break;
        }
    }

    if ($usernameExists) {
        echo "<!DOCTYPE html>
        <html lang='en'>
        <head><meta charset='UTF-8'><title>Error</title>
        <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css' rel='stylesheet'>
        <style>body { font-family: sans-serif; display: flex; justify-content: center; align-items: center; min-height: 100vh; background-color: #f8f9fa; } .card { width: 400px; } </style>
        </head>
        <body>
            <div class='card text-center shadow-lg'>
                <div class='card-header bg-warning text-dark'>Duplicate Username</div>
                <div class='card-body'>
                    <p class='card-text'>Error: Username already exists. Please choose a different username.</p>
                    <a href='../signup.html' class='btn btn-primary'>Go Back to Sign Up</a>
                    <a href='../login.html' class='btn btn-secondary ms-2'>Go to Login</a>
                </div>
            </div>
        </body>
        </html>";
        exit;
    }

    // 4. Add the new user's credentials to the end of the temporary array
    $credentials[] = $newUserCredentials;

    // 5. Encode the contents of the temporary array back to JSON
    $jsonEncodedCredentials = json_encode($credentials, JSON_PRETTY_PRINT);

    // 6. Write the contents of the temporary array back to the file (overwriting the original)
    // Check if the file is writable
    if (is_writable($jsonFile) || !file_exists($jsonFile)) {
        if (file_put_contents($jsonFile, $jsonEncodedCredentials) !== false) {
            // SUCCESS MESSAGE WITH SIGN UP AND LOGIN LINKS
            echo "<!DOCTYPE html>
            <html lang='en'>
            <head><meta charset='UTF-8'><title>Success</title>
            <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css' rel='stylesheet'>
            <style>body { font-family: sans-serif; display: flex; justify-content: center; align-items: center; min-height: 100vh; background-color: #f8f9fa; } .card { width: 400px; } </style>
            </head>
            <body>
                <div class='card text-center shadow-lg'>
                    <div class='card-header bg-success text-white'>Success!</div>
                    <div class='card-body'>
                        <p class='card-text'>Credentials added successfully! You can now access the JSON file to verify.</p>
                        <a href='../signup.html' class='btn btn-primary mt-3 me-2'>Sign Up Another Account</a>
                        <a href='../login.html' class='btn btn-secondary mt-3'>Login Now</a>
                    </div>
                </div>
            </body>
            </html>";
            exit;
        } else {
            echo "<!DOCTYPE html>
            <html lang='en'>
            <head><meta charset='UTF-8'><title>File Write Error</title>
            <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css' rel='stylesheet'>
            <style>body { font-family: sans-serif; display: flex; justify-content: center; align-items: center; min-height: 100vh; background-color: #f8f9fa; } .card { width: 400px; } </style>
            </head>
            <body>
                <div class='card text-center shadow-lg'>
                    <div class='card-header bg-danger text-white'>Error</div>
                    <div class='card-body'>
                        <p class='card-text'>Error: Could not write to credentials file. Check file permissions.</p>
                        <a href='../signup.html' class='btn btn-primary mt-3'>Go Back to Sign Up</a>
                        <a href='../login.html' class='btn btn-secondary ms-2'>Go to Login</a>
                    </div>
                </div>
            </body>
            </html>";
            error_log("File write error: Could not write to " . $jsonFile);
            exit;
        }
    } else {
        echo "<!DOCTYPE html>
        <html lang='en'>
        <head><meta charset='UTF-8'><title>Permission Error</title>
        <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css' rel='stylesheet'>
        <style>body { font-family: sans-serif; display: flex; justify-content: center; align-items: center; min-height: 100vh; background-color: #f8f9fa; } .card { width: 400px; } </style>
        </head>
        <body>
            <div class='card text-center shadow-lg'>
                <div class='card-header bg-danger text-white'>Error</div>
                <div class='card-body'>
                    <p class='card-text'>Error: Credentials file is not writable. Check file permissions (e.g., chmod 664 for file, 775 for directory).</p>
                    <a href='../signup.html' class='btn btn-primary mt-3'>Go Back to Sign Up</a>
                    <a href='../login.html' class='btn btn-secondary ms-2'>Go to Login</a>
                </div>
            </div>
        </body>
        </html>";
        error_log("File permissions error: " . $jsonFile . " is not writable.");
        exit;
    }

} else {
    // If accessed directly without a POST request
    echo "<!DOCTYPE html>
    <html lang='en'>
    <head><meta charset='UTF-8'><title>Access Denied</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css' rel='stylesheet'>
    <style>body { font-family: sans-serif; display: flex; justify-content: center; align-items: center; min-height: 100vh; background-color: #f8f9fa; } .card { width: 400px; } </style>
    </head>
    <body>
        <div class='card text-center shadow-lg'>
            <div class='card-header bg-secondary text-white'>Direct Access Denied</div>
            <div class='card-body'>
                <p class='card-text'>This script only accepts POST requests from the signup form.</p>
                <a href='../signup.html' class='btn btn-primary mt-3'>Go to Sign Up Page</a>
                <a href='../login.html' class='btn btn-secondary ms-2'>Go to Login Page</a>
            </div>
        </div>
    </body>
    </html>";
    exit;
}

?>
