<?php
// updateFloor.php

header('Content-Type: application/json');

// DB setup
$host = 'localhost';
$db   = 'elevator';
$user = 'Alanhpm';
$pass = 'Alanhpm1382!'; // Replace with real password
$charset = 'utf8mb4';

// Connect using PDO
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);

    // Get floor number from POST
    if (!isset($_POST['floor'])) {
        echo json_encode(['success' => false, 'message' => 'No floor provided']);
        exit;
    }

    $floor = intval($_POST['floor']);

    // Insert into elevatorNetwork table
    $stmt = $pdo->prepare("INSERT INTO elevatorNetwork (date, time, nodeID, status, eventType, currentFloor, requestedFloor, processed, otherInfo)
        VALUES (CURDATE(), CURTIME(), :nodeID, :status, :eventType, :currentFloor, :requestedFloor, :processed, :otherInfo)");

    $stmt->execute([
        ':nodeID' => 256, // from GUI
        ':status' => 1,
        ':eventType' => 'FLOOR_REQUEST',
        ':currentFloor' => $floor, // Assuming current floor is same as requested
        ':requestedFloor' => $floor,
        ':processed' => 0,
        ':otherInfo' => 'GUI call (alsaSteamGUI)'
    ]);

    echo json_encode(['success' => true, 'floor' => $floor]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
