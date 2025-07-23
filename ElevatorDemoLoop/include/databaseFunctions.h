// FILE: databaseFunctions.h
// AUTHORS: Alan Hosseinpour, Kyle Dick
// PURPOSE: Header file declaring MySQL database interaction functions for the elevator system
// FUNCTIONS:
// - db_getFloorNum()
// - db_setFloorNum()
// - logCANActivity()

#ifndef DATABASE_FUNCTIONS_H
#define DATABASE_FUNCTIONS_H

#include <string>

// Read latest requested floor from elevatorNetwork
int db_getFloorNum();

// Update latest current floor in elevatorNetwork
int db_setFloorNum(int floorNum);

// Log CAN activity to CAN_subnetwork (TX or RX messages)
int logCANActivity(int nodeID, const std::string& direction, const std::string& message, const std::string& description = "");

#endif