<?php
/**
 * ──────────────────────────────────────────────────────────────────────
 * FILE: updateFloor.php
 * AUTHORS: Alan Hpm, Kyle Dick
 * PURPOSE:
 *  - This script handles incoming POST requests from the elevator GUI 
 *    (e.g., alsaSteamGUI.html) to request a new floor.
 *  - It logs the request in the `elevatorNetwork` table for tracking.
 *  - It also updates nodeID 257 so the C++ backend on the Raspberry Pi 
 *    can detect and respond to the new floor request.
 * DEPENDENCIES:
 *  - Called via AJAX from the GUI
 *  - Connected to MariaDB database `elevator`
 *  - Works in sync with:
 *     • fetchFloor.php (for reading the current floor)
 *     • C++ backend running on Raspberry Pi (option 3 loop)
 * ──────────────────────────────────────────────────────────────────────
 */

header('Content-Type: application/json');

// ──────────────────────
// Database Configuration
// ──────────────────────
$host = 'localhost';
$db   = 'elevator';
$user = 'ese_group4';
$pass = 'ESEgroup4!';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];

try {
    // ───────────────────────────────
    // Create PDO Connection to MySQL
    // ───────────────────────────────
    $pdo = new PDO($dsn, $user, $pass, $options);

    // ───────────────────────────────────────────────
    // Validate Incoming POST Data (floor must be set)
    // ───────────────────────────────────────────────
    if (!isset($_POST['floor'])) {
        echo json_encode(['success' => false, 'message' => 'No floor provided']);
        exit;
    }

    $floor = intval($_POST['floor']); // sanitize and cast to integer

    // ────────────────────────────────────────────────────────────
    // STEP 1: INSERT new log entry for GUI-triggered floor request
    // Logs the request in elevatorNetwork as nodeID 256 (GUI)
    // ────────────────────────────────────────────────────────────
    $insert = $pdo->prepare("INSERT INTO elevatorNetwork 
        (date, time, nodeID, status, eventType, currentFloor, requestedFloor, processed, otherInfo)
        VALUES (CURDATE(), CURTIME(), :nodeID, :status, :eventType, :currentFloor, :requestedFloor, :processed, :otherInfo)");

    $insert->execute([
        ':nodeID' => 256,                    // GUI client identifier
        ':status' => 1,                      // Status 1 = active
        ':eventType' => 'FLOOR_REQUEST',     // Type of event
        ':currentFloor' => $floor,           // Current and requested floor match (GUI side)
        ':requestedFloor' => $floor,
        ':processed' => 0,                   // Marked as not yet handled by backend
        ':otherInfo' => 'GUI call (alsaSteamGUI)' // Note for traceability
    ]);

    // ───────────────────────────────────────────────────────────
    // STEP 2: UPDATE nodeID 257 for Raspberry Pi (elevator control)
    // C++ backend continuously polls this row to detect floor change
    // ───────────────────────────────────────────────────────────
    $update = $pdo->prepare("UPDATE elevatorNetwork SET 
        requestedFloor = :requestedFloor,
        eventType = 'FLOOR_REQUEST',
        date = CURDATE(),
        time = CURTIME(),
        otherInfo = 'Updated by GUI'
        WHERE nodeID = 257");

    $update->execute([':requestedFloor' => $floor]);

    // ──────────────────────────────
    // Send back success response JSON
    // ──────────────────────────────
    echo json_encode(['success' => true, 'floor' => $floor]);

} catch (Exception $e) {
    // Handle any database or logic exceptions
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
