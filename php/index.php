<?php
// === DATABASE CONNECTION ===
try {
  $pdo = new PDO('mysql:host=127.0.0.1;dbname=elevator', 'Alanhpm', 'Alanhpm1382');
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  // === GET MOST RECENT FLOOR LOG ===
  $stmt = $pdo->prepare("
    SELECT currentFloor 
    FROM elevatorNetwork 
    WHERE nodeID = :id 
    ORDER BY id DESC 
    LIMIT 1
  ");
  $stmt->execute([':id' => 0x0101]);
  $row = $stmt->fetch();
  $currentFloor = $row ? $row['currentFloor'] : 'N/A';

} catch (PDOException $e) {
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


  <!-- GUI Interface Frame -->
  <iframe src="../SteamGUI.html" style="width:100%; height:100vh; border:none;"></iframe>
</body>
</html>