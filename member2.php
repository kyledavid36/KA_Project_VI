<?php
// member.php

// Show PHP errors (for debugging — remove on production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// --- Database Configuration ---
$servername = "localhost";
$username = "root";
$password = "kyledavid"; // ✅ Replace with your actual password
$dbname = "quizElevator";

// Initialize variables
$message = "";
$message_type = "";

// 1. Connect to DB
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 2. Process POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nodeID = isset($_POST['nodeID']) ? (int)$_POST['nodeID'] : 0;
    $newFloorNumber = isset($_POST['floorNumber']) ? (int)$_POST['floorNumber'] : 0;

    if ($nodeID <= 0 || $newFloorNumber < 0) {
        $message = "Invalid Node ID or Floor Number.";
        $message_type = "error";
    } else {
        $sql = "UPDATE carNode SET floorNumber = ? WHERE nodeID = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ii", $newFloorNumber, $nodeID);
            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    $message = "Updated Node $nodeID to Floor $newFloorNumber.";
                    $message_type = "success";
                } else {
                    $message = "No change made (maybe same floor or Node ID doesn't exist).";
                    $message_type = "info";
                }
            } else {
                $message = "Update error: " . $stmt->error;
                $message_type = "error";
            }
            $stmt->close();
        } else {
            $message = "SQL prepare error: " . $conn->error;
            $message_type = "error";
        }
    }
}

// 3. Fetch table data
$combined_node_data = [];
$sql_select_join = "
    SELECT
        en.nodeID,
        en.info,
        en.status,
        cn.floorNumber
    FROM
        elevatorNodes AS en
    LEFT JOIN
        carNode AS cn ON en.nodeID = cn.nodeID
    ORDER BY
        en.nodeID;
";

$result_join = $conn->query($sql_select_join);
if ($result_join) {
    while ($row = $result_join->fetch_assoc()) {
        $combined_node_data[] = $row;
    }
} else {
    $message = "Error fetching node data: " . $conn->error;
    $message_type = "error";
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Elevator System Monitor</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
<div class="container bg-white p-8 rounded-2xl shadow-xl w-full max-w-4xl">
    <h1 class="text-3xl font-bold mb-6 text-center">Elevator System Monitor & Updater</h1>

    <?php if (!empty($message)): ?>
        <div class="message mb-4 px-4 py-2 rounded-lg font-medium
            <?= $message_type === 'success' ? 'bg-green-100 text-green-700' :
                ($message_type === 'error' ? 'bg-red-100 text-red-700' : 'bg-blue-100 text-blue-700'); ?>">
            <?= htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <form method="POST" class="space-y-4">
        <div>
            <label for="nodeID" class="block font-semibold text-gray-700">Node ID to Update:</label>
            <input type="number" id="nodeID" name="nodeID" required class="w-full border p-2 rounded-lg">
        </div>
        <div>
            <label for="floorNumber" class="block font-semibold text-gray-700">Set New Floor Number:</label>
            <input type="number" id="floorNumber" name="floorNumber" required class="w-full border p-2 rounded-lg">
        </div>
        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white p-3 rounded-xl font-bold">
            Update Node Floor
        </button>
    </form>

    <h2 class="text-2xl font-bold mt-8 mb-4 text-center">Current Elevator Node Data</h2>
    <?php if (!empty($combined_node_data)): ?>
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white border border-gray-200">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-4 py-2 text-left">Node ID</th>
                        <th class="px-4 py-2 text-left">Info</th>
                        <th class="px-4 py-2 text-left">Status</th>
                        <th class="px-4 py-2 text-left">Floor Number</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($combined_node_data as $node): ?>
                        <tr class="border-t">
                            <td class="px-4 py-2"><?= htmlspecialchars($node['nodeID']) ?></td>
                            <td class="px-4 py-2"><?= htmlspecialchars($node['info']) ?></td>
                            <td class="px-4 py-2"><?= htmlspecialchars($node['status']) ?></td>
                            <td class="px-4 py-2">
                                <?= $node['floorNumber'] !== null ? htmlspecialchars($node['floorNumber']) : 'N/A' ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p class="text-gray-600 text-center">No elevator data available.</p>
    <?php endif; ?>
</div>
</body>
</html>
