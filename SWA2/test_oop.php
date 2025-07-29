<?php
// FILE: test_oop.php
// This file serves as a test bed for the Object-Oriented Programming (OOP) implementation
// of the elevator system components. It instantiates objects of the defined classes
// (Node, Elevator, FloorNode, CallButton, DistanceSensor) and calls their public methods
// to demonstrate their functionality and interactions.

// --- Require all necessary class files ---
// These 'require_once' statements ensure that the class definitions are loaded
// before they are used to create objects.
require_once 'Node.php';              // The base class for all components.
require_once 'Elevator.php';          // The Elevator car class.
require_once 'InvalidFloorException.php'; // Custom exception for invalid floor requests.
require_once 'FloorNode.php';         // Represents a specific floor in the building.
require_once 'CallButton.php';        // Represents elevator call buttons.
require_once 'DistanceSensor.php';    // Represents a sensor for detecting distance/obstacles.

echo "<h1>Testing OOP Implementation</h1>";

// --- Node Instantiation ---
echo "<h2>Node Tests</h2>";
// Create a generic Node object with ID 100.
$generalNode = new Node(id: 100);
// Demonstrate getting the ID of the general node.
echo "<p>General Node created with ID: " . $generalNode->getID() . "</p>";
echo "<hr>"; // Horizontal rule for visual separation.

// --- Elevator Instantiation (existing from previous work) ---
echo "<h2>Elevator Tests</h2>";
// Create an Elevator object with a specific Node ID (0x0101 in hex, which is 257 decimal)
// and a total of 3 floors.
$elevatorCar = new Elevator(id: 0x0101, totalFloors: 3);
// Accessing the ID via the inherited getID() method from the Node class.
echo "<p>Elevator created with Node ID: " . $elevatorCar->getID() . "</p>";
// Get and display the current floor of the elevator.
echo "<p>Current floor is: " . $elevatorCar->getCurrentFloor() . "</p>";

// Use a try...catch block to handle potential exceptions, specifically InvalidFloorException.
try {
    echo "<h3>Attempting a valid elevator request...</h3>";
    // Request the elevator to go to floor 2 (a valid floor).
    $elevatorCar->requestFloor(2);
    // Display the elevator's new current floor after the successful request.
    echo "<p>Request successful. The elevator is now on floor: " . $elevatorCar->getCurrentFloor() . "</p>";

    echo "<h3>Attempting an INVALID elevator request...</h3>";
    // This call will throw the custom exception because floor 5 is out of bounds.
    $elevatorCar->requestFloor(5);

} catch (InvalidFloorException $e) {
    // This 'catch' block specifically targets the InvalidFloorException.
    // When an InvalidFloorException is thrown in the 'try' block, execution jumps here.
    echo "<p style='color:red;'><strong>Caught expected error:</strong> " . $e->getMessage() . "</p>";
}
echo "<hr>"; // Horizontal rule for visual separation.

// --- FloorNode Instantiation ---
echo "<h2>FloorNode Tests</h2>";
// Create two FloorNode objects for floor 1 and floor 2, each with a unique ID.
$floor1 = new FloorNode(id: 0x0201, floorNumber: 1);
$floor2 = new FloorNode(id: 0x0202, floorNumber: 2);
// Display information about the created FloorNodes.
echo "<p>Floor Node for Floor " . $floor1->getFloorNumber() . " created with ID: " . $floor1->getID() . "</p>";
echo "<p>Floor Node for Floor " . $floor2->getFloorNumber() . " created with ID: " . $floor2->getID() . "</p>";
// Simulate calling the elevator from Floor 1 to Floor 3.
echo "<p>" . $floor1->callElevator(3) . "</p>";
echo "<hr>"; // Horizontal rule for visual separation.

// --- CallButton Instantiation ---
echo "<h2>CallButton Tests</h2>";
// Create two CallButton objects: an 'up' button on Floor 1 and a 'down' button on Floor 2.
$upButtonFloor1 = new CallButton(id: 0x0301, targetFloor: 2);
$downButtonFloor2 = new CallButton(id: 0x0302, targetFloor: 1);
// Display information about the created button.
echo "<p>Button ID " . $upButtonFloor1->getID() . " (Up on Floor 1) created for floor: " . $upButtonFloor1->getTargetFloor() . "</p>";
// Check and display the initial state of the button (should be not pressed).
echo "<p>Is Up Button pressed? " . ($upButtonFloor1->isPressed() ? 'Yes' : 'No') . "</p>";
// Simulate pressing the button.
echo "<p>" . $upButtonFloor1->press() . "</p>";
// Check and display the state after pressing the button.
echo "<p>Is Up Button pressed now? " . ($upButtonFloor1->isPressed() ? 'Yes' : 'No') . "</p>";
// Simulate releasing the button.
$upButtonFloor1->release();
// Check and display the state after releasing the button.
echo "<p>After release, is Up Button pressed? " . ($upButtonFloor1->isPressed() ? 'Yes' : 'No') . "</p>";
echo "<hr>"; // Horizontal rule for visual separation.

// --- DistanceSensor Instantiation ---
echo "<h2>DistanceSensor Tests</h2>";
// Create a DistanceSensor object with ID 0x0401 and a detection threshold of 0.2 meters (20 cm).
$doorSensor = new DistanceSensor(id: 0x0401, detectionThreshold: 0.2);
echo "<p>Door Sensor (ID: " . $doorSensor->getID() . ") created.</p>";
// Access and display the static property 'units' directly from the class.
echo "<p>Default units: " . DistanceSensor::$units . "</p>";

// Get a distance reading and display it.
$distance1 = $doorSensor->getDistance();
echo "<p>Sensor detected distance: " . $distance1 . " " . DistanceSensor::$units . "</p>";
// Check and display if an obstacle is detected.
echo "<p>Obstacle detected? " . ($doorSensor->detectObstacle() ? 'Yes' : 'No') . "</p>";

// Get another distance reading and display it.
$distance2 = $doorSensor->getDistance();
echo "<p>Another reading: " . $distance2 . " " . DistanceSensor::$units . "</p>";
// Check and display if an obstacle is detected.
echo "<p>Obstacle detected? " . ($doorSensor->detectObstacle() ? 'Yes' : 'No') . "</p>";

// Use the static method to convert a distance and display the result.
echo "<p>Converted " . $distance2 . "m to " . DistanceSensor::convertToCentimeters($distance2) . "cm (using static method).</p>";
echo "<hr>"; // Horizontal rule for visual separation.

echo "<p>All classes instantiated and basic methods tested.</p>";
?>
