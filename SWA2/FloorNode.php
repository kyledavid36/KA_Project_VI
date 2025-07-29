<?php
// FILE: FloorNode.php
// This file defines the FloorNode class, representing a specific floor in the building.
// It extends the base Node class and provides functionality related to a floor's attributes and actions.
require_once 'Node.php'; // Ensures the base Node class definition is available.

class FloorNode extends Node {
    // Private property to store the floor number this node represents.
    // Encapsulation: ensures direct access to 'floorNumber' is restricted.
    private int $floorNumber;

    // Constructor for the FloorNode class.
    // Initializes a new FloorNode with a unique ID (from parent Node) and its floor number.
    public function __construct(int $id, int $floorNumber) {
        parent::__construct($id); // Calls the constructor of the parent Node class to set the ID.
        $this->floorNumber = $floorNumber;
    }

    // Public method to retrieve the floor number associated with this node.
    public function getFloorNumber(): int {
        return $this->floorNumber;
    }

    // Public method to simulate an elevator call from this floor.
    // In a real system, this method would trigger communication with the elevator control system.
    public function callElevator(int $targetFloor): string {
        return "Floor " . $this->floorNumber . " node (ID: " . $this->getID() . ") requesting elevator to floor " . $targetFloor . ".";
    }
}
?>
