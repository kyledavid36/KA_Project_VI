<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=elevator', 'Alanhpm', 'Alanhpm1382');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$username = $_POST['username'];
$password = $_POST['password'];

$stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username");
$stmt->execute([':username' => $username]);
$user = $stmt->fetch();

if ($user && password_verify($password, $user['password'])) {
    echo "<h2>✅ Welcome back, " . htmlspecialchars($user['fullName']) . "!</h2>";
    echo "<p>Login successful.</p>";
} else {
    echo "<h2>❌ Invalid login</h2><p>Username or password is incorrect.</p>";
}
?>