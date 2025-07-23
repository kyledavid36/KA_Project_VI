<?php
/**
 * ─────────────────────────────────────────────────────
 * FILE: edit_entry.php
 * AUTHORS: Alan Hosseinpour / Project VI Team
 * PURPOSE:
 *  - Admin-only page to edit a specific row in elevatorNetwork table.
 *  - Only accessible after login via session.
 *  - Used to correct entries like currentFloor/requestedFloor/otherInfo.
 * DEPENDENCIES:
 *  - Requires an `id` query string (e.g., ?id=12)
 *  - Requires valid session (user_id must be set)
 * ─────────────────────────────────────────────────────
 */

session_start();

// === 1. ENSURE USER IS AUTHENTICATED ===
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

// === 2. VALIDATE AND FETCH THE ROW ID TO EDIT ===
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id === 0) {
    header('Location: member.php'); // Fallback if no valid ID
    exit();
}

try {
    // === 3. CONNECT TO DB AND FETCH ROW DATA ===
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=elevator', 'ese_group4', 'ESEgroup4!');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->prepare("SELECT * FROM elevatorNetwork WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $entry = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$entry) {
        header('Location: member.php'); // If entry not found
        exit();
    }
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<!-- === 4. HTML FORM TO EDIT DATABASE ENTRY === -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Elevator Log Entry</title>
</head>
<body>
    <h2>📝 Modify Log Entry #<?php echo htmlspecialchars($entry['id']); ?></h2>

    <!-- Submit form to update_entry.php -->
    <form action="update_entry.php" method="post">
        <!-- Hidden ID to track which row is being updated -->
        <input type="hidden" name="id" value="<?php echo htmlspecialchars($entry['id']); ?>">

        <!-- Editable fields -->
        <label for="currentFloor">Current Floor:</label><br>
        <input type="number" id="currentFloor" name="currentFloor" value="<?php echo htmlspecialchars($entry['currentFloor']); ?>"><br><br>

        <label for="requestedFloor">Requested Floor:</label><br>
        <input type="number" id="requestedFloor" name="requestedFloor" value="<?php echo htmlspecialchars($entry['requestedFloor']); ?>"><br><br>

        <label for="otherInfo">Info / Comments:</label><br>
        <input type="text" id="otherInfo" name="otherInfo" value="<?php echo htmlspecialchars($entry['otherInfo']); ?>" size="50"><br><br>

        <!-- Form action
