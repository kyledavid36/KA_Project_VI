<?php
// Filename: server.php
// Description: A robust WebSocket server in PHP using the full Ratchet/ReactPHP stack.

// 1. Require the Composer autoloader
require dirname(__FILE__) . '/vendor/autoload.php';

// Import all the necessary classes from the Ratchet and ReactPHP libraries
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

/**
 * This is the same application logic class as before. No changes are needed here.
 * It defines what happens when a client connects, sends a message, or disconnects.
 */
class AudioStreamServer implements MessageComponentInterface {
    protected $clients;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
        echo "PHP Audio WebSocket server starting...\n";
    }

    public function onOpen(ConnectionInterface $conn) {
        $fileName = "emergency_call_" . time() . "_" . $conn->resourceId . ".webm";
        $fileStream = fopen($fileName, 'a');

        $this->clients->attach($conn, ['fileStream' => $fileStream, 'fileName' => $fileName]);
        echo "Client connected! ({$conn->resourceId}). Recording audio to {$fileName}\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $data = $this->clients[$from];
        if ($data && is_resource($data['fileStream'])) {
             fwrite($data['fileStream'], $msg);
             echo "Received and saved an audio chunk of size: " . strlen($msg) . " bytes from client {$from->resourceId}\n";
        }
    }

    public function onClose(ConnectionInterface $conn) {
        if (isset($this->clients[$conn])) {
            $data = $this->clients[$conn];
            if (is_resource($data['fileStream'])) {
                fclose($data['fileStream']);
            }
            $fileName = $data['fileName'];
            $this->clients->detach($conn);
            echo "Client {$conn->resourceId} has disconnected. Saved call to {$fileName}\n";
        }
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";
        if (isset($this->clients[$conn])) {
            $data = $this->clients[$conn];
             if (is_resource($data['fileStream'])) {
                fclose($data['fileStream']);
            }
        }
        $conn->close();
    }
}

// --- Server Initialization (Robust Method) ---

// Create the server, wrapping our application logic in the WebSocket protocol handler...
$wsServer = new WsServer(
    new AudioStreamServer()
);

// ...which in turn is wrapped by the HTTP protocol handler.
$httpServer = new HttpServer($wsServer);

// Create the final I/O server that will listen on the socket.
// We bind it to '0.0.0.0' to allow connections from any IP on the network.
$server = IoServer::factory($httpServer, 8080, '0.0.0.0');

echo "Server listening on 0.0.0.0:8080\n";

// Run the server's event loop!
$server->run();