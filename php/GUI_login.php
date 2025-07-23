<?php
/**
 * ──────────────────────────────────────────────────────────────
 * FILE: GUI_login.php
 * AUTHORS: Alan Hosseinpour, Kyle Dick
 * PURPOSE:
 *  - Provides the login page for the elevator GUI.
 *  - Validates credentials against `users` table in the elevator database.
 *  - Redirects logged-in users to alsaSteamGUI.php.
 * DEPENDENCIES:
 *  - Database: elevator
 *  - Table: users (fields: username, password, etc.)
 *  - Session handling and secure login validation via PHP
 *  - Redirects to: GUI_request_access.php if user not registered
 *  - Redirects to: alsaSteamGUI.php upon success
 * ──────────────────────────────────────────────────────────────
 */

// ─────────────────────────────────────────────
// 1. START SESSION TO TRACK USER LOGIN STATE
// ─────────────────────────────────────────────
session_start();


// ─────────────────────────────────────────────
// 2. CONNECT TO DATABASE
// ─────────────────────────────────────────────
$pdo = new PDO('mysql:host=127.0.0.1;dbname=elevator', 'ese_group4', 'ESEgroup4!');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


// ─────────────────────────────────────────────
// 3. PROCESS LOGIN FORM SUBMISSION
// ─────────────────────────────────────────────
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Get user input (sanitized fallback)
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Query for matching username
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Verify password against stored hash
    if ($user && password_verify($password, $user['password'])) {
        // Credentials match — start session and redirect
        $_SESSION['loggedin'] = true;
        $_SESSION['username'] = $username;
        header("Location: ../GUI/alsaSteamGUI.php");
        exit;
    } else {
        // Invalid credentials — store error in session and reload
        $_SESSION['login_error'] = "Invalid username or password.";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
}

// ─────────────────────────────────────────────
// 4. RETRIEVE AND CLEAR ERROR MESSAGE
// ─────────────────────────────────────────────
$loginError = $_SESSION['login_error'] ?? '';
unset($_SESSION['login_error']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Elevator Access Login</title>
  <link rel="stylesheet" href="../css/bootstrap.min.css" />
  <style>
    /* 🖼️ Fullscreen dark elevator-themed background */
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

    /* 🔳 Styled login box with neon blue glow */
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

    /* 🔐 Input fields styling */
    .login-box input[type="text"],
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

    /* 🔘 Submit button with hover effect */
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

    /* ⚠️ Error message styling */
    .error-msg {
      color: #ff5252;
      margin-top: 10px;
      font-size: 14px;
    }

    footer {
      margin-top: 20px;
      color: #ccc;
      font-size: 13px;
    }
  </style>
</head>

<body>

  <!-- 🔐 Login Form UI -->
  <div class="login-box">
    <h2>🔐 Elevator Access</h2>
    <form method="POST" action="">
      <input type="text" name="username" placeholder="Username" required />
      <input type="password" name="password" placeholder="Password" required />
      <input type="submit" value="Login" />
    </form>

    <!-- ↩️ Link to Access Request page -->
    <p style="text-align: center; margin-top: 20px;">
        🚪 Don’t have access yet? 
        <a href="GUI_request_access.php" style="color:#00ffff; text-decoration:underline;">
          Request Access
        </a>
    </p>

    <!-- 🚨 Display error message if login failed -->
    <?php if ($loginError): ?>
      <div class="error-msg"><?= htmlspecialchars($loginError) ?></div>
    <?php endif; ?>
  </div>

  <footer>
    <p>© 2025 Alan Hosseinpour / Kyle Dick – Project VI Elevator System</p>
  </footer>
</body>
</html>
