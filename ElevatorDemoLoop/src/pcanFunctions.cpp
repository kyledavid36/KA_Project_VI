// FILE: pcanFunctions.cpp
// DESCRIPTION: Handles CAN transmission and reception for elevator control system.
//              Logs all TX/RX CAN activity to database using logCANActivity().
// AUTHOR: Alan (Group 4)

#include "../include/pcanFunctions.h"
#include "../include/databaseFunctions.h"  // For logging CAN activity

#include <stdio.h>
#include <stdlib.h>
#include <errno.h>
#include <unistd.h> 
#include <signal.h>
#include <string.h>
#include <fcntl.h>
#include <ctype.h>
#include <sstream>
#include <libpcan.h>

// Globals
HANDLE h;              // Handle for transmitting
HANDLE h2;             // Handle for receiving
TPCANMsg Txmsg;        // Message structure for transmission
TPCANMsg Rxmsg;        // Message structure for reception
DWORD status;          // Status of CAN operations

// ===========================================================================
// FUNCTION: pcanTx
// PURPOSE : Send a CAN message with given ID and 1-byte data
// LOGGING : Logs TX to CAN_subnetwork table
// ===========================================================================
int pcanTx(int id, int data){
    h = LINUX_CAN_Open("/dev/pcanusb32", O_RDWR);
    status = CAN_Init(h, CAN_BAUD_125K, CAN_INIT_TYPE_ST);
    status = CAN_Status(h);

    Txmsg.ID = id; 
    Txmsg.MSGTYPE = MSGTYPE_STANDARD; 
    Txmsg.LEN = 1; 
    Txmsg.DATA[0] = data; 

    sleep(1);  // Small delay
    status = CAN_Write(h, &Txmsg);

    CAN_Close(h);

    // ✅ Log TX to database
    std::stringstream txMsg;
    txMsg << "0x" << std::hex << data;
    logCANActivity(id, "TX", txMsg.str(), "Sent from Pi to Node");

    return 0;
}

// ===========================================================================
// FUNCTION: pcanRx
// PURPOSE : Receive and print specified number of CAN messages
// LOGGING : Logs RX to CAN_subnetwork table
// ===========================================================================
int pcanRx(int num_msgs){
    int i = 0;
    h2 = LINUX_CAN_Open("/dev/pcanusb32", O_RDWR);
    status = CAN_Init(h2, CAN_BAUD_125K, CAN_INIT_TYPE_ST);
    status = CAN_Status(h2);

    system("@cls||clear");
    printf("\nReady to receive message(s) over CAN bus\n");

    while(i < num_msgs) {
        while((status = CAN_Read(h2, &Rxmsg)) == PCAN_RECEIVE_QUEUE_EMPTY){
            sleep(1);
        }
        if(status != PCAN_NO_ERROR) {
            printf("Error 0x%x\n", (int)status);
        }

        printf("  - R ID:%4x LEN:%1x DATA:%02x \n",
            (int)Rxmsg.ID, 
            (int)Rxmsg.LEN,
            (int)Rxmsg.DATA[0]);

        // ✅ Log RX to database
        std::stringstream rxMsg;
        rxMsg << "0x" << std::hex << static_cast<int>(Rxmsg.DATA[0]);
        logCANActivity(Rxmsg.ID, "RX", rxMsg.str(), "Received on Pi");

        i++;
    }

    CAN_Close(h2);
    return ((int)Rxmsg.DATA[0]);
}

// ===========================================================================
// FUNCTION: sc_ec_control
// PURPOSE : Listens for STM node floor inputs (201, 202, 203) and relays to Elevator Controller
// LOGGING : Logs STM RX messages and TX to elevator controller
// ===========================================================================
int sc_ec_control(){
    int circle = 0;
    h2 = LINUX_CAN_Open("/dev/pcanusb32", O_RDWR);
    status = CAN_Init(h2, CAN_BAUD_125K, CAN_INIT_TYPE_ST);
    status = CAN_Status(h2);

    system("@cls||clear");
    printf("\nWaiting for STM Floor Input\n");

    while(circle != 1) {
        while((status = CAN_Read(h2, &Rxmsg)) == PCAN_RECEIVE_QUEUE_EMPTY){
            sleep(1);
        }
        if(status != PCAN_NO_ERROR) {
            printf("Error 0x%x\n", (int)status);
        }

        if(Rxmsg.ID == 0x201 || Rxmsg.ID == 0x202 || Rxmsg.ID == 0x203) {
            printf("  - R ID:%4x LEN:%1x DATA:%02x \n",
                (int)Rxmsg.ID, 
                (int)Rxmsg.LEN,
                (int)Rxmsg.DATA[0]);

            // ✅ Log RX from STM
            std::stringstream stmMsg;
            stmMsg << "0x" << std::hex << static_cast<int>(Rxmsg.DATA[0]);
            logCANActivity(Rxmsg.ID, "RX", stmMsg.str(), "STM floor request");

            // ✅ Forward to Elevator Controller (SC → EC)
            pcanTx(0x100, (int)Rxmsg.DATA[0]);
            circle = 1;
        }
    }

    CAN_Close(h2);
    return ((int)Rxmsg.DATA[0]);
}
