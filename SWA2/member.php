<?php
// FILE: member.php

session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

// --- Database Connection ---
try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=elevator', 'Alanhpm', 'Alanhpm1382');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch all records to display
    $stmt = $pdo->query("SELECT id, nodeID, currentFloor, requestedFloor, otherInfo, event_time FROM elevatorNetwork ORDER BY id DESC");
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Members Area - Elevator Log</title>
    <link rel="stylesheet" href="css/site_style.css" />
</head>
<body>
    <div class="container">
        <h1>Welcome, <?php echo htmlspecialchars($_SESSION['user_fullName']); ?>!</h1>
        <p><a href="logout.php">Logout</a></p>
        <hr>
        
        <h2>Elevator Network Log</h2>
        <table border="1" style="width:100%; border-collapse: collapse;">
            <thead>
                <tr style="text-align: left;">
                    <th>ID</th>
                    <th>Node</th>
                    <th>Current Floor</th>
                    <th>Requested Floor</th>
                    <th>Info</th>
                    <th>Timestamp</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($logs as $log): ?>
                <tr>
                    <td><?php echo htmlspecialchars($log['id']); ?></td>
                    <td><?php echo htmlspecialchars($log['nodeID']); ?></td>
                    <td><?php echo htmlspecialchars($log['currentFloor']); ?></td>
                    <td><?php echo htmlspecialchars($log['requestedFloor']); ?></td>
                    <td><?php echo htmlspecialchars($log['otherInfo']); ?></td>
                    <td><?php echo htmlspecialchars($log['event_time']); ?></td>
                    <td>
                        <a href="edit_entry.php?id=<?php echo $log['id']; ?>">Modify</a> |
                        <a href="delete_entry.php?id=<?php echo $log['id']; ?>" onclick="return confirm('Are you sure you want to delete this entry?');">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>