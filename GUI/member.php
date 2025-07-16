<?php
// Start the session
session_start();

// Check if the user is logged in (you'll set this session variable in your login.php)
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    // If not logged in, redirect to the login page
    header('Location: ../login.html'); // Assuming login.html is one directory up
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  
  <title>Select Floor</title>
  
  <link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
    rel="stylesheet"
  />
  
  <link
    href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600&display=swap"
    rel="stylesheet"
  />
  
  <style>
    /* Body styling: background image, font, and text color */
    body {
      background: url('Images/metal.jpg') no-repeat center center fixed;
      background-size: cover;
      font-family: 'Cinzel', serif;
      color: #f0e6d2; /* light tan text color */
    }

    /* Container for the floor selector, centered with padding and styling */
    .floor-selector-container {
      max-width: 400px;
      margin: 5rem auto; /* vertical margin + center horizontally */
      padding: 2rem;
      background-color: rgba(0, 0, 0, 0.85); /* semi-transparent black */
      border: 2px solid #4b3621; /* brown border */
      border-radius: 12px; /* rounded corners */
      box-shadow: 0 0 20px #8b6f47; /* subtle glowing shadow */
      text-align: center;
    }

    /* Heading inside container styled with red text and shadow */
    .floor-selector-container h2 {
      color: red;
      margin-bottom: 1.5rem;
      text-shadow: 1px 1px #440000;
    }

    /* Labels styled as block elements with consistent font and spacing */
    label {
      display: block;
      color: #f0e6d2; /* same tan color */
      font-size: 1.2rem;
      margin-bottom: 0.5rem;
    }

    /* Number input styling: black background, red text, centered text */
    input[type="number"] {
      background-color: black;
      color: red;
      border: 2px solid grey;
      border-radius: 8px;
      padding: 0.5rem;
      width: 150px;
      text-align: center;
      margin: 0 auto; /* center horizontally */
      font-size: 1.2rem;
    }

    /* Remove default spinner arrows in WebKit browsers */
    input[type=number]::-webkit-outer-spin-button,
    input[type=number]::-webkit-inner-spin-button {
      -webkit-appearance: none;
      margin: 0;
    }

    /* Remove default spinner arrows in Firefox */
    input[type=number] {
      -moz-appearance: textfield;
    }

    /* Styling for the "Go" button */
    .go-button {
      margin-top: 1.5rem;
      font-size: 20px;
      padding: 10px 20px;
      background: radial-gradient(circle at 30% 30%, #c4a35a, #7a5c1d); /* gold gradient */
      color: #fff; /* white text */
      border: 4px solid #4b3621; /* brown border */
      border-radius: 12px;
      box-shadow: 0 0 10px #8b6f47, inset 0 0 5px #000; /* outer glow and inset shadow */
      transition: all 0.3s ease; /* smooth transition on hover */
    }

    /* Button hover effect: scale up and glowing shadow */
    .go-button:hover {
      transform: scale(1.05);
      box-shadow: 0 0 20px #f0c97d, inset 0 0 10px #000;
    }

    /* Styling for error message text */
    #errorMsg {
      margin-top: 1rem;
      color: red;
      font-size: 0.9rem;
    }
  </style>
</head>
<body>
  <br>
  <br>
  <br>
  <br>
  <div class="floor-selector-container">
    <label for="floorInput">Enter floor (1–3):</label>

    <input type="number" id="floorInput" placeholder="1-3" min="1" max="3" />
    <br />

    <button class="go-button" onclick="goToFloor()">Go</button>

    <div id="errorMsg"></div>
  </div>

  <script>
    function goToFloor() {
      // Get the input value and trim whitespace
      const floor = document.getElementById('floorInput').value.trim();
      // Reference to error message container
      const errorMsg = document.getElementById('errorMsg');

      // Check if the input matches allowed floor numbers (1, 2, or 3)
      if (floor === '1' || floor === '2' || floor === '3') {
        // Navigate to the corresponding floor GUI page
        window.location.href = `floor${floor}GUI.html`;
      } else {
        // Display error message for invalid input
        errorMsg.textContent = "Enter valid floor number: (1-3)";
      }
    }
  </script>
</body>
</html><?php
// Start the session
session_start();

// Check if the user is logged in (you'll set this session variable in your login.php)
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    // If not logged in, redirect to the login page
    header('Location: ../login.html'); // Assuming login.html is one directory up
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  
  <title>Select Floor</title>
  
  <link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
    rel="stylesheet"
  />
  
  <link
    href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600&display=swap"
    rel="stylesheet"
  />
  
  <style>
    /* Body styling: background image, font, and text color */
    body {
      background: url('Images/metal.jpg') no-repeat center center fixed;
      background-size: cover;
      font-family: 'Cinzel', serif;
      color: #f0e6d2; /* light tan text color */
    }

    /* Container for the floor selector, centered with padding and styling */
    .floor-selector-container {
      max-width: 400px;
      margin: 5rem auto; /* vertical margin + center horizontally */
      padding: 2rem;
      background-color: rgba(0, 0, 0, 0.85); /* semi-transparent black */
      border: 2px solid #4b3621; /* brown border */
      border-radius: 12px; /* rounded corners */
      box-shadow: 0 0 20px #8b6f47; /* subtle glowing shadow */
      text-align: center;
    }

    /* Heading inside container styled with red text and shadow */
    .floor-selector-container h2 {
      color: red;
      margin-bottom: 1.5rem;
      text-shadow: 1px 1px #440000;
    }

    /* Labels styled as block elements with consistent font and spacing */
    label {
      display: block;
      color: #f0e6d2; /* same tan color */
      font-size: 1.2rem;
      margin-bottom: 0.5rem;
    }

    /* Number input styling: black background, red text, centered text */
    input[type="number"] {
      background-color: black;
      color: red;
      border: 2px solid grey;
      border-radius: 8px;
      padding: 0.5rem;
      width: 150px;
      text-align: center;
      margin: 0 auto; /* center horizontally */
      font-size: 1.2rem;
    }

    /* Remove default spinner arrows in WebKit browsers */
    input[type=number]::-webkit-outer-spin-button,
    input[type=number]::-webkit-inner-spin-button {
      -webkit-appearance: none;
      margin: 0;
    }

    /* Remove default spinner arrows in Firefox */
    input[type=number] {
      -moz-appearance: textfield;
    }

    /* Styling for the "Go" button */
    .go-button {
      margin-top: 1.5rem;
      font-size: 20px;
      padding: 10px 20px;
      background: radial-gradient(circle at 30% 30%, #c4a35a, #7a5c1d); /* gold gradient */
      color: #fff; /* white text */
      border: 4px solid #4b3621; /* brown border */
      border-radius: 12px;
      box-shadow: 0 0 10px #8b6f47, inset 0 0 5px #000; /* outer glow and inset shadow */
      transition: all 0.3s ease; /* smooth transition on hover */
    }

    /* Button hover effect: scale up and glowing shadow */
    .go-button:hover {
      transform: scale(1.05);
      box-shadow: 0 0 20px #f0c97d, inset 0 0 10px #000;
    }

    /* Styling for error message text */
    #errorMsg {
      margin-top: 1rem;
      color: red;
      font-size: 0.9rem;
    }
  </style>
</head>
<body>
  <br>
  <br>
  <br>
  <br>
  <div class="floor-selector-container">
    <label for="floorInput">Enter floor (1–3):</label>

    <input type="number" id="floorInput" placeholder="1-3" min="1" max="3" />
    <br />

    <button class="go-button" onclick="goToFloor()">Go</button>

    <div id="errorMsg"></div>
  </div>

  <script>
    function goToFloor() {
      // Get the input value and trim whitespace
      const floor = document.getElementById('floorInput').value.trim();
      // Reference to error message container
      const errorMsg = document.getElementById('errorMsg');

      // Check if the input matches allowed floor numbers (1, 2, or 3)
      if (floor === '1' || floor === '2' || floor === '3') {
        // Navigate to the corresponding floor GUI page
        window.location.href = `floor${floor}GUI.html`;
      } else {
        // Display error message for invalid input
        errorMsg.textContent = "Enter valid floor number: (1-3)";
      }
    }
  </script>
</body>
</html>