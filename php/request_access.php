<?php
// === PURPOSE ===
// Handles new access (user registration) form submissions.
// Validates the input, hashes the password, and stores user info in the 'users' table.

// === 1. CONNECT TO DATABASE ===
try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=elevator', 'Alanhpm', 'Alanhpm1382');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("DB Connection failed: " . $e->getMessage());
}

// === 2. COLLECT FORM INPUTS ===
// Use null coalescing operator to provide default empty strings
$fullName = $_POST['fullName'] ?? '';
$email = $_POST['email'] ?? '';
$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';
$reason = $_POST['reason'] ?? '';

// === 3. VALIDATION RULES ===
// Only one validation currently — limits reason to 180 chars
if (strlen($reason) > 180) {
    echo "<h2>Error: Reason cannot exceed 180 characters.</h2>";
    exit;
}

// === 4. HASH THE PASSWORD ===
// Securely hash the password using bcrypt algorithm before storing it in DB
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// === 5. INSERT USER DATA INTO DATABASE ===
try {
    $stmt = $pdo->prepare("INSERT INTO users (full_name, email, username, password) 
                           VALUES (:full_name, :email, :username, :password)");

    $stmt->execute([
        ':full_name' => $fullName,
        ':email' => $email,
        ':username' => $username,
        ':password' => $hashedPassword
    ]);

    // === 6. CONFIRMATION OUTPUT ===
    echo "<h2>✅ Access Request Submitted</h2>";
    echo "Username <strong>" . htmlspecialchars($username) . "</strong> registered successfully.";

} catch (PDOException $e) {
    // === 7. HANDLE DUPLICATE ENTRY OR OTHER ERRORS ===
    echo "<h2>❌ Registration Failed</h2>";

    // If error code is 1062, it's likely a unique constraint (duplicate email or username)
    if ($e->errorInfo[1] == 1062) {
        echo "⚠️ Username or Email already exists.";
    } else {
        echo "Database error: " . $e->getMessage();
    }
}
?>
