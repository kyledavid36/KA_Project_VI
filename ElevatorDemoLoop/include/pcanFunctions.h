#ifndef PCAN_FUNCTIONS
#define PCAN_FUNCTIONS


// Defines
// ***********************************************************************************************************
#define PCAN_RECEIVE_QUEUE_EMPTY        0x00020U  	// Receive queue is empty
#define PCAN_NO_ERROR               	0x00000U  	// No error 

// Elevator project specific 
#define ID_SC_TO_EC  0x100	// ID for messages from Supervisory controller to elevator controller
#define ID_EC_TO_ALL 0x101	// ID for messages from Elevator controller to all other nodes
#define ID_CC_TO_SC  0x200	// ID for messages from Car controller to supervisory controller 
#define ID_F1_TO_SC  0x201	// ID for messages from floor 1 controller to supervisory controller
#define ID_F2_TO_SC  0x202	// ID for messages from floor 2 controller to supervisory controller
#define ID_F3_TO_SC  0x203	// ID for messages from floor 3 controller to supervisory controller	

#define GO_TO_FLOOR1 0x05	// Go to floor 1
#define GO_TO_FLOOR2 0x06	// Go to floor 2
#define GO_TO_FLOOR3 0x07	// Go to floor 3


// Function declarations

// Transmits a CAN message with the given CAN ID and data byte.
// Opens the CAN channel, configures the message, sends it, and closes the channel.
int pcanTx(int id, int data);

// Receives a specified number of CAN messages.
// Opens the CAN channel, waits for messages, displays them, and returns the last data received.
int pcanRx(int num_msgs);

// Listens for specific STM floor input messages and forwards them to the Arduino (or elevator controller).
// Designed for direct Pi-to-Arduino communication, bypassing air interface or intermediate modules.
int sc_ec_control();


#endif
