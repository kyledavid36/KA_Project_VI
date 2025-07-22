// FILE: databaseFunctions.cpp
// DESCRIPTION: Defines functions to read/write floor numbers from/to the MySQL database
// for the elevator control system. Uses MySQL Connector/C++ to communicate with the
// 'elevator' database schema on localhost.

// === INCLUDE HEADERS ===
// Include header for this module's function declarations
#include "../include/databaseFunctions.h"

// Standard library includes
#include <stdlib.h>
#include <iostream>

// MySQL C++ Connector headers
#include <mysql_connection.h>
#include <cppconn/driver.h>
#include <cppconn/exception.h>
#include <cppconn/resultset.h>
#include <cppconn/statement.h>
#include <cppconn/prepared_statement.h>

using namespace std; 

// ================================================================
// FUNCTION: db_getFloorNum()
// PURPOSE : Reads the most recent requestedFloor from the elevatorNetwork table
// RETURNS : Integer floor number (default = 1 if not found)
// ================================================================
int db_getFloorNum() {
    sql::Driver *driver;
    sql::Connection *con;
    sql::Statement *stmt;
    sql::ResultSet *res;
    int floorNum = 1; // Default floor if no result is found

    // Connect to MySQL server and select schema
    driver = get_driver_instance();
    con = driver->connect("tcp://127.0.0.1:3306", "ese_group4", "ESEgroup4!"); // Update as needed
    con->setSchema("elevator");

    // Query the latest requestedFloor for nodeID 257 (Elevator Controller)
    stmt = con->createStatement();
    res = stmt->executeQuery("SELECT requestedFloor FROM elevatorNetwork WHERE nodeID = 257");

    // Fetch floor number from query result
    if (res->next()) {
        floorNum = res->getInt("requestedFloor");
    }

    // Cleanup
    delete res;
    delete stmt;
    delete con;

    return floorNum;
}

// ================================================================
// FUNCTION: db_setFloorNum()
// PURPOSE : Updates the currentFloor field in the elevatorNetwork table
//           for the Elevator Controller (nodeID 257)
// INPUT   : floorNum - the floor to be written into the database
// RETURNS : 0 on success
// ================================================================
int db_setFloorNum(int floorNum) {
    sql::Driver *driver;
    sql::Connection *con;
    sql::PreparedStatement *pstmt;

    // Connect to MySQL server and select schema
    driver = get_driver_instance();
    con = driver->connect("tcp://127.0.0.1:3306", "ese_group4", "ESEgroup4!");
    con->setSchema("elevator");

    // Prepare and execute the update query for currentFloor
    pstmt = con->prepareStatement("UPDATE elevatorNetwork SET currentFloor = ? WHERE nodeID = 257");
    pstmt->setInt(1, floorNum);
    pstmt->executeUpdate();

    // Cleanup
    delete pstmt;
    delete con;

    return 0;
}

 
