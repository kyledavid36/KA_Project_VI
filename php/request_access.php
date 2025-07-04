<?php
// Database connection
try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=elevator', 'Alanhpm', 'Alanhpm1382');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("DB Connection failed: " . $e->getMessage());
}

// Get form inputs
$fullName = $_POST['fullName'] ?? '';
$email = $_POST['email'] ?? '';
$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';
$reason = $_POST['reason'] ?? '';

// Validation
if (strlen($reason) > 180) {
    echo "<h2>Error: Reason cannot exceed 180 characters.</h2>";
    exit;
}

// Hash password
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

try {
    $stmt = $pdo->prepare("INSERT INTO users (full_name, email, username, password) 
                           VALUES (:full_name, :email, :username, :password)");
    $stmt->execute([
        ':full_name' => $fullName,
        ':email' => $email,
        ':username' => $username,
        ':password' => $hashedPassword
    ]);

    echo "<h2>✅ Access Request Submitted</h2>";
    echo "Username <strong>" . htmlspecialchars($username) . "</strong> registered successfully.";

} catch (PDOException $e) {
    echo "<h2>❌ Registration Failed</h2>";
    if ($e->errorInfo[1] == 1062) {
        echo "⚠️ Username or Email already exists.";
    } else {
        echo "Database error: " . $e->getMessage();
    }
}
?>