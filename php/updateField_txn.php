<?php
// FILE: updateField_txn.php
// Adds transaction and exception handling to the update process

try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=elevator', 'ese_group4', 'ESEgroup4!');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die(" Database connection failed: " . $e->getMessage());
}

// Update details
$table = 'elevatorNodes';
$column = 'nodeName';
$newValue = 'Transactional Update';  // <-- Updated name for Q8
$idColumn = 'nodeID';
$idValue = 1;

try {
    if ($column === $idColumn) {
        throw new Exception(" Attempt to update the primary key is not allowed.");
    }

    // Begin transaction
    $pdo->beginTransaction();

    // Perform update
    $sql = "UPDATE `$table` SET `$column` = :value WHERE `$idColumn` = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':value' => $newValue,
        ':id' => $idValue
    ]);

    // Commit transaction
    $pdo->commit();
    echo "Transaction successful: `$column` updated to '$newValue' for `$idColumn` = $idValue";

} catch (Exception $e) {
    // Rollback on any failure
    $pdo->rollBack();
    echo " Transaction failed: " . $e->getMessage();
}
?>
