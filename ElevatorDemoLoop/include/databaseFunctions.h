// FILE: databaseFunctions.h
// AUTHORS: Micahel Galle (Intructor) | edited by: Alan Hosseinpour, Kyle Dick
// PURPOSE: Header file declaring MySQL database interaction functions for the elevator system
// FUNCTIONS:
// - db_getFloorNum()
// - db_setFloorNum()
// - logCANActivity()

#ifndef DATABASE_FUNCTIONS_H
#define DATABASE_FUNCTIONS_H

#include <string>

#define DB_HOST "tcp://127.0.0.1:3306"
#define DB_USER "ese_group4"
#define DB_PASS "ESEgroup4!"
#define DB_NAME "elevator"

// Read latest requested floor from elevatorNetwork
int db_getFloorNum();

// Update latest current floor in elevatorNetwork
int db_setFloorNum(int floorNum);

// Log CAN activity to CAN_subnetwork (TX or RX messages)
int logCANActivity(int nodeID, const std::string& direction, const std::string& message, const std::string& description = "");

void logElevatorRequest(int nodeID, int currentFloor, int requestedFloor, const std::string& source, const std::string& eventType);


#endif