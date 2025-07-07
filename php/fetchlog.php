<?php
header('Content-Type: application/json');

try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=elevator', 'Alanhpm', 'Alanhpm1382');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->query("
        SELECT nodeID, currentFloor, requestedFloor, event_time
        FROM elevatorNetwork
        ORDER BY id DESC
        LIMIT 10
    ");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($rows);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
