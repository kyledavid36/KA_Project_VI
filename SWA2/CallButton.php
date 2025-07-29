<?php
// FILE: CallButton.php
// This file defines the CallButton class, representing a button in the elevator system.
// This could be an 'up'/'down' button on a floor or a floor selection button inside the car.
// It inherits common properties from the Node class.
require_once 'Node.php'; // Ensures the base Node class definition is available.

class CallButton extends Node {
    // Private property to store the target floor this button requests.
    private int $targetFloor;
    // Private boolean property to track if the button is currently pressed.
    private bool $isPressed;

    // Constructor for the CallButton class.
    // Initializes the button with a unique ID (from parent Node) and the floor it aims for.
    public function __construct(int $id, int $targetFloor) {
        parent::__construct($id); // Calls the parent Node constructor.
        $this->targetFloor = $targetFloor;
        $this->isPressed = false; // A button is initially not pressed.
    }

    // Public method to simulate pressing the button.
    // Sets the 'isPressed' state to true and returns a confirmation message.
    public function press(): string {
        $this->isPressed = true;
        return "Button ID " . $this->getID() . " (for floor " . $this->targetFloor . ") has been pressed.";
    }

    // Public method to simulate releasing the button.
    // Sets the 'isPressed' state back to false.
    public function release(): void {
        $this->isPressed = false;
    }

    // Public method to get the target floor associated with this button.
    public function getTargetFloor(): int {
        return $this->targetFloor;
    }

    // Public method to check the current pressed state of the button.
    public function isPressed(): bool {
        return $this->isPressed;
    }
}
?>
