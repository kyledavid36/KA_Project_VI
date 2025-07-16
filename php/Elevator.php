<?php
// FILE: Elevator.php

require_once 'Node.php';
require_once 'InvalidFloorException.php';

// The Elevator class inherits from the Node class
class Elevator extends Node {
    // private properties demonstrate encapsulation
    private int $currentFloor;
    private int $totalFloors;

    // The constructor calls the parent's constructor
    public function __construct(int $id, int $totalFloors, int $startFloor = 1) {
        parent::__construct($id); // Call Node's constructor
        $this->totalFloors = $totalFloors;
        $this->currentFloor = $startFloor;
    }

    // A public method (part of the public interface)
    public function requestFloor(int $requestedFloor) {
        // Throw a custom exception if input is outside the expected range
        if ($requestedFloor < 1 || $requestedFloor > $this->totalFloors) {
            throw new InvalidFloorException("Floor $requestedFloor does not exist. This building only has floors 1-" . $this->totalFloors . ".");
        }
        $this->currentFloor = $requestedFloor;
    }

    public function getCurrentFloor(): int {
        return $this->currentFloor;
    }
}
?>