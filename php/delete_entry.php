<?php
// FILE: delete_entry.php

session_start();
// Security: Ensure user is logged in before allowing deletion
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

// Get the ID from the URL parameter
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    try {
        $pdo = new PDO('mysql:host=127.0.0.1;dbname=elevator', 'Alanhpm', 'Alanhpm1382');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $pdo->prepare("DELETE FROM elevatorNetwork WHERE id = :id");
        $stmt->execute([':id' => $id]);
    } catch (PDOException $e) {
        die("Error deleting record: " . $e->getMessage());
    }
}

// Redirect back to the main log page
header("Location: member.php");
exit();
?>