// Server

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

	// Create the socket address that the listening socket will be bound to
	SOCKADDR_IN addr;  // Address the listening socket will be bound to 
	int addrlen = sizeof(addr); // length of the address 
	addr.sin_addr.s_addr = inet_addr("127.0.0.1"); // Set up for local broadcast
	addr.sin_port = htons(1111); // Port number
	addr.sin_family = AF_INET; // Defined as an IPv4 Socket

	// Create the socket to listen for new connections
	SOCKET sListen = socket(AF_INET, SOCK_STREAM, NULL); // socket to listen for new connections
	bind(sListen, (SOCKADDR*)&addr, sizeof(addr)); // Bind the address addr to the socket sListen
	listen(sListen, SOMAXCONN); // Set sListen into a listening state in which it listens for incoming connections, SOMAXCONN = socket maximum # of connections

	// Create a new socket to hold the clients connection (TCP) 
	SOCKET newConnection;  // temporary socket for holding clients new connection
	newConnection = accept(sListen, (SOCKADDR*)&addr, &addrlen); // accept new connection

	if (newConnection == 0) {
		std::cout << "Failed to accept the client connection. " << std::endl; 
	}
	else {
		std::cout << "Client connected" << std::endl;
		// Send first message from server to client
		char welcomeMsg[200] = "Thank you for connecting to the server";
		send(newConnection, (char*)&welcomeMsg, sizeof(welcomeMsg), NULL); 

		// Receive response from client
		char response[200];
		recv(newConnection, (char*)&response, sizeof(response), NULL); 
		std::cout << "Message from client: " << response << std::endl;
	}

	system("pause");


	return 0;
}