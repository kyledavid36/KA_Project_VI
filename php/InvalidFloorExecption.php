<?php
// FILE: InvalidFloorException.php

class InvalidFloorException extends Exception {
    // You can customize this exception class further if needed,
    // but for the assignment, simply extending the base Exception class is sufficient.
    public function __construct($message = "Invalid floor requested.", $code = 0, Throwable $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}
?>