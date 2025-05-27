<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Access Request Received</title>
</head>
<body>
    <?php
        $name = $_POST['fullName'];
        $email = $_POST['email'];
        $username = $_POST['username'];
        $password = $_POST['password'];
        $reason = $_POST['reason'];

        echo "<h2>Access Request Received</h2>";
        echo "Name: " . htmlspecialchars($name) . "<br>";
        echo "Email: " . htmlspecialchars($email) . "<br>";
        echo "Desired Username: " . htmlspecialchars($username) . "<br>";
        echo "Desired Password: " . htmlspecialchars($password) . "<br>";
        echo "Reason: " . nl2br(htmlspecialchars($reason));
    ?>
</body>
</html>

