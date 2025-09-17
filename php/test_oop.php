<?php
// FILE: test_oop.php

require_once 'Elevator.php';

echo "<h1>Testing OOP Implementation</h1>";

// Create an object instantiation of your Elevator class
$elevatorCar = new Elevator(id: 0x0101, totalFloors: 3);

echo "<p>Elevator created with Node ID: " . $elevatorCar->getNodeID() . "</p>";
echo "<p>Current floor is: " . $elevatorCar->getCurrentFloor() . "</p>";

// Use a try...catch block to handle potential exceptions
try {
    echo "<h3>Attempting a valid request...</h3>";
    $elevatorCar->requestFloor(2);
    echo "<p>Request successful. The elevator is now on floor: " . $elevatorCar->getCurrentFloor() . "</p>";

    echo "<h3>Attempting an INVALID request...</h3>";
    // This call will throw the custom exception
    $elevatorCar->requestFloor(5);

} catch (InvalidFloorException $e) {
    // Catch the specific custom exception
    echo "<p style='color:red;'><strong>Caught expected error:</strong> " . $e->getMessage() . "</p>";
}
?>