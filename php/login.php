<?php
$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

echo "<h2>Login Info Received</h2>";
echo "Username: " . htmlspecialchars($username) . "<br>";
echo "Password: " . htmlspecialchars($password);
?>

