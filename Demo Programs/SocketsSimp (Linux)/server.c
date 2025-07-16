/* server.c - Adapted from course notes by Peter Roeser 
 *             with contributions from Michael Galle
 */

// Common C headers with common functions and constants  
#include <stdio.h>			// BUFSIZ defined here 
#include <string.h>
#include <ctype.h>
#include <stdlib.h>

// Headers with definition and implementation of socket functions in Linux
#include <unistd.h>
#include <sys/types.h>								// defines u_short
#include <sys/socket.h>
#include <netinet/in.h>
#include <arpa/inet.h>								// htons() function defined here
#include <netdb.h>
#include <signal.h>
#include <sys/wait.h>								// Server must wait for communication from client 

#define PORT 5000									// Server set up to listen to port 5000

int main (void)
{
	char buffer[BUFSIZ];							// Data buffer for communications
	int server_socket, client_socket;				// server_socket has port to listen for incoming comms, each client is assigned their own client_socket
	int client_len;
	struct sockaddr_in client_addr;					// Struct to store client address (new socket set up for each incoming client connection)
	struct sockaddr_in server_addr;					// Struct to store server address (to listen for incoming connections)
	int len, i;
	
	// Create a socket handle (variable) for the server to listen to incoming connections from (at port 5000 in this case) 
	if ((server_socket = socket(AF_INET, SOCK_STREAM, 0)) < 0) {  	// new TCP socket created to connect with each individual client
		printf ("Error: cannot create the server listening socket\n");				// < 0 indicates an error
		return 1;
	}

	// Set up handle for server socket that listens for incoming comunications  
	memset (&server_addr, 0, sizeof (server_addr));   // Copies a number of 0's to server_addr (number of 0's is sizeof(server_addr) 
	server_addr.sin_family = AF_INET;                 // IP address format 
	server_addr.sin_addr.s_addr = htonl (INADDR_ANY); // Binds socket to all available interfaces (all IP addresses of the server), htonl() is host to network long (convert to big-endian)
	server_addr.sin_port = htons (PORT);              // htons converts the PORT number from HOST byte order (could be big or little endian) to NETWORK byte order (which is always big-endian) 

	// Bind server address (defined above) to an actual socket handle (created above)  
	if (bind (server_socket, (struct sockaddr *)&server_addr, 
	sizeof (server_addr)) < 0) {
		printf ("Error binding to server socket\n");  // If there is an error
		close (server_socket);
		return 2;
	}	

	// Start listening on the socket for incoming connections from clients 
	if (listen (server_socket, 1) < 0) {            
		printf ("Error listening on the socket\n");
		close (server_socket);
		return 3;
	}

	// If a client attempts to connect, create a new client_socket on the server (ephemeral port) for that client 
	client_len = sizeof (client_addr);
	if ((client_socket = accept (server_socket, (struct sockaddr *)&client_addr, &client_len)) < 0) {		// client_socket is created for server to connect to an individual client
	  printf ("Error, can't accept a packet from client\n");	// If there is an error
	  close (server_socket);
	  return 4;
	}

    // Read data from client 
	read (client_socket, buffer, BUFSIZ);
	printf("\nClient has entered: %s\n", buffer);	
	
	// Send message back to the client 
	strcat (buffer, ", is the message received by the server\n");
	len = strlen (buffer);
	write (client_socket, buffer, len);
		
	// Close the socket to the client  
	close (client_socket);

	// Close the servers listening socket
	close (server_socket);
	printf("\nConnection terminated\n");

	return 0;				// success
}	/* end main */



