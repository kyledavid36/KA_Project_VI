// ╔════════════════════════════════════════════════════════════════════════╗
// ║ FILE: main.cpp                                                         ║
// ║ AUTHORS: Alan Hpm, Kyle Dick                                           ║
// ║ PURPOSE:                                                               ║
// ║  - Main control loop for CAN-based elevator system on Raspberry Pi     ║
// ║  - Communicates with GUI, STM32 nodes, CAN bus, and MySQL backend      ║
// ║  - Supports multiple operating modes: manual, GUI, demo, maintenance   ║
// ║ DEPENDENCIES:                                                          ║
// ║  - pcanFunctions.h, databaseFunctions.h, mainFunctions.h               ║
// ╚════════════════════════════════════════════════════════════════════════╝

// ─── Include Custom Headers ──────────────────────────────────────────────
#include "../include/pcanFunctions.h"
#include "../include/databaseFunctions.h"
#include "../include/mainFunctions.h"

// ─── Include Standard Libraries ──────────────────────────────────────────
#include <stdio.h>
#include <stdlib.h>
#include <unistd.h> 
#include <iostream>
#include <sstream>
#include <string>

using namespace std;

// ╔══════════════════════════════════════════════════════════════════════╗
// ║ FUNCTION: main                                                       ║
// ║ DESCRIPTION:                                                         ║
// ║ - Entry point for main program loop.                                ║
// ║ - Handles menu options and routes control flow to CAN actions.      ║
// ╚══════════════════════════════════════════════════════════════════════╝
int main(int argc, char* argv[]) {

    // ─── VARIABLE DECLARATIONS ─────────────────────────────
    int choice;
    int ID;
    int data;
    int numRx;
    int floorNumber = 1;
    int prev_floorNumber = 1;

    // ─── MAIN MENU LOOP ─────────────────────────────────────
    while(1) {
        system("@cls||clear");
        choice = menu();

        switch (choice) {
            // ─────────────────────────────────────────────
            // CASE 1: MANUAL CAN TRANSMISSION
            case 1:
                ID = chooseID();
                data = chooseMsg();
                pcanTx(ID, data);
                db_setFloorNum(FloorFromHex(data));
                break;

            // ─────────────────────────────────────────────
            // CASE 2: MANUAL CAN RECEIVING
            case 2:
                printf("\nHow many messages to receive? ");
                scanf("%d", &numRx);
                pcanRx(numRx);
                break;

            // ─────────────────────────────────────────────
            // CASE 3: GUI FLOOR REQUEST LISTENER MODE
            // Purpose:
            //   - Starts by sending elevator to Floor 1
            //   - Continuously polls database for GUI floor request
            //   - If floor has changed, it:
            //       1. Converts to CAN hex
            //       2. Sends CAN TX command to elevator controller
            //       3. Logs to CAN_subnetwork and elevatorNetwork
            case 3:
                std::cout << "\n[MODE 3] Listening to GUI floor requests..." << std::endl;

                pcanTx(ID_SC_TO_EC, GO_TO_FLOOR1);
                db_setFloorNum(1);
                logCANActivity(ID_SC_TO_EC, "TX", "0x01", "Initialize to Floor 1");

                while (true) {
                    int floorNumber = db_getFloorNum();

                    if (floorNumber != prev_floorNumber) {
                        std::cout << "Detected floor change request: " << floorNumber << std::endl;

                        int hexMsg = HexFromFloor(floorNumber);
                        pcanTx(ID_SC_TO_EC, hexMsg);

                        std::stringstream msgHex;
                        msgHex << "0x" << std::hex << hexMsg;
                        logCANActivity(ID_SC_TO_EC, "TX", msgHex.str(), "GUI requested floor " + std::to_string(floorNumber));

                        db_setFloorNum(floorNumber);
                        prev_floorNumber = floorNumber;
                    }
                    sleep(1);
                }
                break;

            // ─────────────────────────────────────────────
            // CASE 4: DEMO MODE
            // Loops between all 3 floors automatically every 20 seconds.
            case 4:
                printf("\nDemo Mode - loop from floor to floor - press ctrl-z to cancel\n");
                while(1) {
                    pcanTx(ID_SC_TO_EC, GO_TO_FLOOR1);
                    db_setFloorNum(1);
                    sleep(20);
                    pcanTx(ID_SC_TO_EC, GO_TO_FLOOR2);
                    db_setFloorNum(2);
                    sleep(20);
                    pcanTx(ID_SC_TO_EC, GO_TO_FLOOR3);
                    db_setFloorNum(3);
                    sleep(20);
                }
                break;

            // ─────────────────────────────────────────────
            // CASE 5: MAINTENANCE MODE (STM32 BUTTON INPUT)
            // Purpose:
            //   - Listens to CAN RX messages from STM32 NodeIDs
            //   - Interprets message as a floor request
            //   - Updates MySQL and logs to CAN_subnetwork and elevatorNetwork
            case 5:
                std::cout << "[MODE 5] Maintenance Mode Activated - STM input directly to elevator controller\n";

                while (true) {
                    CANMessage msg = sc_ec_control();  // Blocking CAN RX function

                    int decodedFloor = FloorFromHex(msg.floor);  // Convert 0x05, 0x06, 0x07 to 1, 2, 3
                    int senderID = msg.senderID;

                    if (decodedFloor >= 1 && decodedFloor <= 3) {
                        db_setFloorNum(decodedFloor);

                        std::string canMsgHex = "0x" + to_hex(msg.floor);  // Still log original CAN hex
                        std::string source = getNodeSource(senderID);
                        std::string info = "STM button (NodeID 0x" + to_hex(senderID) + ") requested floor " + std::to_string(decodedFloor);

                        logCANActivity(senderID, "RX", canMsgHex, source);
                        logElevatorRequest(257, decodedFloor, decodedFloor, source, "FLOOR_REQUEST");
                    } else {
                        std::cout << "?? Invalid floor received: 0x" << std::hex << msg.floor 
                                  << " from STM NodeID 0x" << std::hex << senderID << std::endl;
                    }

                    sleep(1);
                }
                break;

            // ─────────────────────────────────────────────
            // CASE 6: EXIT
            case 6:
                return(0);

            // ─────────────────────────────────────────────
            // DEFAULT: Invalid menu input
            default:
                printf("Error on input values");
                sleep(3);
                break;
        }

        if (argc > 1) break; // Auto-trigger mode
        sleep(1); // Delay before next loop
    }
    return(0);
}
