<?php
/**
 * ───────────────────────────────────────────────────────────────
 * FILE: fetchlog.php
 * AUTHORS: Alan Hosseinpour, Kyle Dick
 * PURPOSE:
 *  - Fetches the last 10 log entries from the `elevatorNetwork` table.
 *  - Used for display in the frontend changelog or diagnostics panel.
 *  - Returns data as a JSON array for easy parsing by JavaScript.
 * DEPENDENCIES:
 *  - Database: elevator
 *  - Table: elevatorNetwork
 * ───────────────────────────────────────────────────────────────
 */

// === 1. CONNECT TO DATABASE ===
$pdo = new PDO('mysql:host=127.0.0.1;dbname=elevator', 'ese_group4', 'ESEgroup4!');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// === 2. FETCH LAST 10 EVENTS FROM elevatorNetwork TABLE ===
$stmt = $pdo->query("
    SELECT date, time, nodeID, status, currentFloor, requestedFloor, eventType, otherInfo
    FROM elevatorNetwork
    ORDER BY id DESC
    LIMIT 10
");

// === 3. FETCH RESULTS INTO ASSOCIATIVE ARRAY ===
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// === 4. RETURN AS JSON RESPONSE TO FRONTEND ===
header('Content-Type: application/json');
echo json_encode($logs);
?>
