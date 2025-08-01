<?php
// FILE: updateField.php
// DESCRIPTION: Function to update any field in a table, except the primary key.


//  Database connection using credentials
try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=elevator', 'ese_group4', 'ESEgroup4!');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("❌ Database connection failed: " . $e->getMessage());
}

//  Define values to update
$table = 'elevatorNodes';       //  parent table
$column = 'nodeName';           // Field to update
$newValue = 'Updated Elevator'; // New value to set
$idColumn = 'nodeID';           // Primary key column
$idValue = 1;                   // ID of the row to update

//  Prevent primary key update
if ($column === $idColumn) {
    die("❌ Error: Cannot update the primary key.");
}

//  Prepare and execute the update
try {
    $sql = "UPDATE `$table` SET `$column` = :value WHERE `$idColumn` = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':value' => $newValue,
        ':id' => $idValue
    ]);
    echo " Successfully updated `$column` to '$newValue' in `$table` where `$idColumn` = $idValue";
} catch (PDOException $e) {
    echo "❌ Update failed: " . $e->getMessage();
}
?>
