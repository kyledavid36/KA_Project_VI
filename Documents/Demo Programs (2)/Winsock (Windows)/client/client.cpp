// Client
#define _WINSOCK_DEPRECATED_NO_WARNINGS 
#include <winsock2.h>
#include <WS2tcpip.h>
#include <iostream>
#pragma comment(lib,"ws2_32.lib")  // indicates to the linker that the ws2_32.lib (Winsock library) file is needed

int main() {

	// Initialize WinSock 
	WSADATA wsaData;                        // Create a WSADATA object containing info on Windows sockets implementation
	int iResult = WSAStartup(MAKEWORD(2, 2), &wsaData);   // Initiate the use of WS2_32.dll 
	if (iResult != 0) {
		printf("WSAStartup failed: %d\n", iResult);
		return 1;
	}

	// Create address to bind to the Connection Socket
	SOCKADDR_IN addr;  // Address to bind to Connection Socket
	int addrlen = sizeof(addr); // length of the address 
	addr.sin_addr.s_addr = inet_addr("127.0.0.1"); // Address = localhost (this pc - for this example)
	addr.sin_port = htons(1111); // Port number = 1111 in this example
	addr.sin_family = AF_INET; // Defined as an IPv4 Socket

	// Create the connection socket
	SOCKET Connection = socket(AF_INET, SOCK_STREAM, NULL); // sets the connection socket
	if (connect(Connection, (SOCKADDR*)&addr, addrlen) != 0) {    // cannot connect to server
		std::cout << "Server failed to accept the client connection. " << std::endl;
	}
	else {
		std::cout << "Connected to server" << std::endl;

		// Receive welcome message from server
		char welcomeMsg[200]; 
		recv(Connection, (char*)&welcomeMsg, sizeof(welcomeMsg), NULL); // Receive message from Server
		std::cout << "Message from server: " << welcomeMsg << std::endl;
		
		// Send response to server
		char response[200] = "Thank you for accepting my connection"; 
		send(Connection, (char*)&response, sizeof(response), NULL); 
	}
	system("pause");
	return 0;
}