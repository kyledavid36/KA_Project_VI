#include "../include/pcanFunctions.h"

#include <stdio.h>
#include <stdlib.h>
#include <stdlib.h>  
#include <errno.h>
#include <unistd.h> 
#include <signal.h>
#include <string.h>
#include <fcntl.h>     // For O_RDWR flag
#include <unistd.h>    // For sleep() and POSIX functions
#include <ctype.h>
#include <libpcan.h>   // PCAN library for CAN communication

// Globals
// ***********************************************************************************************************
// CAN handles and message structures for transmission and reception
HANDLE h;              // Handle for transmitting
HANDLE h2;             // Handle for receiving
TPCANMsg Txmsg;        // Message structure for transmission
TPCANMsg Rxmsg;        // Message structure for reception
DWORD status;          // Status of CAN operations

// Functions
// ***********************************************************************************************************

// Function to transmit a CAN message with a given ID and data
int pcanTx(int id, int data){
    // Open the CAN channel
    h = LINUX_CAN_Open("/dev/pcanusb32", O_RDWR);

    // Initialize the channel for 125 kbps standard frames
    status = CAN_Init(h, CAN_BAUD_125K, CAN_INIT_TYPE_ST);

    // Clear channel status (important for fresh transmission/reception)
    status = CAN_Status(h);

    // Setup the CAN message structure
    Txmsg.ID = id; 
    Txmsg.MSGTYPE = MSGTYPE_STANDARD; 
    Txmsg.LEN = 1; 
    Txmsg.DATA[0] = data; 

    sleep(1);   // Small delay to ensure readiness
    status = CAN_Write(h, &Txmsg);  // Transmit the message

    // Close the CAN channel to free resources
    CAN_Close(h);

    // *** UPDATE: Added 'return();' instead of no return value (old version had no return!) ***
    return();
}

// Function to receive a specified number of CAN messages
int pcanRx(int num_msgs){
    int i = 0;

    // Open a CAN channel for receiving
    h2 = LINUX_CAN_Open("/dev/pcanusb32", O_RDWR);

    // Initialize the channel for 125 kbps standard frames
    status = CAN_Init(h2, CAN_BAUD_125K, CAN_INIT_TYPE_ST);

    // Clear the channel status
    status = CAN_Status(h2);

    // Clear the terminal for a fresh display of incoming messages
    system("@cls||clear");

    printf("\nReady to receive message(s) over CAN bus\n");

    // Loop to read the specified number of messages
    while(i < num_msgs) {
        while((status = CAN_Read(h2, &Rxmsg)) == PCAN_RECEIVE_QUEUE_EMPTY){
            sleep(1);  // Wait if no messages yet
        }
        if(status != PCAN_NO_ERROR) {       // Check for errors
            printf("Error 0x%x\n", (int)status);
            //break; // Leave loop on error (commented out)
        }

        // Ignore default status messages
        if(Rxmsg.ID != 0x01 && Rxmsg.LEN != 0x04) {
            printf("  - R ID:%4x LEN:%1x DATA:%02x \n",
                (int)Rxmsg.ID, 
                (int)Rxmsg.LEN,
                (int)Rxmsg.DATA[0]);
            i++;  // Count only valid messages
        }
    }

    // Close the CAN channel after done receiving
    CAN_Close(h2);

    // Return the last message's data (same as old version)
    return ((int)Rxmsg.DATA[0]);
}

// *** NEW FUNCTION ADDED: sc_ec_control ***
// This function listens for specific STM floor input CAN IDs and directly forwards them to Arduino
// (bypassing typical air communication, as needed for direct Pi-to-Arduino control).
int sc_ec_control(){
    int circle = 0;  // Control flag to exit loop after one valid message

    // Open a CAN channel for receiving
    h2 = LINUX_CAN_Open("/dev/pcanusb32", O_RDWR);

    // Initialize the channel
    status = CAN_Init(h2, CAN_BAUD_125K, CAN_INIT_TYPE_ST);

    // Clear the channel status
    status = CAN_Status(h2);

    // Clear screen to indicate new listening session
    system("@cls||clear");
    printf("\nWaiting for STM Floor Input\n");

    // Loop to receive STM input until one valid message is received
    while(circle != 1) {
        while((status = CAN_Read(h2, &Rxmsg)) == PCAN_RECEIVE_QUEUE_EMPTY){
            sleep(1);  // Wait if no messages
        }
        if(status != PCAN_NO_ERROR) {
            printf("Error 0x%x\n", (int)status);
        }

        // Check for specific STM floor IDs (201, 202, 203)
        if(Rxmsg.ID == 0x201 || Rxmsg.ID == 0x202 || Rxmsg.ID == 0x203) {
            printf("  - R ID:%4x LEN:%1x DATA:%02x \n",
                (int)Rxmsg.ID, 
                (int)Rxmsg.LEN,
                (int)Rxmsg.DATA[0]);

            // Directly forward this STM floor input to Arduino using CAN ID 0x100
            pcanTx(0x100, (int)Rxmsg.DATA[0]);

            circle = 1;  // Exit after first valid STM input is handled
        }
    }

    // Close CAN channel after done
    CAN_Close(h2);

    // Return last data value (floor number)
    return ((int)Rxmsg.DATA[0]);
}
