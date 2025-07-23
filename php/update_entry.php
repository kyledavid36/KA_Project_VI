<?php
// This script allows an authenticated user to update an existing entry in the elevatorNetwork table.
// Typically used for correcting log entries or diagnostics.

// === 1. START SESSION AND VERIFY AUTHENTICATION ===
// Start PHP session to track user login state.
session_start();

// Check if the user is logged in. If not, redirect to login page.
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

// === 2. HANDLE FORM SUBMISSION (ONLY ON POST REQUEST) ===
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Safely extract data from POST array
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $currentFloor = isset($_POST['currentFloor']) ? (int)$_POST['currentFloor'] : 1;
    $requestedFloor = isset($_POST['requestedFloor']) ? (int)$_POST['requestedFloor'] : 1;
    $otherInfo = isset($_POST['otherInfo']) ? $_POST['otherInfo'] : '';

    // Only continue if a valid row ID was submitted
    if ($id > 0) {
        try {
            // === 3. CONNECT TO DATABASE ===
            $pdo = new PDO('mysql:host=127.0.0.1;dbname=elevator', 'ese_group4', 'ESEgroup4!');
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // === 4. PREPARE AND EXECUTE UPDATE STATEMENT ===
            // Update the entry with new floor values and info for the given ID
            $sql = "UPDATE elevatorNetwork 
                    SET currentFloor = :currentFloor, 
                        requestedFloor = :requestedFloor, 
                        otherInfo = :otherInfo 
                    WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':currentFloor' => $currentFloor,
                ':requestedFloor' => $requestedFloor,
                ':otherInfo' => $otherInfo,
                ':id' => $id
            ]);
        } catch (PDOException $e) {
            // Print error and stop if anything goes wrong
            die("Error updating record: " . $e->getMessage());
        }
    }
}

// === 5. REDIRECT TO MAIN LOG PAGE AFTER UPDATE ===
// After updating, send the user back to the member log viewer page.
header("Location: member.php");
exit();
?>
