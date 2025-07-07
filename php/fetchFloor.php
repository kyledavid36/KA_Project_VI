<?php
header('Content-Type: application/json');
try {
  $pdo = new PDO('mysql:host=127.0.0.1;dbname=elevator', 'Alanhpm', 'Alanhpm1382');
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  $stmt = $pdo->prepare("SELECT currentFloor FROM elevatorNetwork WHERE nodeID = :id ORDER BY id DESC LIMIT 1");
  $stmt->execute([':id' => 0x0101]);
  $row = $stmt->fetch();

  echo json_encode(['floor' => $row ? $row['currentFloor'] : null]);
} catch (PDOException $e) {
  echo json_encode(['error' => 'DB error']);
}
?>
