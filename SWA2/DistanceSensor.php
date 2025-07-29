<?php
// FILE: DistanceSensor.php
// This file defines the DistanceSensor class, representing a sensor that measures distance.
// This could be used for obstacle detection in the elevator doors or for precise leveling.
// It extends the base Node class and includes an example of static members.
require_once 'Node.php'; // Ensures the base Node class definition is available.

class DistanceSensor extends Node {
    // Private property to store the most recently measured distance.
    private float $currentDistance;
    // Private property defining the threshold below which an obstacle is detected.
    private float $detectionThreshold; 

    // Static property for default units of measurement.
    // 'static' means it belongs to the class itself, not to individual objects.
    public static string $units = "meters";

    // Constructor for the DistanceSensor class.
    // Initializes the sensor with a unique ID (from parent Node) and an optional detection threshold.
    public function __construct(int $id, float $detectionThreshold = 0.5) {
        parent::__construct($id);
        $this->detectionThreshold = $detectionThreshold;
        $this->currentDistance = 0.0; // Initialize current distance to zero.
    }

    // Public method to simulate getting a distance reading from the sensor.
    // In a real system, this would interact with hardware; here, it generates a random value for testing.
    public function getDistance(): float {
        $this->currentDistance = rand(10, 200) / 100.0; // Generates a random float between 0.10 and 2.00 meters.
        return $this->currentDistance;
    }

    // Public method to check if an obstacle is detected based on the current distance and threshold.
    // It first gets a fresh distance reading before checking.
    public function detectObstacle(): bool {
        $this->getDistance(); // Get a fresh reading before checking.
        return $this->currentDistance < $this->detectionThreshold;
    }

    // Static method to convert a distance from the sensor's default units (meters) to centimeters.
    // 'static' means this method can be called directly on the class (e.g., `DistanceSensor::convertToCentimeters()`)
    // without needing an object instance.
    public static function convertToCentimeters(float $distance): float {
        return $distance * 100;
    }
}
?>
