<?php
// Filename: server.php
// Description: A WebSocket server in PHP using the Ratchet library to receive audio streams.

// 1. Require the Composer autoloader
require dirname(__FILE__) . '/vendor/autoload.php';

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

/**
 * Our custom WebSocket application class to handle audio streaming.
 * This class defines what happens when a client connects, sends a message, or disconnects.
 */
class AudioStreamServer implements MessageComponentInterface {
    
    // We'll use a SplObjectStorage to associate connections with their file resources.
    protected $clients;

    public function __construct() {
        // SplObjectStorage is an efficient way to store objects as keys.
        $this->clients = new \SplObjectStorage; 
        echo "PHP Audio WebSocket server started on port 8080\n";
    }

    /**
     * Called when a new client has connected to the server.
     * @param ConnectionInterface $conn The connection object for the new client.
     */
    public function onOpen(ConnectionInterface $conn) {
        // Create a unique filename for this call using a timestamp.
        $fileName = "emergency_call_" . time() . "_" . $conn->resourceId . ".webm";
        $fileStream = fopen($fileName, 'a'); // Open file in append mode

        // Store the new connection and its associated file stream
        $this->clients->attach($conn, ['fileStream' => $fileStream, 'fileName' => $fileName]);

        echo "New client connected! ({$conn->resourceId}). Recording audio to {$fileName}\n";
    }

    /**
     * Called when a message is received from a client.
     * @param ConnectionInterface $from The connection from which the message came.
     * @param string $msg The message received. In our case, this is a binary audio chunk.
     */
    public function onMessage(ConnectionInterface $from, $msg) {
        // Get the file stream associated with this connection
        $data = $this->clients[$from];
        $fileStream = $data['fileStream'];

        // Write the received audio chunk directly to the file.
        fwrite($fileStream, $msg);

        echo "Received and saved an audio chunk of size: " . strlen($msg) . " bytes from client {$from->resourceId}\n";
    }

    /**
     * Called when a client disconnects.
     * @param ConnectionInterface $conn The connection that is closing.
     */
    public function onClose(ConnectionInterface $conn) {
        // Get the data associated with the disconnecting client.
        $data = $this->clients[$conn];
        $fileStream = $data['fileStream'];
        $fileName = $data['fileName'];

        // Close the file stream to save the file.
        fclose($fileStream);

        // The connection is closed, remove it from the list of clients.
        $this->clients->detach($conn);

        echo "Client {$conn->resourceId} has disconnected. Saved call to {$fileName}\n";
    }

    /**
     * Called when an error occurs on a connection.
     * @param ConnectionInterface $conn The connection that experienced the error.
     * @param \Exception $e The exception that was thrown.
     */
    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";

        // It's good practice to also close the file and the connection on error.
        if (isset($this->clients[$conn])) {
            $data = $this->clients[$conn];
            fclose($data['fileStream']);
        }
        $conn->close();
    }
}


// --- Server Initialization ---
// Create a new Ratchet application that will listen on port 8080.
$app = new Ratchet\App('0.0.0.0', 8080);

// Set up the WebSocket route. The first parameter is the URL path.
// The second is our application logic class.
// The third specifies allowed IP addresses ('*' means all).
$app->route('/', new AudioStreamServer(), ['*']);

// Run the application!
$app->run();

