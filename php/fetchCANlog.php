<?php
/**
 * ╔════════════════════════════════════════════════════════════════════════╗
 * ║ FILE: fetchCANlog.php                                                 ║
 * ║ AUTHORS: Alan Hpm, Kyle Dick                                          ║
 * ║ LAST UPDATED: July 24, 2025                                           ║
 * ║                                                                        ║
 * ║ PURPOSE:                                                              ║
 * ║ - Retrieve CAN bus TX/RX activity from `CAN_subnetwork` table         ║
 * ║ - Format nodeID/message fields as hexadecimal for diagnostics         ║
 * ║ - Output JSON for use by frontend dashboards (e.g., diagnostics.html) ║
 * ║                                                                        ║
 * ║ DEPENDENCIES:                                                         ║
 * ║ - Database: elevator                                                  ║
 * ║ - Table: CAN_subnetwork (timestamp, nodeID, direction, message)       ║
 * ║ - Used by: JS fetch() or AJAX on diagnostics or changelog pages      ║
 * ╚════════════════════════════════════════════════════════════════════════╝
 */

// ╔════════════════════════════════════════════════════╗
// ║ Set response type to JSON                          ║
// ╚════════════════════════════════════════════════════╝
header('Content-Type: application/json');

try {
    // ╔════════════════════════════════════════════════════╗
    // ║ 1. Connect to MySQL Database using PDO             ║
    // ║    - DB: elevator                                  ║
    // ║    - User: ese_group4                              ║
    // ╚════════════════════════════════════════════════════╝
    $pdo = new PDO("mysql:host=localhost;dbname=elevator", "ese_group4", "ESEgroup4!");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // ╔═══════════════════════════════════════════════════════════╗
    // ║ 2. Query last 50 entries from CAN_subnetwork table        ║
    // ╚═══════════════════════════════════════════════════════════╝
    $stmt = $pdo->query("SELECT timestamp, nodeID, direction, message FROM CAN_subnetwork ORDER BY id DESC LIMIT 50");
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // ╔══════════════════════════════════════════════════════════════╗
    // ║ 3. Format nodeID and message fields as hex strings           ║
    // ║    - nodeID: 4-digit hex (e.g., 0x0201)                      ║
    // ║    - message: convert numeric messages to hex if needed     ║
    // ╚══════════════════════════════════════════════════════════════╝
    foreach ($logs as &$log) {
        $log['nodeID'] = sprintf("0x%04X", intval($log['nodeID']));

        if (!str_starts_with($log['message'], '0x')) {
            $log['message'] = sprintf("0x%X", intval($log['message']));
        }
    }

    // ╔══════════════════════════════════════════════════╗
    // ║ 4. Output formatted log data as JSON             ║
    // ╚══════════════════════════════════════════════════╝
    echo json_encode($logs);

} catch (PDOException $e) {
    // ╔════════════════════════════════════════════════════╗
    // ║ 5. Error Handling: Return generic DB failure msg   ║
    // ╚════════════════════════════════════════════════════╝
    echo json_encode(['error' => 'Database connection failed.']);
}
?>
