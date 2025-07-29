<?php
// FILE: Node.php
// This file defines the base class for all components in the elevator system.
// It establishes a common identifier (ID) for various nodes like the elevator car, floor nodes, and sensors.
class Node {
    // Protected property to store the unique ID of the node.
    // 'protected' allows child classes to access it directly.
    protected int $id; 

    // Constructor for the Node class.
    // Initializes a new Node object with a given ID.
    public function __construct(int $id) {
        $this->id = $id;
    }

    // Public method to retrieve the ID of the node.
    // Encapsulation: provides controlled access to the private/protected 'id' property.
    public function getID(): int {
        return $this->id;
    }

    // You might add other common node functionalities here later,
    // such as methods for communication or status checks.
}
?>
