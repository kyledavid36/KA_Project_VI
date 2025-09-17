<?php
/**
 * ───────────────────────────────────────────────────────────────
 * FILE: fetchlog.php
 * AUTHORS: Alan Hosseinpour, Kyle Dick
 * UPDATED BY: ChatGPT
 * PURPOSE:
 *  - Fetches last 10 log entries from both:
 *      1. elevatorNetwork
 *      2. CAN_subnetwork
 *  - Returns combined JSON object with both datasets for frontend display.
 * ───────────────────────────────────────────────────────────────
 */

// === 1. CONNECT TO DATABASE ===
$pdo = new PDO('mysql:host=127.0.0.1;dbname=elevator', 'ese_group4', 'ESEgroup4!');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// === 2. FETCH LAST 10 EVENTS FROM elevatorNetwork TABLE ===
$elevatorStmt = $pdo->query("
    SELECT date, time, nodeID, status, currentFloor, requestedFloor, eventType, otherInfo
    FROM elevatorNetwork
    ORDER BY id DESC
    LIMIT 10
");
$elevatorLogs = $elevatorStmt->fetchAll(PDO::FETCH_ASSOC);

// === 3. FETCH LAST 10 EVENTS FROM CAN_subnetwork TABLE ===
$canStmt = $pdo->query("
    SELECT timestamp, nodeID, direction, message
    FROM CAN_subnetwork
    ORDER BY id DESC
    LIMIT 10
");
$canLogs = $canStmt->fetchAll(PDO::FETCH_ASSOC);

// Convert nodeID (integer) to hex string for CAN logs
foreach ($canLogs as &$log) {
    $log['nodeID'] = '0x' . strtoupper(str_pad(dechex($log['nodeID']), 4, '0', STR_PAD_LEFT));
}

// === 4. RETURN AS JSON WITH TWO CATEGORIES ===
header('Content-Type: application/json');
echo json_encode([
    'elevatorLogs' => $elevatorLogs,
    'canLogs' => $canLogs
]);
