<?php
// === PURPOSE ===
// This script is designed to be called (usually by JavaScript or the frontend GUI)
// to fetch the most recent currentFloor value of the elevator controller.
// It returns a JSON response containing the floor number.

// === 1. SET RESPONSE TYPE ===
// Tells the browser this script will return JSON format
header('Content-Type: application/json');

try {
    // === 2. CONNECT TO DATABASE ===
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=elevator', 'Alanhpm', 'Alanhpm1382');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // === 3. FETCH LATEST FLOOR ENTRY FOR CONTROLLER NODE ===
    $stmt = $pdo->prepare("
        SELECT currentFloor 
        FROM elevatorNetwork 
        WHERE nodeID = :id 
        ORDER BY id DESC 
        LIMIT 1
    ");
    // Use nodeID 0x0101 which is the Elevator Controller
    $stmt->execute([':id' => 0x0101]);

    // Get the floor number or return null if not found
    $row = $stmt->fetch();

    // === 4. RETURN RESPONSE TO FRONTEND ===
    echo json_encode(['floor' => $row ? $row['currentFloor'] : null]);

} catch (PDOException $e) {
    // If there's an error (e.g., DB failure), return an error response
    echo json_encode(['error' => 'DB error']);
}
?>
