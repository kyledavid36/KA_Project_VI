<?php
// FILE: Node.php

class Node {
    // 'protected' allows child classes to access this property
    protected int $nodeID;

    public function __construct(int $id) {
        $this->nodeID = $id;
    }

    public function getNodeID(): int {
        return $this->nodeID;
    }
}
?>