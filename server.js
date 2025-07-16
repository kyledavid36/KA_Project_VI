// Filename: server.js
// Description: A WebSocket server to receive audio streams from the browser.
// To run: 
// 1. Install Node.js
// 2. Open a terminal or command prompt in this folder
// 3. Run: npm install ws
// 4. Run: node server.js

const WebSocket = require('ws');
const fs = require('fs');

const PORT = 8080;

// Create a WebSocket server
const wss = new WebSocket.Server({ port: PORT });

console.log(`WebSocket server started on port ${PORT}`);
console.log('Waiting for a connection from the elevator GUI...');

wss.on('connection', (ws) => {
    console.log('Client connected! Receiving emergency audio stream...');
    
    // Create a unique filename for this call using a timestamp
    const fileName = `emergency_call_${Date.now()}.webm`;
    const fileStream = fs.createWriteStream(fileName, { flags: 'a' });

    console.log(`Recording audio to: ${fileName}`);

    // When a message (audio data) is received from the client
    ws.on('message', (message) => {
        // The message is a Blob from the browser, which is received as a Buffer here.
        // We can write it directly to a file.
        fileStream.write(message);
        console.log(`Received and saved an audio chunk of size: ${message.length} bytes.`);
    });

    // When the client disconnects
    ws.on('close', () => {
        console.log('Client disconnected. Audio stream finished.');
        fileStream.end(); // Close the file stream
        console.log(`Saved call to ${fileName}`);
    });

    // Handle potential errors
    ws.on('error', (error) => {
        console.error('WebSocket error:', error);
        fileStream.end();
    });
});