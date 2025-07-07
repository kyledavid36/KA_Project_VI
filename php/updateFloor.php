<?php
// Connect to DB
$pdo = new PDO('mysql:host=127.0.0.1;dbname=elevator', 'Alanhpm', 'Alanhpm1382');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Get floor from POST request
$floor = isset($_POST['floor']) ? intval($_POST['floor']) : null;

if ($floor === null) {
    echo json_encode(['success' => false, 'message' => 'No floor received']);
    exit;
}

// INSERT into elevatorNetwork table
$stmt = $pdo->prepare("
    INSERT INTO elevatorNetwork (nodeID, status, currentFloor, requestedFloor, otherInfo)
    VALUES (:nodeID, :status, :currentFloor, :requestedFloor, :otherInfo)
");
$stmt->execute([
    ':nodeID' => 0x0101,
    ':status' => 1,
    ':currentFloor' => $floor,
    ':requestedFloor' => $floor,
    ':otherInfo' => 'User request via GUI'
]);

echo json_encode(['success' => true, 'floor' => $floor]);
?>
