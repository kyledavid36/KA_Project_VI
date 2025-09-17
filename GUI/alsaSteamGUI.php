<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    // Not logged in, redirect to login
    header('Location: ../php/GUI_login.php');
    exit;
}
?>


<!-- ===========================================================
  FILE: alsaSteamGUI.php
  TITLE: Steampunk Elevator GUI Interface
  AUTHOR: Alan Hpm and Kyle Dick
  PURPOSE:
    This HTML file serves as the main graphical user interface (GUI)
    for the elevator control system. It allows authenticated users to:
      - Request elevator floors
      - Open/close doors
      - Enter maintenance and Sabbath modes
      - Trigger emergency calls
    It connects with backend PHP scripts (updateFloor.php, fetchFloor.php)
    and communicates with Raspberry Pi CAN system and audio triggers.
  DEPENDENCIES:
    - ../php/updateFloor.php (POST floor requests)
    - ../php/fetchFloor.php (GET current floor)
    - ../php/GUI_login.php (Session login)
    - audio/*.mp3 files for cues
    - maintenance.html, changelog.html, SteamGUI.html
    - Python/C++ backend trigger scripts (maintenance mode, emergency)
=========================================================== -->



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Elevator GUI</title>

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
    />
    <link
        href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600&display=swap"
        rel="stylesheet"
    />
    

    <style>
        /* ==========================================================================
           BUTTON STATE AND INTERACTION STYLES
           ========================================================================== */
        
        /* Style for a control button when it is logically "locked" */
        .control-button.locked {
            border-color: red !important;
        }

        /* A class to provide visual feedback when a button is clicked */
        .lit-up {
            transform: scale(1.1) !important; /* Makes the button temporarily larger */
            transition: all 0.3s ease;
        }

        /* A subtle scaling effect when the user hovers over a button */
        .floor-button:hover:not(.lit-up),
        .control-button:hover:not(.lit-up) {
            transform: scale(1.05);
        }

        /* ==========================================================================
           GENERAL PAGE THEME
           ========================================================================== */

        body {
            background: url('Images/metal.jpg') no-repeat center center fixed;
            background-size: cover;
            font-family: 'Cinzel', serif; /* The primary "steampunk" font */
            color: #f0e6d2;
        }

        h1 {
            text-align: center;
            margin-top: 30px;
            margin-bottom: 30px;
            font-size: 48px;
            text-shadow: 2px 2px #000;
        }

        main {
            text-align: center;
            margin-top: 40px;
        }

        /* ==========================================================================
           LAYOUT STYLES
           ========================================================================== */
        
        /* The main flex container that creates the two-column layout */
        .main-container {
            display: flex;
            flex-wrap: wrap; 
            justify-content: center;
            align-items: flex-start; /* This aligns the tops of the panel boxes */
            gap: 50px; /* Space between the left and right panels */
        }
        
        /* Shared style for the left and right columns */
        .left-panel, .right-panel {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        
        /* Added top padding for vertical content alignment */
        .left-panel {
            padding-top: 20px;
        }
        
        /* A decorative border and background for the right-side control panel */
        .right-panel {
            border: 4px double  rgba(0,0,0,0);
            border-radius: 15px;
            padding: 20px;
            background-color: rgba(0,0,0,0);
        }
        
        /* A CSS Grid container for the 6 control buttons on the right panel */
        .control-grid {
            display: grid;
            grid-template-columns: repeat(2, auto); /* Creates a 2-column layout */
            gap: 15px;
        }
        
        /* ==========================================================================
           COMPONENT STYLES
           ========================================================================== */

        /* The black status display screen on the right panel */
        #function-display {
            background-color: black;
            color: red;
            border: 2px solid #4b3621;
            border-radius: 10px;
            padding: 10px;
            min-height: 48px;
            width: 100%; 
            margin-bottom: 20px;
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }

        /* The black floor indicator screen on the left panel */
        #current-floor {
            background-color: black;
            color: red;
            border: 2px solid black;
            border-radius: 10px;
            padding: 10px;
            font-weight: bold;
            font-size: 24px;
            max-width: 80px; 
            margin: 0 auto 20px;
            text-align: center;
            text-shadow: 1px 1px #440000;
        }

        /* Styles for the rectangular floor buttons */
        .floor-button {
            width: 100px;
            font-size: 24px;
            margin: 10px auto;
            padding: 15px;
            background: radial-gradient(circle at 30% 30%, #c4a35a, #7a5c1d);
            color: #fff;
            border: 4px solid #4b3621;
            border-radius: 12px;
            box-shadow: none;
            display: block;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        /* Shared styles for all circular control buttons */
        .control-button {
            font-size: 28px; /* Sized for emojis to render well */
            white-space: nowrap;
            line-height: 1;
            text-align: center;
            vertical-align: middle;
            margin: 0; 
            padding: 15px;
            background: radial-gradient(circle at 30% 30%, #c4a35a, #7a5c1d);
            color: #fff;
            border: 4px solid #4b3621;
            border-radius: 50%; /* This creates the circular shape */
            box-shadow: none;
            width: 80px;
            height: 80px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }
        
        /* Specific style to make the emergency button red */
        #emergency-call-button {
            background: radial-gradient(circle, #b71c1c, #7f0000);
            border-color: #660000;
        }

        /* Position the logout button in the bottom-right corner */
        #logout-container {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 9999;
        }

        /* Adjustments to match floor-button size but keep it square */
        #logout-button {
            width: 60px;
            height: 60px;
            font-size: 10px;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        
        /* ==========================================================================
           ANIMATION STYLES
           ========================================================================== */

        /* A class added via JavaScript to indicate an active, ongoing process */
        #emergency-call-button.calling,
        #mic-button.listening {
            animation: pulse-green 1.5s infinite;
        }
        
        /* The definition of the pulsing green border animation */
        @keyframes pulse-green {
            0% { border-color: #00ff00; }
            50% { border-color: #8aff8a; }
            100% { border-color: #00ff00; }
        }
    </style>
</head>
<body>
    <div class="container">
        <main>
            <h1>Elevator Control</h1>
            <div class="main-container">
                <div class="left-panel">
                    <input type="text" id="current-floor" readonly value="3" />
                    
                    <div>
                        <button class="floor-button">3</button>
                        <button class="floor-button">2</button>
                        <button class="floor-button">1</button>
                    </div>
                </div>
                <div class="right-panel">
                    <div id="function-display"></div>
                    <div class="control-grid">
                        <button class="control-button" id="open-door">≪≫</button>
                        <button class="control-button locked" id="close-door">≫≪</button>
                        <button class="control-button" id="maintenance-button" title="Maintenance">⚙️</button>
                        <button class="control-button" id="log-button" title="Log">📜</button>
                        <button class="control-button" id="mic-button" title="Sabbath">♾️</button>
                        <button class="control-button" id="emergency-call-button" title="Emergency">📞</button>
                    </div>
                </div>
            </div>
        </main>
    </div>
            <!-- Logout Button in Bottom-Right Corner -->
        <div id="logout-container">
            <button class="floor-button" id="logout-button" title="Logout">Logout</button>
        </div>


    <audio id="ding-sound" src="audio/ding.mp3" preload="auto"></audio>
    <audio id="door-open" src="audio/ElevatorOpeningFinal.mp3" preload="auto"></audio>
    <audio id="door-click" src="audio/ElevatorButtonFinal.mp3" preload="auto"></audio>
    <audio id="door-close" src="audio/ElevatorOpeningFinal.mp3" preload="auto"></audio>
    <audio id="screech-sound" src="audio/screeching.mp3" preload="auto"></audio>
    <audio id="emergency-sound" src="audio/emergencyAudio.mp3" preload="auto"></audio>
    <audio id="floor1-sound" src="audio/Floor 1.mp3" preload="auto"></audio>
    <audio id="floor2-sound" src="audio/Floor 2.mp3" preload="auto"></audio>
    <audio id="floor3-sound" src="audio/Floor 3.mp3" preload="auto"></audio>
    <audio id="shaft-exit-sound" src="audio/shaftExit.mp3" preload="auto"></audio>

    <script>
        /* =============================================================================
           JAVASCRIPT LOGIC FOR ELEVATOR GUI INTERFACE
           -----------------------------------------------------------------------------
           FILE: alsaSteamGUI.html (JavaScript Section)
           PURPOSE:
             - Handles user interactions and audio feedback for elevator operation
             - Sends floor requests to backend (updateFloor.php)
             - Controls Maintenance Mode and Sabbath Mode logic
             - Interfaces with CAN system and emergency audio trigger
        ============================================================================= */

        /* ============================================================================
           SECTION 1: DOM ELEMENT CACHING & GLOBAL STATE
           - Stores references to all interactive DOM elements
           - Declares audio elements and mode state flags
        ============================================================================ */
        const floorDisplay = document.getElementById('current-floor');
        const openButton = document.getElementById('open-door');
        const closeButton = document.getElementById('close-door');
        const logButton = document.getElementById('log-button');
        const micButton = document.getElementById('mic-button');
        const maintenanceButton = document.getElementById('maintenance-button');
        const allButtons = document.querySelectorAll('button');
        const floorButtons = document.querySelectorAll('.floor-button');
        const emergencyCallButton = document.getElementById('emergency-call-button');
        const functionDisplay = document.getElementById('function-display');
        
        const dingSound = document.getElementById('ding-sound');
        const openSound = document.getElementById('door-open');
        const clickSound = document.getElementById('door-click');
        const closeSound = document.getElementById('door-close');
        const screechSound = document.getElementById('screech-sound');
        const emergencySound = document.getElementById('emergency-sound');
        const floor1Sound = document.getElementById('floor1-sound');
        const floor2Sound = document.getElementById('floor2-sound');
        const floor3Sound = document.getElementById('floor3-sound');
        const shaftExitSound = document.getElementById('shaft-exit-sound');

        const urlParams = new URLSearchParams(window.location.search);
        const isMaintenanceMode = urlParams.get('mode') === 'maintenance';

        let isSabbathModeActive = false;
        let sabbathLoopTimeout;

        /* ============================================================================
           SECTION 2: EVENT LISTENERS
           ----------------------------------------------------------------------------
           A. Generic button lighting and click sound (excluding mic/emergency buttons)
        ============================================================================ */
        allButtons.forEach(button => {
            if (button.id === 'emergency-call-button' || button.id === 'mic-button') return;

            button.addEventListener('click', () => {
                const isDoorButton = button.id === 'open-door' || button.id === 'close-door';
                if (!isMaintenanceMode || !isDoorButton) {
                    if (clickSound) { clickSound.currentTime = 0; clickSound.play(); }
                }
                button.classList.add('lit-up');
                setTimeout(() => { button.classList.remove('lit-up'); }, 500);
            });
        });

        // --- B. Floor Button Request Handling ---
        let floorRequestInProgress = false;

        floorButtons.forEach(button => {
            button.addEventListener('click', () => {
                if (floorRequestInProgress) return;
                floorRequestInProgress = true;

                const floorNumber = button.innerText.trim();
                floorDisplay.value = floorNumber;

                fetch('../php/updateFloor.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'floor=' + encodeURIComponent(floorNumber)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        console.log("✅ Floor updated in DB: " + data.floor);
                    } else {
                        console.error("❌ Floor update failed: " + (data.message || 'No error message'));
                    }
                })
                .catch(error => {
                    console.error("❌ Error sending request:", error);
                })
                .finally(() => {
                    setTimeout(() => { floorRequestInProgress = false; }, 2500);
                });

                // Audio simulation for elevator movement
                setTimeout(() => { if (screechSound) screechSound.currentTime = 0, screechSound.play(); }, 200);
                setTimeout(() => { if (dingSound) dingSound.currentTime = 0, dingSound.play(); }, 4800);
                setTimeout(() => {
                    if (floorNumber === '1' && floor1Sound) floor1Sound.play();
                    else if (floorNumber === '2' && floor2Sound) floor2Sound.play();
                    else if (floorNumber === '3' && floor3Sound) floor3Sound.play();
                }, 5200);
            });
        });

        // --- C. Door Controls ---
        openButton.addEventListener('click', () => {
            if (isMaintenanceMode) {
                shaftExitSound?.play();
            } else {
                setTimeout(() => { openSound?.play(); }, 1500);
                setTimeout(() => {
                    const currentFloor = floorDisplay.value.trim();
                    if (currentFloor === '1') window.location.href = 'floor1GUI.html';
                    else if (currentFloor === '2') window.location.href = 'floor2GUI.html';
                    else if (currentFloor === '3') window.location.href = 'floor3GUI.html';
                }, 5250);
            }
        });

        closeButton.addEventListener('click', () => {
            if (isMaintenanceMode) {
                shaftExitSound?.play();
            } else {
                setTimeout(() => { closeSound?.play(); }, 500);
                floorButtons.forEach(btn => (btn.disabled = false));
                micButton.disabled = false;
                closeButton.classList.remove('locked');
            }
        });

        // --- D. Maintenance Mode Toggle ---
        maintenanceButton.addEventListener('click', () => {
            if (isMaintenanceMode) {
                functionDisplay.textContent = 'EXITING...';
                setTimeout(() => { window.location.href = 'alsaSteamGUI.html'; }, 1000);
            } else {
                window.location.href = 'maintenance.html';
            }
        });

        // --- E. Log Button ---
        logButton.addEventListener('click', () => { window.location.href = '../changelog.html'; });

        /* ============================================================================
           FUNCTION: sabbathLoop()
           ----------------------------------------------------------------------------
           - Recursively loops between floors simulating automatic Sabbath Mode
           - Updates visual and audio indicators accordingly
        ============================================================================ */
        function sabbathLoop(targetFloor, direction) {
            if (!isSabbathModeActive) {
                functionDisplay.textContent = 'EXITING...';
                setTimeout(() => { window.location.reload(); }, 1500);
                return;
            }

            functionDisplay.textContent = `GOING TO ${targetFloor}`;
            screechSound?.play();
            setTimeout(() => { dingSound?.play(); }, 4800);
            setTimeout(() => {
                floorDisplay.value = targetFloor;
                functionDisplay.textContent = `FLOOR ${targetFloor}`;
                if (targetFloor === 1) floor1Sound?.play();
                else if (targetFloor === 2) floor2Sound?.play();
                else if (targetFloor === 3) floor3Sound?.play();
            }, 5200);

            sabbathLoopTimeout = setTimeout(() => {
                let nextFloor = direction === 'down' ? (targetFloor > 1 ? targetFloor - 1 : 2) : (targetFloor < 3 ? targetFloor + 1 : 2);
                let nextDirection = (targetFloor === 1) ? 'up' : (targetFloor === 3) ? 'down' : direction;
                sabbathLoop(nextFloor, nextDirection);
            }, 10200);
        }

        // --- F. Sabbath Mode Mic Button ---
        micButton.addEventListener('click', () => {
            clickSound?.play();
            isSabbathModeActive = !isSabbathModeActive;

            if (isSabbathModeActive) {
                functionDisplay.textContent = 'SABBATH MODE';
                micButton.classList.add('listening');
                allButtons.forEach(btn => {
                    if (btn.id !== 'mic-button' && btn.id !== 'emergency-call-button') {
                        btn.disabled = true;
                        btn.style.cursor = 'not-allowed';
                    }
                });
                setTimeout(() => sabbathLoop(2, 'down'), 1500);
            } else {
                functionDisplay.textContent = 'FINISHING MOVE...';
                micButton.classList.remove('listening');
            }
        });

        // --- G. Emergency Call Button ---
        emergencyCallButton.addEventListener('click', () => {
            emergencySound?.play();
            const TRIGGER_URL = 'http://localhost:8080/start_emergency';
            functionDisplay.textContent = 'CALLING...';
            emergencyCallButton.classList.add('calling');
            emergencyCallButton.disabled = true;
            fetch(TRIGGER_URL, { mode: 'no-cors' }).catch(err => console.log('Trigger error (expected):', err));
        });

        emergencySound?.addEventListener('ended', () => {
            functionDisplay.textContent = 'CALL ESTABLISHED';
            functionDisplay.style.color = '#76ff03';
        });

        /* ============================================================================
           SECTION 3: PAGE INITIALIZATION ON LOAD
           - Checks if in maintenance mode and disables/enables controls accordingly
           - Fetches current floor from backend on startup
        ============================================================================ */
        document.addEventListener('DOMContentLoaded', () => {
            if (isMaintenanceMode) {
                functionDisplay.textContent = 'MAINTENANCE MODE';
                allButtons.forEach(btn => {
                    btn.disabled = true;
                    btn.style.cursor = 'not-allowed';
                });
                openButton.disabled = false;
                closeButton.disabled = false;
                maintenanceButton.disabled = false;
                openButton.style.cursor = 'pointer';
                closeButton.style.cursor = 'pointer';
                maintenanceButton.style.cursor = 'pointer';
                closeButton.classList.remove('locked');
            } else {
                floorButtons.forEach(btn => (btn.disabled = true));
                micButton.disabled = true;
                closeButton.classList.add('locked');
            }

            fetch('../php/fetchFloor.php')
                .then(response => response.json())
                .then(data => {
                    const latestFloor = data.floor || '1';
                    floorDisplay.value = latestFloor;
                    console.log("✅ Initialized with DB floor:", latestFloor);
                })
                .catch(error => {
                    console.error("❌ Error fetching floor from DB:", error);
                    floorDisplay.value = '1';
                });
        });

        // Trigger C++ maintenance listener via fetch if in maintenance mode
        window.addEventListener('DOMContentLoaded', () => {
            if (urlParams.get('mode') === 'maintenance') {
                fetch('http://localhost:8090/start')
                    .then(response => response.text())
                    .then(msg => console.log("🛠️ Maintenance mode triggered:", msg))
                    .catch(err => console.error("⚠️ Failed to trigger maintenance mode:", err));
            }
        });

        document.getElementById('logout-button').addEventListener('click', () => {
            window.location.href = '../php/GUI_logout.php';
        });
    </script>


</body>
</html>