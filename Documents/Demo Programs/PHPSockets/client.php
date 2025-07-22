<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
    <title>Client</title>
</head>
<body>
    <div class="container mt-5">
        <h1>Register new account form</h1>
        <p>Please fill in the form below to send data to the server.</p>
        <form method="POST" action="client.php">
        <div class="form-group">
            <label for="name">Name:</label>
            <input type="text" id="name" name="name" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="ID">ID:</label>
            <input type="text" id="ID" name="ID" required class="form-control">
        </div>

        <button type="submit" class="btn btn-primary">Submit</button>
        </form>
    </div>
    

    <?php 

    // Check if the form is submitted
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Get the form data
        $name = $_POST['name'];
        $email = $_POST['email'];
        $ID = $_POST['ID'];

        $entry = array($ID, $name, $email);                         // Entry data to submit to server

        // Connect to the server using a socket 
        $host = '127.0.0.1';                                        // Server IP address
        $port = 5000;                                               // Server port

        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);       // In Linux need to install the sockets extension (sudo apt-get install php-sockets) and restart (sudo systemctl restart apache2)
        if(!$socket) {                                                // In Windows (In XAMPP Apache click 'Config' button and uncomment 'extension=sockets' to enable the extension then restart Apache)
            echo "Error: could not create socket";
        } else {
            $result = socket_connect($socket, $host, $port);
            if(!$result) {
                echo "Error: could not connect to server";
            } else {
                // Send the data to the server
                $data = json_encode($entry);                    // Needed? Could be used to fill in the fields if hit 'retrieve' button instead of 'submit' button in the form
                socket_write($socket, $data, strlen($data));

                // Get the response from the server - for now it echos the submitted data back
                $response = socket_read($socket, 2048);
                if ($response === false) {
                    echo "Error: could not read response from server";
                } else {
                    echo "<div class='container mt-5'>";
                    echo "<h2>Server Response</h2>";
                    echo "<p>$response</p>";
                    echo "</div>";
                }

                // Close the socket
                socket_close($socket);
                echo "<p>Data sent to server successfully.</p>";
            }
        }
    }
    ?>
</body>
</html>