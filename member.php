<?php
// member.php

/**
 * Purpose: This script manages elevator node data via a web interface.
 * It allows viewing and updating elevator node information, including their floor numbers.
 *
 * Exists:
 * - HTML form: For user input (Node ID and New Floor Number).
 * - PHP logic: Processes form submissions, interacts with the MySQL database.
 * - Database: Connects to 'quizElevator' database, using tables 'elevatorNodes' and 'carNodes'.
 *
 * Inputs:
 * - Initial page load: GET request.
 * - Form submission: POST request with 'nodeID' (int) and 'floorNumber' (int).
 *
 * Outputs:
 * - Web page displaying an update form and a table of combined node data.
 * - Success/Error/Info messages after updates.
 * - Updates to the 'carNodes' table in the database.
 */
 
// --- Database Configuration ---
// Defines database connection parameters.
// IMPORTANT: Change 'your_mysql_root_password' to your actual MySQL password.
$servername = "localhost";
$username = "ese_group4";
$password = "kyledavid"; // CHANGE THIS!
$dbname = "quizElevator";

// Variables for user feedback messages.
$message = "";
$message_type = "";

// --- 1. Establish Database Connection ---
// Creates a new connection to the MySQL database.
// Fails if credentials are wrong or MySQL server is down.
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error); // Terminates script on connection failure.
}

// --- 2. Process Form Submission (if applicable) ---
// Executes when the form is submitted via POST request.
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and validate input to prevent SQL injection and ensure correct types.
    $nodeID = isset($_POST['nodeID']) ? (int)$_POST['nodeID'] : 0;
    $newFloorNumber = isset($_POST['floorNumber']) ? (int)$_POST['floorNumber'] : 0;

    // Basic validation for input values.
    if ($nodeID <= 0 || $newFloorNumber < 0) {
        $message = "Invalid Node ID or Floor Number provided.";
        $message_type = "error";
    } else {
        // SQL UPDATE query using a prepared statement for security.
        // Placeholders '?' are used for data, 'carNodes' is the target table.
        $sql = "UPDATE carNodes SET floorNumber = ? WHERE nodeID = ?";

        // Prepares the SQL statement for execution. Fails on SQL syntax error.
        if ($stmt = $conn->prepare($sql)) {
            // Binds PHP variables to the SQL placeholders. 'ii' denotes two integers.
            $stmt->bind_param("ii", $newFloorNumber, $nodeID);

            // Executes the prepared statement. Fails on database execution errors (e.g., permissions).
            if ($stmt->execute()) {
                // Checks if any rows were affected by the update.
                if ($stmt->affected_rows > 0) {
                    $message = "Record updated successfully for Node ID: " . $nodeID . " to Floor: " . $newFloorNumber . ".";
                    $message_type = "success";
                } else {
                    $message = "No record found with Node ID: " . $nodeID . " or floor number was already " . $newFloorNumber . ".";
                    $message_type = "info";
                }
            } else {
                $message = "Error executing update statement: " . $stmt->error;
                $message_type = "error";
            }
            $stmt->close(); // Closes the prepared statement.
        } else {
            $message = "Error preparing update statement: " . $conn->error;
            $message_type = "error";
        }
    }
}

// --- 3. Retrieve and Display Combined Data using LEFT JOIN ---
// Fetches data from both 'elevatorNodes' and 'carNodes' for display.
$combined_node_data = [];

// SQL query using LEFT JOIN to get all elevator nodes and their floor numbers if available.
// 'en' is an alias for elevatorNodes, 'cn' for carNodes.
$sql_select_join = "
    SELECT
        en.nodeID,
        en.info,
        en.status,
        cn.floorNumber
    FROM
        elevatorNodes AS en
    LEFT JOIN
        carNodes AS cn ON en.nodeID = cn.nodeID
    ORDER BY
        en.nodeID;
";

// Executes the SELECT query. Fails on SQL syntax error or read permissions.
$result_join = $conn->query($sql_select_join);

// Processes query results into an array.
if ($result_join) {
    while ($row = $result_join->fetch_assoc()) {
        $combined_node_data[] = $row;
    }
} else {
    $message = "Error fetching combined node data: " . $conn->error;
    $message_type = "error";
}

// --- Close Database Connection ---
// Releases database resources.
$conn->close();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Elevator System Monitor</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* General styling for body and container */
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f3f4f6;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            min-height: 100vh;
            padding: 2rem;
            box-sizing: border-box;
        }
        .container {
            background-color: #ffffff;
            padding: 2.5rem;
            border-radius: 1.5rem;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            max-width: 800px;
            width: 100%;
            text-align: center;
        }
        /* Styling for message boxes (success, error, info) */
        .message {
            padding: 0.75rem;
            margin-bottom: 1rem;
            border-radius: 0.75rem;
            font-weight: 500;
        }
        .message.success { background-color: #d1fae5; color: #065f46; }
        .message.error { background-color: #fee2e2; color: #991b1b; }
        .message.info { background-color: #e0f2fe; color: #0369a1; }
        /* Styling for input fields and buttons */
        input[type="number"], input[type="submit"] {
            border-radius: 0.75rem;
        }
        /* Table text alignment */
        th, td {
            text-align: left;
        }
    </style>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="container space-y-6">
        <h1 class="text-3xl font-extrabold text-gray-800 mb-6">Elevator System Monitor & Updater</h1>

        <!-- Displays feedback messages from PHP logic -->
        <?php if (!empty($message)): ?>
            <div class="message <?php echo $message_type; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <!-- Form to update a node's floor number -->
        <form method="POST" action="member.php" class="space-y-4">
            <div>
                <label for="nodeID" class="text-lg font-medium text-gray-700 mb-1">Node ID to Update:</label>
                <input type="number" id="nodeID" name="nodeID" required
                       class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-lg"
                       placeholder="e.g., 1">
            </div>

            <div>
                <label for="floorNumber" class="text-lg font-medium text-gray-700 mb-1">Set New Floor Number:</label>
                <input type="number" id="floorNumber" name="floorNumber" required
                       class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-lg"
                       placeholder="e.g., 10">
            </div>

            <button type="submit"
                    class="w-full px-6 py-3 bg-gradient-to-r from-blue-500 to-indigo-600 text-white font-bold text-xl rounded-xl shadow-lg hover:from-blue-600 hover:to-indigo-700 focus:outline-none focus:ring-4 focus:ring-blue-500 focus:ring-opacity-50 transition transform hover:-translate-y-1">
                Update Node Floor
            </button>
        </form>

        <!-- Displays current combined data from 'elevatorNodes' and 'carNodes' -->
        <h2 class="text-2xl font-bold text-gray-800 mt-8 mb-4">Current Elevator Node Data</h2>
        <?php if (!empty($combined_node_data)): ?>
            <div class="overflow-x-auto rounded-xl shadow-md">
                <table class="min-w-full bg-white border-collapse">
                    <thead>
                        <tr class="bg-gray-200">
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-600 uppercase tracking-wider rounded-tl-xl">Node ID</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-600 uppercase tracking-wider">Info</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-600 uppercase tracking-wider rounded-tr-xl">Floor Number</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php foreach ($combined_node_data as $node): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-lg text-gray-800"><?php echo htmlspecialchars($node['nodeID']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-lg text-gray-800"><?php echo htmlspecialchars($node['info']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-lg text-gray-800"><?php echo htmlspecialchars($node['status']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-lg text-gray-800">
                                    <?php
                                    // Displays floor number or 'N/A' if no floor is associated.
                                    echo ($node['floorNumber'] !== null) ? htmlspecialchars($node['floorNumber']) : 'N/A';
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="text-gray-600">No combined node data available to display.</p>
        <?php endif; ?>
    </div>
</body>
</html>