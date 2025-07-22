// FILE: mainFunctions.h
// DESCRIPTION: Header file containing utility functions used for user input, 
// menu display, and CAN message translation for the elevator control system.

#ifndef MAIN_FUNCTIONS
#define MAIN_FUNCTIONS

// Displays the main control menu and returns the user's selected option.
// Returns: Integer value representing the chosen menu option (1–6).
int menu();

// Prompts the user to choose a CAN ID for message transmission.
// Returns: Integer value representing the selected CAN ID.
int chooseID();

// Prompts the user to choose a CAN message command (e.g., go to floor).
// Returns: Integer value representing the message hex code.
int chooseMsg();

// Converts a floor number to its corresponding CAN message hex value.
// Parameters: floorVal - floor number (1, 2, or 3)
// Returns: Corresponding CAN message hex value.
int HexFromFloor(int floorVal);

// Converts a CAN message hex value to its corresponding floor number.
// Parameters: Hex - the CAN message hex code
// Returns: Floor number (1, 2, or 3).
int FloorFromHex(int Hex);

#endif
