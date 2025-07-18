<?php
// FILE: Elevator.php
// This file defines a class representing an Elevator unit in the system.
// It extends the Node class and uses encapsulation, inheritance, and custom exceptions.

require_once 'Node.php';                // Base class that Elevator inherits from
require_once 'InvalidFloorException.php'; // Custom exception for invalid floor requests

// === ELEVATOR CLASS DEFINITION ===
class Elevator extends Node {
    // Declare private member variables (encapsulation)
    private int $currentFloor;   // Tracks which floor the elevator is on
    private int $totalFloors;    // Total number of floors the building has

    // === CONSTRUCTOR ===
    public function __construct(int $id, int $totalFloors, int $startFloor = 1) {
        parent::__construct($id); // Call the parent Node class constructor with the given node ID
        $this->totalFloors = $totalFloors;
        $this->currentFloor = $startFloor;
    }

    // === METHOD: requestFloor() ===
    // Changes the elevator’s target floor if the request is valid.
    // Throws a custom exception if the floor number is out of bounds.
    public function requestFloor(int $requestedFloor) {
        if ($requestedFloor < 1 || $requestedFloor > $this->totalFloors) {
            throw new InvalidFloorException(
                "Floor $requestedFloor does not exist. This building only has floors 1-" . $this->totalFloors . "."
            );
        }

        // If floor is valid, update the current floor
        $this->currentFloor = $requestedFloor;
    }

    // === METHOD: getCurrentFloor() ===
    // Returns the current floor the elevator is on
    public function getCurrentFloor(): int {
        return $this->currentFloor;
    }
}
?>
