<?php
// FILE: edit_entry.php

session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id === 0) {
    header('Location: member.php');
    exit();
}

try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=elevator', 'Alanhpm', 'Alanhpm1382');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $stmt = $pdo->prepare("SELECT * FROM elevatorNetwork WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $entry = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$entry) {
        // Entry not found, redirect
        header('Location: member.php');
        exit();
    }
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Modify Entry</title>
</head>
<body>
    <h2>Modify Log Entry #<?php echo htmlspecialchars($entry['id']); ?></h2>
    <form action="update_entry.php" method="post">
        <input type="hidden" name="id" value="<?php echo htmlspecialchars($entry['id']); ?>">

        <label for="currentFloor">Current Floor:</label><br>
        <input type="number" id="currentFloor" name="currentFloor" value="<?php echo htmlspecialchars($entry['currentFloor']); ?>"><br><br>

        <label for="requestedFloor">Requested Floor:</label><br>
        <input type="number" id="requestedFloor" name="requestedFloor" value="<?php echo htmlspecialchars($entry['requestedFloor']); ?>"><br><br>

        <label for="otherInfo">Info:</label><br>
        <input type="text" id="otherInfo" name="otherInfo" value="<?php echo htmlspecialchars($entry['otherInfo']); ?>" size="50"><br><br>

        <input type="submit" value="Save Changes">
        <a href="member.php">Cancel</a>
    </form>
</body>
</html>