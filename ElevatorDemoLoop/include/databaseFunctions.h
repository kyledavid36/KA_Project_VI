// FILE: databaseFunctions.h
// DESCRIPTION: Header file for database interaction functions used by the 
// elevator control system. Provides declarations for reading and writing
// floor numbers to the MySQL database.

#ifndef DB_FUNCTIONS
#define DB_FUNCTIONS

// Reads the most recent requested floor number from the database
// Returns: Integer value of the requested floor (default is 1 if not found)
int db_getFloorNum();

// Updates the current floor number for the elevator controller in the database
// Parameters: floorNum - the floor number to set
// Returns: 0 on success
int db_setFloorNum(int floorNum);

#endif
