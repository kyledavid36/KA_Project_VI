<?php
/**
 * ───────────────────────────────────────────────────────────────
 * FILE: fetchFloor.php
 * AUTHORS: Alan Hosseinpour, Kyle Dick
 * PURPOSE:
 *  - Retrieves the most recent `currentFloor` value of the elevator.
 *  - Intended to be called asynchronously by the GUI (e.g., via JavaScript).
 *  - Returns a JSON object like: { "floor": 2 } works as debugging mechnaism to test the proper logging of floor number to database
 * DEPENDENCIES:
 *  - Database: elevator
 *  - Table: elevatorNetwork
 * ───────────────────────────────────────────────────────────────
 */

// === 1. SET CONTENT TYPE TO JSON FOR JAVASCRIPT CONSUMPTION ===
header('Content-Type: application/json');

try {
    // === 2. CONNECT TO DATABASE ===
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=elevator', 'ese_group4', 'ESEgroup4!');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // === 3. QUERY FOR MOST RECENT currentFloor OF nodeID 0x0101 (257) ===
    $stmt = $pdo->prepare("
        SELECT currentFloor 
        FROM elevatorNetwork 
        WHERE nodeID = :id 
        ORDER BY id DESC 
        LIMIT 1
    ");
    $stmt->execute([':id' => 0x0101]); // 0x0101 = 257, elevator controller node

    // === 4. EXTRACT FLOOR VALUE OR NULL IF NOT FOUND ===
    $row = $stmt->fetch();

    // === 5. RETURN FLOOR IN JSON FORMAT ===
    echo json_encode(['floor' => $row ? $row['currentFloor'] : null]);

} catch (PDOException $e) {
    // === 6. HANDLE DATABASE ERRORS ===
    echo json_encode(['error' => 'DB error']);
}
?>
