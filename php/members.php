<?php
session_start();

// 🔒 Authentication check
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: GUI_login.php'); // Adjust path if needed
    exit;
}

// ✅ Connect to database
try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=elevator', 'ese_group4', 'ESEgroup4!');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die(" DB connection failed: " . $e->getMessage());
}

// ✅ Handle insert
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['submitInsert'])) {
    $stmt = $pdo->prepare("
        INSERT INTO elevatorNetwork 
        (date, time, nodeID, status, currentFloor, requestedFloor, otherInfo, eventType, processed)
        VALUES (CURDATE(), CURTIME(), ?, ?, ?, ?, ?, 'GUI_CALL', 0)
    ");
    $stmt->execute([
        $_POST['nodeID'],
        $_POST['status'],
        $_POST['currentFloor'],
        $_POST['requestedFloor'],
        $_POST['otherInfo']
    ]);
    echo "<p style='color:green;'> Record inserted successfully!</p>";
}

//  Handle update/delete
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['action']) && isset($_POST['targetID'])) {
    $targetID = intval($_POST['targetID']);

    if ($_POST['action'] === 'update') {
        $fields = [];
        $params = [];

        if ($_POST['status'] !== '') {
            $fields[] = "status = :status";
            $params[':status'] = $_POST['status'];
        }
        if ($_POST['currentFloor'] !== '') {
            $fields[] = "currentFloor = :currentFloor";
            $params[':currentFloor'] = $_POST['currentFloor'];
        }
        if ($_POST['requestedFloor'] !== '') {
            $fields[] = "requestedFloor = :requestedFloor";
            $params[':requestedFloor'] = $_POST['requestedFloor'];
        }
        if ($_POST['otherInfo'] !== '') {
            $fields[] = "otherInfo = :otherInfo";
            $params[':otherInfo'] = $_POST['otherInfo'];
        }

        if (count($fields) > 0) {
            $params[':id'] = $targetID;
            $sql = "UPDATE elevatorNetwork SET " . implode(', ', $fields) . " WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            echo "<p style='color:green;'>✅ Record ID $targetID updated.</p>";
        } else {
            echo "<p style='color:orange;'>⚠️ No values to update.</p>";
        }

    } elseif ($_POST['action'] === 'delete') {
        $stmt = $pdo->prepare("DELETE FROM elevatorNetwork WHERE id = ?");
        $stmt->execute([$targetID]);
        echo "<p style='color:red;'>🗑️ Record ID $targetID deleted.</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Members Area - Elevator Network</title>
</head>
<body>
  <h1>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?> 👋</h1>

  <!-- INSERT Form -->
  <h2>➕ Insert New Elevator Record</h2>
  <form method="POST">
    <label>Node ID:</label>
    <input type="number" name="nodeID" required><br><br>

    <label>Status:</label>
    <input type="number" name="status" required><br><br>

    <label>Current Floor:</label>
    <input type="number" name="currentFloor" required><br><br>

    <label>Requested Floor:</label>
    <input type="number" name="requestedFloor" required><br><br>

    <label>Other Info:</label>
    <input type="text" name="otherInfo"><br><br>

    <button type="submit" name="submitInsert">Insert Record</button>
  </form>

  <!-- DISPLAY Table -->
  <h2>📋 Recent Elevator Logs</h2>
  <table border="1" cellpadding="5" cellspacing="0">
    <tr>
      <th>ID</th><th>Date</th><th>Time</th><th>Node ID</th>
      <th>Status</th><th>Current Floor</th><th>Requested Floor</th>
      <th>Other Info</th><th>Event Type</th><th>Processed</th>
    </tr>
    <?php
    $stmt = $pdo->query("SELECT * FROM elevatorNetwork ORDER BY id DESC LIMIT 10");
    while ($row = $stmt->fetch()) {
        echo "<tr>
                <td>{$row['id']}</td>
                <td>{$row['date']}</td>
                <td>{$row['time']}</td>
                <td>{$row['nodeID']}</td>
                <td>{$row['status']}</td>
                <td>{$row['currentFloor']}</td>
                <td>{$row['requestedFloor']}</td>
                <td>{$row['otherInfo']}</td>
                <td>{$row['eventType']}</td>
                <td>{$row['processed']}</td>
              </tr>";
    }
    ?>
  </table>

  <!-- UPDATE/DELETE Form -->
  <h2>✏️ Update or 🗑️ Delete a Record</h2>
  <form method="POST">
    <label>Target Row ID:</label>
    <input type="number" name="targetID" required><br><br>

    <label>Status:</label>
    <input type="number" name="status"><br>

    <label>Current Floor:</label>
    <input type="number" name="currentFloor"><br>

    <label>Requested Floor:</label>
    <input type="number" name="requestedFloor"><br>

    <label>Other Info:</label>
    <input type="text" name="otherInfo"><br><br>

    <button type="submit" name="action" value="update">Update Row</button>
    <button type="submit" name="action" value="delete" style="background-color:red;color:white;">Delete Row</button>
  </form>
</body>
</html>
