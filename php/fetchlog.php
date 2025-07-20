<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=elevator', 'Alanhpm', 'Alanhpm1382!');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Get last 10 events
$stmt = $pdo->query("
    SELECT date, time, nodeID, status, currentFloor, requestedFloor, eventType, otherInfo
    FROM elevatorNetwork
    ORDER BY id DESC
    LIMIT 10
");

$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Return JSON for changelog
header('Content-Type: application/json');
echo json_encode($logs);
?>

