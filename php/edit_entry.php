<?php
// FILE: edit_entry.php
// This page allows an authenticated user to edit an individual record (log entry) in the elevatorNetwork table.

session_start(); // === 1. START SESSION TO CHECK LOGIN STATE ===

if (!isset($_SESSION['user_id'])) {
    // Redirect unauthorized users to the login page
    header("Location: login.html");
    exit();
}

// === 2. GET ENTRY ID TO EDIT ===
// This ID is passed as a query string (e.g., edit_entry.php?id=5)
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// If no ID is provided or invalid, redirect to member log
if ($id === 0) {
    header('Location: member.php');
    exit();
}

try {
    // === 3. CONNECT TO DATABASE AND FETCH ENTRY ===
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=elevator', 'Alanhpm', 'Alanhpm1382');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Prepare and execute the SELECT query to get the entry with the given ID
    $stmt = $pdo->prepare("SELECT * FROM elevatorNetwork WHERE id = :id");
    $stmt->execute([':id' => $id]);

    // Fetch the result as an associative array
    $entry = $stmt->fetch(PDO::FETCH_ASSOC);

    // If no entry was found, redirect user
    if (!$entry) {
        header('Location: member.php');
        exit();
    }
} catch (PDOException $e) {
    // Stop execution and display error message if DB fails
    die("Database error: " . $e->getMessage());
}
?>

<!-- === 4. DISPLAY FORM TO EDIT LOG ENTRY === -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Modify Entry</title>
</head>
<body>
    <h2>Modify Log Entry #<?php echo htmlspecialchars($entry['id']); ?></h2>

    <!-- Form submits to update_entry.php -->
    <form action="update_entry.php" method="post">
        <!-- Hidden field to store the ID of the row being edited -->
        <input type="hidden" name="id" value="<?php echo htmlspecialchars($entry['id']); ?>">

        <!-- Input field for current floor value -->
        <label for="currentFloor">Current Floor:</label><br>
        <input type="number" id="currentFloor" name="currentFloor" value="<?php echo htmlspecialchars($entry['currentFloor']); ?>"><br><br>

        <!-- Input field for requested floor value -->
        <label for="requestedFloor">Requested Floor:</label><br>
        <input type="number" id="requestedFloor" name="requestedFloor" value="<?php echo htmlspecialchars($entry['requestedFloor']); ?>"><br><br>

        <!-- Text input for any extra info/comments -->
        <label for="otherInfo">Info:</label><br>
        <input type="text" id="otherInfo" name="otherInfo" value="<?php echo htmlspecialchars($entry['otherInfo']); ?>" size="50"><br><br>

        <!-- Submit and cancel buttons -->
        <input type="submit" value="Save Changes">
        <a href="member.php">Cancel</a>
    </form>
</body>
</html>
