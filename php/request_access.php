<?php
/**
 * ─────────────────────────────────────────────────────────────────────
 * FILE: registerAccess.php
 * AUTHORS: Alan Hpm, Kyle D
 * PURPOSE:
 *  - Handles new user registration from the GUI access form.
 *  - Validates inputs and securely stores user data into the `users` table.
 *  - Hashes passwords using bcrypt for secure authentication.
 * DEPENDENCIES:
 *  - HTML registration form (e.g., accessRequest.html)
 *  - MariaDB `elevator` database with `users` table
 *  - Synced with: GUI_login.php for login functionality
 * ─────────────────────────────────────────────────────────────────────
 */


// ─────────────────────────────────────
// 1. CONNECT TO DATABASE
// ─────────────────────────────────────
try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=elevator', 'ese_group4', 'ESEgroup4!');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("DB Connection failed: " . $e->getMessage());
}


// ─────────────────────────────────────
// 2. COLLECT FORM INPUTS
// Uses null coalescing operator to avoid undefined index errors
// ─────────────────────────────────────
$fullName = $_POST['fullName'] ?? '';
$email    = $_POST['email'] ?? '';
$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';
$reason   = $_POST['reason'] ?? '';


// ─────────────────────────────────────
// 3. VALIDATE FORM INPUT
// Limits reason text to 180 characters
// ─────────────────────────────────────
if (strlen($reason) > 180) {
    echo "<h2>Error: Reason cannot exceed 180 characters.</h2>";
    exit;
}


// ─────────────────────────────────────
// 4. HASH PASSWORD
// Uses bcrypt for strong one-way encryption
// ─────────────────────────────────────
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);


// ─────────────────────────────────────
// 5. INSERT USER DATA INTO DATABASE
// Fields: full_name, email, username, password (hashed)
// ─────────────────────────────────────
try {
    $stmt = $pdo->prepare("INSERT INTO users (full_name, email, username, password) 
                           VALUES (:full_name, :email, :username, :password)");

    $stmt->execute([
        ':full_name' => $fullName,
        ':email'     => $email,
        ':username'  => $username,
        ':password'  => $hashedPassword
    ]);


    // ─────────────────────────────────────
    // 6. CONFIRMATION MESSAGE TO USER
    // ─────────────────────────────────────
    echo "<h2>✅ Access Request Submitted</h2>";
    echo "Username <strong>" . htmlspecialchars($username) . "</strong> registered successfully.";

} catch (PDOException $e) {

    // ─────────────────────────────────────
    // 7. HANDLE ERRORS (e.g., duplicates)
    // MySQL error 1062 = duplicate entry (email or username)
    // ─────────────────────────────────────
    echo "<h2>❌ Registration Failed</h2>";

    if ($e->errorInfo[1] == 1062) {
        echo "⚠️ Username or Email already exists.";
    } else {
        echo "Database error: " . $e->getMessage();
    }
}
?>
