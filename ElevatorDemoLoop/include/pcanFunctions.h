#ifndef PCAN_FUNCTIONS
#define PCAN_FUNCTIONS
#include "databaseFunctions.h"  // Already declares logCANActivity properly


// ─────────────────────────────────────────────────────────────
// PCAN_FUNCTIONS.H
// AUTHOR: Alan Hpm (Group 4)
// DESCRIPTION:
//   Header for CAN transmission, reception, and forwarding logic.
//   Also declares logCANActivity for CAN_subnetwork diagnostics.
// ─────────────────────────────────────────────────────────────


// ──────────── DEFINES ────────────
#define PCAN_RECEIVE_QUEUE_EMPTY  0x00020U  // Receive queue is empty
#define PCAN_NO_ERROR             0x00000U  // No error

// Elevator CAN IDs
#define ID_SC_TO_EC  0x100  // Supervisory Controller → Elevator Controller
#define ID_EC_TO_ALL 0x101  // Elevator Controller → All
#define ID_CC_TO_SC  0x200  // Car Controller → Supervisory Controller
#define ID_F1_TO_SC  0x201  // Floor 1 → Supervisory Controller
#define ID_F2_TO_SC  0x202  // Floor 2 → Supervisory Controller
#define ID_F3_TO_SC  0x203  // Floor 3 → Supervisory Controller

// CAN Commands to Elevator Controller
#define GO_TO_FLOOR1 0x05
#define GO_TO_FLOOR2 0x06
#define GO_TO_FLOOR3 0x07



// ──────────── FUNCTION DECLARATIONS ────────────

// Transmit a CAN message with ID and data
int pcanTx(int id, int data);

// Receive N CAN messages and return last data byte
int pcanRx(int num_msgs);

// Listen for STM (floor) CAN input and forward to elevator controller
int sc_ec_control();

// Log CAN activity to diagnostics database
// Updated signature to match databaseFunctions.h
//int logCANActivity(int nodeID, const std::string& direction, const std::string& message, const std::string& description);

#endif  // PCAN_FUNCTIONS
