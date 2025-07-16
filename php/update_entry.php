<?php
// FILE: update_entry.php

session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $currentFloor = isset($_POST['currentFloor']) ? (int)$_POST['currentFloor'] : 1;
    $requestedFloor = isset($_POST['requestedFloor']) ? (int)$_POST['requestedFloor'] : 1;
    $otherInfo = isset($_POST['otherInfo']) ? $_POST['otherInfo'] : '';

    if ($id > 0) {
        try {
            $pdo = new PDO('mysql:host=127.0.0.1;dbname=elevator', 'Alanhpm', 'Alanhpm1382');
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $sql = "UPDATE elevatorNetwork SET currentFloor = :currentFloor, requestedFloor = :requestedFloor, otherInfo = :otherInfo WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':currentFloor' => $currentFloor,
                ':requestedFloor' => $requestedFloor,
                ':otherInfo' => $otherInfo,
                ':id' => $id
            ]);
        } catch (PDOException $e) {
            die("Error updating record: " . $e->getMessage());
        }
    }
}

// Redirect back to the main log page
header("Location: member.php");
exit();
?>