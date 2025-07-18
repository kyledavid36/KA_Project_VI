<?php
// This script receives a floor number from a frontend POST request
// and logs that request into the 'elevatorNetwork' table of the MySQL database.

// === 1. DATABASE CONNECTION SETUP ===
// Create a new PDO (PHP Data Object) connection to the MySQL database.
// 'elevator' is the name of the database.
// 'Alanhpm' and 'Alanhpm1382' are the database username and password respectively.
$pdo = new PDO('mysql:host=127.0.0.1;dbname=elevator', 'Alanhpm', 'Alanhpm1382');

// Set the PDO to throw exceptions on SQL errors. Helpful for debugging and preventing silent failures.
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// === 2. RETRIEVE FLOOR FROM POST DATA ===
// Checks if the 'floor' key exists in the POST request and converts it to an integer.
// If it doesn’t exist, we set $floor to null.
$floor = isset($_POST['floor']) ? intval($_POST['floor']) : null;

// === 3. VALIDATE INPUT ===
// If no valid floor number was received, stop the script and return a JSON error.
if ($floor === null) {
    echo json_encode(['success' => false, 'message' => 'No floor received']);
    exit;
}

// === 4. INSERT DATA INTO elevatorNetwork TABLE ===
// Prepare an SQL statement to insert the floor request.
// We're logging nodeID (sender), status (e.g., 1 = active), currentFloor, requestedFloor, and metadata.
$stmt = $pdo->prepare("
    INSERT INTO elevatorNetwork (nodeID, status, currentFloor, requestedFloor, otherInfo)
    VALUES (:nodeID, :status, :currentFloor, :requestedFloor, :otherInfo)
");

// Execute the SQL statement with actual values.
// nodeID = 0x0101 (elevator controller), status = 1 (active),
// currentFloor/requestedFloor = same in this simple case (user pressed button for a floor),
// otherInfo = text note to show origin of command.
$stmt->execute([
    ':nodeID' => 0x0101,  // Hexadecimal for 257, represents Elevator Controller
    ':status' => 1,       // Status flag: 1 might indicate "active" or "initiated"
    ':currentFloor' => $floor,
    ':requestedFloor' => $floor,
    ':otherInfo' => 'User request via GUI'
]);

// === 5. RETURN SUCCESS RESPONSE TO FRONTEND ===
// Sends a JSON response back confirming that the floor was received and logged.
echo json_encode(['success' => true, 'floor' => $floor]);
?>
