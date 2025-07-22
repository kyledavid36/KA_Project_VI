// Include custom header files for PCAN functions, database functions, and main functions
#include "../include/pcanFunctions.h"
#include "../include/databaseFunctions.h"
#include "../include/mainFunctions.h"

// Include standard C/C++ libraries
#include <stdio.h>
#include <stdlib.h>
#include <unistd.h> 
#include <iostream>

using namespace std;

// ******************************************************************
// Main program starts here
int main() {

    // Variable declarations
    int choice;                    // User choice for menu option
    int ID;                        // CAN ID to transmit
    int data;                      // Message data to transmit
    int numRx;                     // Number of messages to receive
    int floorNumber = 1;           // Current floor number
    int prev_floorNumber = 1;      // Previous floor number (for comparison)

    // Infinite loop for continuous program operation
    while(1) {
        system("@cls||clear");     // Clear the console screen
        choice = menu();           // Show menu and get user's choice

        switch (choice) {
            case 1: 
                // Manual transmission of a message
                ID = chooseID();                // User selects CAN ID
                data = chooseMsg();             // User selects message data
                pcanTx(ID, data);               // Transmit CAN message
                db_setFloorNum(FloorFromHex(data));  // Update database floor number
                break; 
                
            case 2:
                // Manual receiving of CAN messages
                printf("\nHow many messages to receive? ");
                scanf("%d", &numRx);
                pcanRx(numRx);                  // Call function to receive messages
                break;
                
            case 3:
                printf("\nListening to GUI requests... (press ctrl-z to stop)\n");

                pcanTx(ID_SC_TO_EC, GO_TO_FLOOR1);
                db_setFloorNum(1);

                while (true) {
                    int floorNumber = db_getFloorNum();
                    if (floorNumber != prev_floorNumber) {
                        pcanTx(ID_SC_TO_EC, HexFromFloor(floorNumber));
                        db_setFloorNum(floorNumber); // reflect movement complete
                        prev_floorNumber = floorNumber;
                    }
                    sleep(1);
                }
                break;

                
            case 4:
                // Demo mode: Automatically loop between floors
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
                
            case 5: 
                // *** NEW OPTION ADDED: Listen for STM input directly and send it to Arduino ***
                // This mode bypasses the Arduino's air communication and directly forwards STM messages to the Arduino.
                while(1){
                    data = sc_ec_control();         // Listen for STM input (floor button press)
                    db_setFloorNum(data);           // Update floor number in DB with received STM message
                }
                break;
            
            case 6:
                // Exit the program
                return(0);

            default:
                // Handle invalid input
                printf("Error on input values");
                sleep(3);
                break;
        }
        sleep(1);   // Delay between menu operations
    }

    return(0);      // End of program
}



	
 