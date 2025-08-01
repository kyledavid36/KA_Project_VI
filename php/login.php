<?php
session_start();

// Handle login POST
$loginError = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // Database connection
    try {
        $pdo = new PDO('mysql:host=127.0.0.1;dbname=elevator', 'ese_group4', 'ESEgroup4!');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        die("DB connection failed: " . $e->getMessage());
    }

    // Fetch user
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['loggedin'] = true;
        $_SESSION['username'] = $username;
        header("Location: members.php"); // Adjust path if needed
        exit;
    } else {
        $loginError = "❌ Invalid username or password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login - Project VI</title>

  <!-- Stylesheets -->
  <link rel="stylesheet" href="css/site_style.css" />
  <link rel="stylesheet" href="css/request_form_style.css" />
  <link 
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" 
    rel="stylesheet" 
    integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" 
    crossorigin="anonymous"
  />
</head>
<body>
  <div id="page" class="container">
    <main class="container">
      <h2>🔐 Login to Project VI</h2>

      <?php if ($loginError): ?>
        <div style="color: red; font-weight: bold; margin-bottom: 1rem;">
          <?= htmlspecialchars($loginError) ?>
        </div>
      <?php endif; ?>

      <form action="login.php" method="POST">
        <label for="username">Username:</label><br />
        <input type="text" id="username" name="username" required /><br /><br />

        <label for="password">Password:</label><br />
        <input type="password" id="password" name="password" required /><br /><br />

        <input type="submit" value="Login" /><br /><br />
      </form>

      <p>
        Don't have an account?<br /><br />
        <a href="request_access.html" class="button">Request Access</a>
      </p>
    </main>

    <footer class="footer mt-4">
      <p>© 2025 Alan Hosseinpourmoghadam & Kyle Dick. All rights reserved.</p>
    </footer>
  </div>

  <!-- Bootstrap JS -->
  <script 
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" 
    integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" 
    crossorigin="anonymous">
  </script>
</body>
</html>
