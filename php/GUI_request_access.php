<?php
$success = '';
$error = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $full_name = $_POST['full_name'] ?? '';
    $email     = $_POST['email'] ?? '';
    $username  = $_POST['username'] ?? '';
    $password  = $_POST['password'] ?? '';

    if (!$full_name || !$email || !$username || !$password) {
        $error = "❌ All fields are required.";
    } else {
        try {
            $pdo = new PDO("mysql:host=127.0.0.1;dbname=elevator", "Alanhpm", "Alanhpm1382!");
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $check = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $check->execute([$username, $email]);
            if ($check->rowCount() > 0) {
                $error = "⚠️ Username or email already exists.";
            } else {
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (full_name, email, username, password) VALUES (?, ?, ?, ?)");
                $stmt->execute([$full_name, $email, $username, $hashed]);
                $success = "✅ Account created successfully. You may now login.";
            }
        } catch (PDOException $e) {
            $error = "❌ Database error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Request Access</title>
  <link rel="stylesheet" href="../css/bootstrap.min.css" />
  <style>
    body {
      margin: 0;
      padding: 0;
      height: 100vh;
      background-image: url("../Images/login_elevator.png");
      background-size: cover;
      background-position: center;
      background-color: #000;
      font-family: 'Segoe UI', sans-serif;
      display: flex;
      justify-content: center;
      align-items: center;
      flex-direction: column;
    }

    .login-box {
      width: 100%;
      max-width: 400px;
      padding: 30px;
      background-color: rgba(0, 0, 0, 0.8);
      border-radius: 15px;
      color: #fff;
      box-shadow: 0 0 30px rgba(0,255,255,0.3);
      text-align: center;
    }

    .login-box h2 {
      color: #00ffff;
      margin-bottom: 20px;
    }

    .login-box input[type="text"],
    .login-box input[type="email"],
    .login-box input[type="password"] {
      width: 90%;
      max-width: 320px;
      padding: 12px;
      margin: 10px auto;
      display: block;
      border: none;
      border-radius: 5px;
      background-color: #222;
      color: #fff;
    }

    .login-box input[type="submit"] {
      width: 90%;
      max-width: 320px;
      margin: 10px auto;
      display: block;
      background-color: #007bff;
      color: white;
      padding: 12px;
      border: none;
      border-radius: 5px;
      font-weight: bold;
      cursor: pointer;
      transition: background-color 0.3s ease;

    }

    .login-box input[type="submit"]:hover {
      background-color: #0056b3;
    }

    .error-msg, .success-msg {
      margin-top: 15px;
      font-size: 14px;
    }

    .error-msg {
      color: #ff5252;
    }

    .success-msg {
      color: #00ff88;
    }

    footer {
      margin-top: 20px;
      color: #ccc;
      font-size: 13px;
    }

    a {
      color: #00ffff;
      text-decoration: underline;
    }
  </style>
</head>
<body>

  <div class="login-box">
    <h2>🪪 Request Access</h2>
    <form method="POST">
      <input type="text" name="full_name" placeholder="Full Name" required />
      <input type="email" name="email" placeholder="Email Address" required />
      <input type="text" name="username" placeholder="Username" required />
      <input type="password" name="password" placeholder="Password" required />
      <input type="submit" value="Submit Request" />
    </form>

    <p style="text-align:center; margin-top: 20px;">
      🔙 Already registered? <a href="GUI_login.php">Back to Login</a>
    </p>

    <?php if ($error): ?>
      <div class="error-msg"><?= htmlspecialchars($error) ?></div>
    <?php elseif ($success): ?>
      <div class="success-msg"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
  </div>

  <footer>
    <p>© 2025 Project VI – Elevator System</p>
  </footer>

</body>
</html>
