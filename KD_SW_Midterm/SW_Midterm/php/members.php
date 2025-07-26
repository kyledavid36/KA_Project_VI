<?php
session_start(); // Start the session

// Check if the user is logged in. If not, redirect to login page.
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../login.html"); // Redirect to your login page (relative to php/ folder)
    exit;
}

// User is logged in, display members-only content
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Members Area</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        xintegrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous" />
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            text-align: center;
            /* Added background image for members.php */
            background-image: url('../images/galaxy.jpeg'); /* Assumes galaxy.jpeg is in the images folder relative to php/ folder */
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
        }
        .container {
            background-color: #ffffff;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            max-width: 600px;
        }
        h1 {
            color: #007bff;
            margin-bottom: 25px;
        }
        .logout-link {
            margin-top: 30px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Welcome to the Members Only Area!</h1>
        <p class="lead">You have successfully logged in.</p>
        <?php if (isset($_SESSION['username'])): ?>
            <p>Hello, <?php echo htmlspecialchars($_SESSION['username']); ?>!</p>
        <?php endif; ?>
        <div class="logout-link">
            <!-- Corrected logout link to use absolute path from web root -->
            <a href="/KA_Project_VI/SW_Midterm/php/logout.php" class="btn btn-danger btn-lg">Logout</a>
        </div>
    </div>
</body>
</html>
