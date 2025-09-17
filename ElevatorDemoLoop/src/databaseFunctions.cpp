// FILE: databaseFunctions.cpp
// DESCRIPTION: Defines functions to read/write floor numbers from/to the MySQL database
// for the elevator control system. Uses MySQL Connector/C++ to communicate with the
// 'elevator' database schema on localhost.

// === INCLUDE HEADERS ===
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
    try {
        sql::Driver *driver = get_driver_instance();
        std::unique_ptr<sql::Connection> con(driver->connect("tcp://127.0.0.1:3306", "ese_group4", "ESEgroup4!"));
        con->setSchema("elevator");

        std::unique_ptr<sql::Statement> stmt(con->createStatement());
        std::unique_ptr<sql::ResultSet> res(stmt->executeQuery(
            "SELECT requestedFloor FROM elevatorNetwork WHERE nodeID = 257 ORDER BY id DESC LIMIT 1"));

        if (res->next()) {
            return res->getInt("requestedFloor");
        }
    } catch (sql::SQLException& e) {
        std::cerr << "db_getFloorNum() error: " << e.what() << std::endl;
    }
    return 1; // Default
}

// ================================================================
// FUNCTION: db_setFloorNum()
// PURPOSE : Updates the currentFloor field in the elevatorNetwork table
//           for the Elevator Controller (nodeID 257)
// INPUT   : floorNum - the floor to be written into the database
// RETURNS : 0 on success
// ================================================================
int db_setFloorNum(int floorNum) {
    try {
        sql::Driver *driver = get_driver_instance();
        std::unique_ptr<sql::Connection> con(driver->connect("tcp://127.0.0.1:3306", "ese_group4", "ESEgroup4!"));
        con->setSchema("elevator");

        std::unique_ptr<sql::PreparedStatement> pstmt(con->prepareStatement(R"(
            UPDATE elevatorNetwork 
            SET currentFloor = ? 
            WHERE id = (
                SELECT id FROM (
                    SELECT id FROM elevatorNetwork 
                    WHERE nodeID = 257 
                    ORDER BY id DESC 
                    LIMIT 1
                ) AS latest
            )
        )"));

        pstmt->setInt(1, floorNum);
        pstmt->executeUpdate();

        return 0;
    } catch (sql::SQLException& e) {
        std::cerr << "db_setFloorNum() error: " << e.what() << std::endl;
        return 1;
    }
}

// ================================================================
// FUNCTION: logCANActivity()
// AUTHOR: Alan Hpm
// PURPOSE : Logs CAN messages into CAN_subnetwork table for diagnostics
// INPUT   : nodeID - CAN node identifier
//         : direction - "TX" or "RX"
//         : message - message content in hex
//         : description - (optional) human-readable context
// RETURNS : 0 on success, 1 on failure
// ================================================================
int logCANActivity(int nodeID, const std::string& direction, const std::string& message, const std::string& description) {
    try {
        sql::Driver* driver = get_driver_instance();
        std::unique_ptr<sql::Connection> con(driver->connect("tcp://127.0.0.1:3306", "ese_group4", "ESEgroup4!"));
        con->setSchema("elevator");

        std::unique_ptr<sql::PreparedStatement> pstmt(
            con->prepareStatement("INSERT INTO CAN_subnetwork (nodeID, direction, message, description) VALUES (?, ?, ?, ?)")
        );

        pstmt->setInt(1, nodeID);
        pstmt->setString(2, direction);
        pstmt->setString(3, message);
        pstmt->setString(4, description);
        pstmt->execute();

        return 0;

    } catch (sql::SQLException& e) {
        std::cerr << "CAN logging error: " << e.what() << std::endl;
        return 1;
    }
}

// ================================================================
// FUNCTION: logElevatorRequest()
// AUTHOR  : Alan Hosseinpour
// PURPOSE : Logs elevator events (floor requests, STM input, Pi control)
//           into the elevatorNetwork table for tracking and diagnostics.
// INPUT   : nodeID         - Source CAN node identifier
//         : currentFloor   - Current floor of the elevator
//         : requestedFloor - Target/requested floor
//         : source         - Description of origin (e.g. "STM Floor 1")
//         : eventType      - Type of event (e.g. "STM_RX", "GUI_TX")
// RETURNS : void
// ================================================================
void logElevatorRequest(int nodeID, int currentFloor, int requestedFloor, const std::string& source, const std::string& eventType) {
    try {
        sql::Driver *driver;
        sql::Connection *con;
        sql::PreparedStatement *pstmt;

        driver = get_driver_instance();
        con = driver->connect(DB_HOST, DB_USER, DB_PASS);
        con->setSchema(DB_NAME);

        time_t now = time(0);
        tm *ltm = localtime(&now);

        char dateStr[11], timeStr[9];
        strftime(dateStr, sizeof(dateStr), "%Y-%m-%d", ltm);
        strftime(timeStr, sizeof(timeStr), "%H:%M:%S", ltm);

        pstmt = con->prepareStatement(
            "INSERT INTO elevatorNetwork (date, time, nodeID, status, currentFloor, requestedFloor, otherInfo, eventType, processed) "
            "VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        pstmt->setString(1, dateStr);
        pstmt->setString(2, timeStr);
        pstmt->setInt(3, nodeID);
        pstmt->setInt(4, 1); // status = active
        pstmt->setInt(5, currentFloor);
        pstmt->setInt(6, requestedFloor);
        pstmt->setString(7, source);
        pstmt->setString(8, eventType);
        pstmt->setInt(9, 0); // not processed

        pstmt->execute();

        delete pstmt;
        delete con;
    } catch (sql::SQLException &e) {
        std::cerr << "Error in logElevatorRequest(): " << e.what() << std::endl;
    }
}

