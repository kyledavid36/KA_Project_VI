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
        /* ========================================================================
            BUTTON STATE AND INTERACTION STYLES
        ======================================================================== */

        /* Highlight a control button when it is logically "locked" */
        .control-button.locked {
            border-color: red !important;
        }

        /* Visual feedback for a button press (lit-up) with red glow */
        .lit-up {
            transform: scale(1.15) !important; /* Pop the button slightly */
            transition: all 0.1s ease;        /* Quick animation */
            z-index: 9999;                    /* Bring button above neighbors */

            /* Strong red glow using multiple box-shadows */
            box-shadow:
                0 0 40px 15px rgba(255, 0, 0, 1),
                0 0 60px 30px rgba(255, 0, 0, 0.7);
        }

        /* Gentle hover effect for non-lit buttons */
        .floor-button:hover:not(.lit-up),
        .control-button:hover:not(.lit-up) {
            transform: scale(1.05);
        }

        /* Emergency button: base color (red metallic) */
        #emergency-call-button {
            background: radial-gradient(circle, #b71c1c, #7f0000);
            border-color: #660000;
        }

        /* Pulsing green glow when emergency button is in "calling" state */
        #emergency-call-button.calling {
            animation: pulse-green 1.5s infinite;
        }

        /* Green pulse animation for emergency button */
        @keyframes pulse-green {
            0%   { box-shadow: 0 0 10px 2px #00ff00; }
            50%  { box-shadow: 0 0 20px 4px #8aff8a; }
            100% { box-shadow: 0 0 10px 2px #00ff00; }
        }

        /* ========================================================================
            PAGE BACKGROUND & THEME
        ======================================================================== */

        body {
            /* Elevator-themed background image */
            background: url('Images/elevator.png') no-repeat center center fixed;
            background-size: cover;
            font-family: 'Cinzel', serif; /* Steampunk/silver theme font */
            color: #f0f0f0;
            margin: 0;
            height: 100vh;
        }

        /* Main page title with silver metallic gradient text */
        h1 {
            text-align: center;
            margin-top: 120px;
            margin-bottom: 50px;
            font-size: 52px;
            font-family: 'Cinzel', serif;

            /* Brighter metallic gradient */
            background: linear-gradient(
                180deg,
                #f8f8f8 0%,  /* top highlight */
                #dcdcdc 30%, /* light silver */
                #a0a0a0 70%, /* darker steel */
                #c0c0c0 100% /* base reflection */
            );
            -webkit-background-clip: text;
            -webkit-text-fill-color: #eaeaea; /* solid color to keep edges crisp */

            /* Outline / Border effect for better contrast */
            text-shadow:
                0 0 6px rgba(255,255,255,0.7),  /* subtle glow */
                0 0 12px rgba(200,200,200,0.5), /* softer outer glow */
                -1px -1px 0 #222, /* dark gray border top-left */
                1px -1px 0 #222,  /* dark gray border top-right */
                -1px 1px 0 #222,  /* dark gray border bottom-left */
                1px 1px 0 #222;   /* dark gray border bottom-right */
        }



        main {
            text-align: center;
        }

        /* Layout for two panels (floors on left, controls on right) */
        .main-container {
            display: flex;
            justify-content: center;
            align-items: flex-start;  
            gap: 80px;            /* Space between left/right panels */
            min-height: 65vh;  
        }

        /* Panels use vertical stacking */
        .left-panel, .right-panel {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        /* ========================================================================
            FLOOR DISPLAY & BUTTONS
        ======================================================================== */

        /* Digital floor indicator (left panel) */
        #current-floor {
            background-color: black;
            color: red;
            border: 2px solid black;
            border-radius: 10px;
            padding: 10px;
            font-weight: bold;
            font-size: 24px;
            max-width: 80px;  
            margin-bottom: 25px;
            text-align: center;
            text-shadow: 1px 1px #440000;
        }

        /* Vertical stack of floor buttons */
        .floor-panel {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        /* Metallic style for all buttons (floor + control) */
        .floor-button,
        .control-button {
            font-weight: bold;
            color: #222;
            border: 2px solid #888;
            border-radius: 12px;
            text-shadow: 0 1px 1px #fff;

            /* Brushed steel metallic background */
            background: 
                linear-gradient(145deg, #f2f2f2, #c1c1c1 40%, #8a8a8a 70%, #d9d9d9 100%),
                repeating-linear-gradient(45deg, rgba(255,255,255,0.3) 0px, rgba(255,255,255,0.05) 2px, rgba(0,0,0,0.05) 4px);
            background-blend-mode: overlay;

            /* Inner and outer shadows for depth */
            box-shadow: inset 0 1px 2px #fff, inset 0 -2px 4px #777, 0 2px 4px rgba(0,0,0,0.4);
            transition: all 0.3s ease;
        }

        /* Floor buttons: rectangular */
        .floor-button {
            width: 100px;
            font-size: 26px;
            padding: 15px;
        }

        /* Control buttons grid (right panel) */
        .control-grid {
            display: grid;
            grid-template-columns: repeat(2, auto);
            gap: 18px;
            margin-top: 20px;
        }

        /* Control buttons: circular shape */
        .control-button {
            font-size: 26px;
            border-radius: 50%;
            width: 80px;
            height: 80px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Emergency button: red metallic style */
        #emergency-call-button {
            background: linear-gradient(145deg, #ff5555, #bb0000 60%);
            color: white;
            border: 3px solid #660000;
            text-shadow: 0 1px 2px black;
        }

        /* Hover effect for all buttons */
        .floor-button:hover,
        .control-button:hover {
            filter: brightness(1.2);
            transform: scale(1.05);
        }

        /* Digital function display (right panel) */
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
        }

        /* ========================================================================
            LOGOUT BUTTON
        ======================================================================== */
        #logout-container {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 9999;
        }

        #logout-button {
            width: 60px;
            height: 60px;
            font-size: 10px;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }
    </style>
</head>

<body>
    <main>
        <h1>Elevator Control</h1>
        <div class="main-container">
            <div class="left-panel">
                <input type="text" id="current-floor" readonly value="3" />

                <div class="floor-panel">
                    <button class="floor-button">3</button>
                    <button class="floor-button">2</button>
                    <button class="floor-button">1</button>
                </div>
            </div>

            <div class="right-panel">
                <div id="function-display"></div>

                <div class="control-grid">
                    <button class="control-button" id="open-door">≪≫</button>
                    <button class="control-button" id="close-door">≫≪</button>
                    <button class="control-button" id="maintenance-button" title="Maintenance">⚙️</button>
                    <button class="control-button" id="log-button" title="Log">📜</button>
                    <button class="control-button" id="mic-button" title="Sabbath">♾️</button>
                    <button class="control-button" id="emergency-call-button" title="Emergency">📞</button>
                </div>
            </div>
        </div>
    </main>

    <div id="logout-container">
        <button class="floor-button" id="logout-button" title="Logout">Logout</button>
    </div>
</body>
</html>



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
        let isEmergencyCallActive = false; // State variable for emergency call status

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
        - Updates visual and audio indicators for each move
        - Disables manual floor buttons while active
        - Sends DB updates via updateFloor.php with source = "Sabbath Auto-Cycle"
          for Pi backend to trigger real elevator movement
        ============================================================================ */
        function updateFloorInDB(floorNumber, source = 'GUI') {
            return fetch('../php/updateFloor.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'floor=' + encodeURIComponent(floorNumber) +
                    '&source=' + encodeURIComponent(source)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    console.log(`✅ ${source} updated DB to floor: ${data.floor}`);
                } else {
                    console.error("❌ DB update failed:", data.message || 'No error');
                }
            })
            .catch(err => console.error("❌ Fetch error:", err));
        }

            functionDisplay.textContent = `GOING TO ${targetFloor}`;
            screechSound?.play();
            setTimeout(() => { dingSound?.play(); }, 4800);

            setTimeout(() => {
                floorDisplay.value = targetFloor;
                functionDisplay.textContent = `FLOOR ${targetFloor}`;

                // Play the correct floor audio
                if (targetFloor === 1) floor1Sound?.play();
                else if (targetFloor === 2) floor2Sound?.play();
                else if (targetFloor === 3) floor3Sound?.play();

                // **NEW: Update DB so Pi sees the floor change**
                updateFloorInDB(targetFloor, 'Sabbath Auto-Cycle');

            }, 5200);

            sabbathLoopTimeout = setTimeout(() => {
                let nextFloor;
                let nextDirection;

                if (direction === 'down') {
                    if (targetFloor > 1) {
                        nextFloor = targetFloor - 1;
                        nextDirection = 'down';
                    } else {
                        nextFloor = 2;
                        nextDirection = 'up';
                    }
                } else {
                    if (targetFloor < 3) {
                        nextFloor = targetFloor + 1;
                        nextDirection = 'up';
                    } else {
                        nextFloor = 2;
                        nextDirection = 'down';
                    }
                }

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
            const TRIGGER_URL = 'http://localhost:8080/start_emergency';
            const DISCONNECT_URL = 'http://localhost:8080/end_emergency';

            if (!isEmergencyCallActive) {
                // Initiate call
                emergencySound?.play();
                functionDisplay.textContent = 'CALLING...';
                emergencyCallButton.classList.add('calling');
                // emergencyCallButton.disabled = true; // No need to disable; we want it clickable to end

                fetch(TRIGGER_URL, { mode: 'no-cors' })
                    .then(() => {
                        isEmergencyCallActive = true;
                        // emergencyCallButton.disabled = false; // Re-enable button after trigger sent
                        functionDisplay.textContent = 'CALL ESTABLISHED';
                        functionDisplay.style.color = '#76ff03'; // Green for established
                    })
                    .catch(err => {
                        console.error('Trigger error (expected, or actual issue):', err);
                        functionDisplay.textContent = 'CALL FAILED';
                        emergencyCallButton.classList.remove('calling');
                        // emergencyCallButton.disabled = false; // Re-enable on failure
                        functionDisplay.style.color = 'red';
                    });
            } else {
                // Disconnect call
                functionDisplay.textContent = 'DISCONNECTING...';
                emergencyCallButton.classList.remove('calling');
                emergencyCallButton.style.borderColor = ''; // Reset border color
                functionDisplay.style.color = 'red'; // Back to red for disconnected state
                emergencySound?.pause(); // Stop emergency sound if playing
                emergencySound.currentTime = 0; // Reset sound

                fetch(DISCONNECT_URL, { mode: 'no-cors' })
                    .then(() => {
                        isEmergencyCallActive = false;
                        // emergencyCallButton.disabled = false; // Re-enable button after disconnect sent
                        functionDisplay.textContent = 'CALL ENDED'; // Updated text here
                    })
                    .catch(err => {
                        console.error('Disconnect error (expected, or actual issue):', err);
                        functionDisplay.textContent = 'DISCONNECT FAILED';
                        // emergencyCallButton.disabled = false; // Re-enable on failure
                    });
            }
        });

        // Removed the redundant emergencySound?.addEventListener('ended', ...) as it was causing issues.

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