<?php
// This script is the landing page for your elevator control panel.
// It loads the most recent floor from the database and embeds the GUI interface.

// === 1. CONNECT TO DATABASE AND FETCH CURRENT FLOOR ===
try {
    // Create a new PDO connection
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=elevator', 'Alanhpm', 'Alanhpm1382');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Prepare SQL query to get the most recent floor log for the elevator controller (nodeID = 0x0101)
    $stmt = $pdo->prepare("
        SELECT currentFloor 
        FROM elevatorNetwork 
        WHERE nodeID = :id 
        ORDER BY id DESC 
        LIMIT 1
    ");
    $stmt->execute([':id' => 0x0101]);
    $row = $stmt->fetch();

    // If a row was returned, store the floor number. Otherwise, fallback to 'N/A'.
    $currentFloor = $row ? $row['currentFloor'] : 'N/A';

} catch (PDOException $e) {
    // If database connection or query fails, fallback value
    $currentFloor = 'Error';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Project VI - Elevator Control Panel</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="stylesheet" href="css/site_style.css" />
</head>
<body>
    <!-- === 2. EMBEDDED FRONTEND GUI === -->
    <!-- Loads the HTML GUI (Steam-themed) as a fullscreen iframe -->
    <iframe src="../GUI/alsaSteamGUI.html" style="width:100%; height:100vh; border:none;"></iframe>
</body>
</html>
