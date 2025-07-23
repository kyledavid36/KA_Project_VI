<?php
/**
 * FILE: fetchCANlog.php
 * AUTHOR: Alan Hpm, Kyle Dick
 * PURPOSE: 
 *  - Retrieves recent CAN bus activity from the `CAN_subnetwork` table.
 *  - Used for diagnostic purposes (e.g., Chart.js visualization).
 *  - Returns JSON array with timestamp, nodeID, direction, message.
 * USAGE: Called asynchronously via AJAX from diagnostics page.
 */

header('Content-Type: application/json');

try {
    // 1. Connect to MySQL
    $pdo = new PDO("mysql:host=localhost;dbname=elevator", "ese_group4", "ESEgroup4!");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 2. Query last 50 CAN log entries
    $stmt = $pdo->query("SELECT timestamp, nodeID, direction, message FROM CAN_subnetwork ORDER BY id DESC LIMIT 50");
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 3. Return data as JSON
    echo json_encode($logs);

} catch (PDOException $e) {
    // 4. Handle DB error gracefully
    echo json_encode(['error' => 'Database connection failed.']);
}
?>
