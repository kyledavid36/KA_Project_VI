<?php
/**
 * ─────────────────────────────────────────────────────────────────────
 * FILE: updateFloor.php
 * AUTHORS: Alan Hpm, Kyle Dick
 * LAST UPDATED: July 2025
 * 
 * PURPOSE:
 *  This backend script handles floor requests submitted via the elevator GUI 
 *  (e.g., alsaSteamGUI.html). When a user clicks a floor button, this script:
 *   1. Validates and debounces the request to prevent spamming.
 *   2. Logs the request in the 'elevatorNetwork' table (for C++ backend to act).
 *   3. Simultaneously logs it into 'CAN_subnetwork' as a simulated TX event 
 *      for diagnostics and monitoring.
 *   4. Updates the latest elevatorNetwork row for nodeID 257 with the new floor.
 * 
 * DEPENDENCIES:
 * ─────────────────────────────────────────────────────────────────────
 *  - CALLED FROM:
 *     • /GUI/alsaSteamGUI.html or similar GUI pages (via JavaScript POST)
 * 
 *  - DATABASE STRUCTURE:
 *     • 'elevatorNetwork' table stores elevator state changes
 *     • 'CAN_subnetwork' table stores CAN TX/RX logs for diagnostics
 * 
 *  - RELATED FILES:
 *     • fetchFloor.php   — used to display current floor in GUI
 *     • fetchCANlog.php  — used by diagnostics.html to visualize CAN logs
 *     • databaseFunctions.cpp/.h — C++ code polls 'elevatorNetwork' for changes
 *     • Option 3 loop (main.cpp) on Raspberry Pi — reads new floor requests
 * ─────────────────────────────────────────────────────────────────────
 */

header('Content-Type: application/json');  // Ensure JSON response to frontend

// ───── DATABASE CONNECTION CONFIG ─────
$host = 'localhost';
$db   = 'elevator';
$user = 'ese_group4';
$pass = 'ESEgroup4!';
$charset = 'utf8mb4';

// Setup DSN and error mode for PDO
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];

try {
    // ───── CONNECT TO DATABASE ─────
    $pdo = new PDO($dsn, $user, $pass, $options);

    // ───── VALIDATE POST DATA ─────
    if (!isset($_POST['floor'])) {
        echo json_encode(['success' => false, 'message' => 'No floor provided']);
        exit;
    }

    $floor = intval($_POST['floor']);  // Convert to integer
    $now = new DateTime("now", new DateTimeZone("America/Toronto"));  // Get current timestamp in EST

    // ───── SPAM PROTECTION: ENFORCE 1s DEBOUNCE WINDOW ─────
    $stmt = $pdo->query("
        SELECT date, time FROM elevatorNetwork 
        WHERE nodeID = 257 
        ORDER BY id DESC LIMIT 1
    ");
    $last = $stmt->fetch();

    if ($last) {
        $lastDT = new DateTime($last['date'] . ' ' . $last['time'], new DateTimeZone("America/Toronto"));
        $diff = $now->getTimestamp() - $lastDT->getTimestamp();
        if ($diff < 1) {
            echo json_encode(['success' => false, 'message' => 'Request ignored (too soon after last one)']);
            exit;
        }
    }

    // ───── DUPLICATE REQUEST CHECK ─────
    $stmt = $pdo->query("
        SELECT requestedFloor FROM elevatorNetwork 
        WHERE nodeID = 257 
        ORDER BY id DESC LIMIT 1
    ");
    $lastFloor = $stmt->fetchColumn();

    if ($lastFloor !== false && intval($lastFloor) === $floor) {
        echo json_encode(['success' => false, 'message' => 'Floor already requested']);
        exit;
    }

    // ───── FETCH LAST KNOWN CURRENT FLOOR ─────
    $stmt = $pdo->query("
        SELECT currentFloor FROM elevatorNetwork 
        WHERE nodeID = 257 
        ORDER BY id DESC LIMIT 1
    ");
    $currentFloor = $stmt->fetchColumn();  // Might be null if first run

    // ───── INSERT NEW FLOOR REQUEST INTO elevatorNetwork ─────
    $insert = $pdo->prepare("
        INSERT INTO elevatorNetwork 
        (date, time, nodeID, status, eventType, currentFloor, requestedFloor, processed, otherInfo)
        VALUES (:date, :time, 257, 1, 'FLOOR_REQUEST', :currentFloor, :requestedFloor, 0, 'GUI call (alsaSteamGUI)')
    ");
    $insert->execute([
        ':date' => $now->format('Y-m-d'),
        ':time' => $now->format('H:i:s'),
        ':currentFloor' => $currentFloor ?? null,
        ':requestedFloor' => $floor
    ]);

    // ───── SIMULATED TX ENTRY FOR CAN_subnetwork (FROM GUI) ─────
    $log = $pdo->prepare("
        INSERT INTO CAN_subnetwork (timestamp, nodeID, direction, dataByte, source)
        VALUES (NOW(), 513, 'TX', :dataByte, 'GUI')
    ");
    $log->execute([
        ':dataByte' => strval($floor)
    ]);

    // ───── SEND SUCCESS RESPONSE TO FRONTEND ─────
    echo json_encode(['success' => true, 'floor' => $floor]);

} catch (Exception $e) {
    // On error, send error message as JSON
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
