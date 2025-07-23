/* client.c - Adapted from course notes by Peter Roeser 
 *             with contributions from Michael Galle
 */

// Common C headers 
#include <stdio.h>
#include <string.h>
#include <ctype.h>
#include <stdlib.h>
 
 // Headers with definition and implementation of socket functions in Linux
#include <unistd.h>
#include <sys/types.h>		// defines u_short
#include <sys/socket.h>
#include <netinet/in.h>
#include <arpa/inet.h>		// htons() function defined here 
#include <netdb.h>

#define PORT 5000			// Server set up to listen to port 5000


int
main (int argc, char *argv[])			// argv[1] contains IP address of the server (passed via command line) 
{
	/* Socket set up */
	/* ******************************************************************************* */ 
	char buffer[BUFSIZ];				// data buffer for communications
	int client_socket, len;				// socket handle is an integer
	int addr;
	struct sockaddr_in server_addr;		// struct sockaddr_in is defined in headers
	struct hostent* host;				// struct hostent is defined in the headers

	if ((host = gethostbyname(argv[1])) == NULL) {   				    // Must pass an IP address or website name like 'localhost' or 'http://www.google.com' as argument when calling this executable 
		printf ("Please enter a valid server address \n");
		return 2;
	}
	memcpy (&addr, host->h_addr_list[0], host->h_length);				// Copy the first (and only) IP address to variable addr (an integer variable)				

	// Create a client socket
	if ((client_socket = socket(AF_INET, SOCK_STREAM, 0)) < 0) {		// Creates a TCP socket (for the client) and stores it in the client_socket handle (variable) - uses an ephemeral port
		printf ("Error setting up a client socket!\n");
		return 3;
	}

	// Connect the client to the server
	// Add data to server address struct
	memset (&server_addr, 0, sizeof (server_addr));						// Clear the memory block
	server_addr.sin_family = AF_INET;									// Set to use IP address format  
	server_addr.sin_addr.s_addr = addr;									// Set IP address to the one input at command line (converted to integer addr)	
	server_addr.sin_port = htons (PORT);								// Set the port number (htons is a function that converts from host format to network short format which is big endian) 

	// Connect client to server 										// Create a socket connection between client_socket and the server socket (created by server) at port 5000
	if (connect (client_socket, (struct sockaddr*)&server_addr, sizeof (server_addr)) < 0) {
		printf ("\nError, can't connet to server\n");
		close (client_socket);											// If fail to connect to server, close client socket
		return 4;
	}	
	
	/* Message setup */
	/* ********************************************************************* */

	/* Get message to send to server from user and store in a buffer */ 
	printf ("\nEnter the message for server: ");
	fflush (stdout);
	fgets (buffer, sizeof (buffer), stdin);
	if (buffer[strlen (buffer) - 1] == '\n')	// If enter is pressed then replace with string termination character '\0'
		buffer[strlen (buffer) - 1] = '\0';     
	
	/* Socket communication */
	/* *********************************************************************** */ 
	// Send message in buffer (via socket to server) using the write() command 
	write (client_socket, buffer, strlen (buffer));

	// Read message back from server into buffer
	len = read (client_socket, buffer, sizeof (buffer));
	printf ("Server response: %s\n\n", buffer);

	/* Close client socket */
	close (client_socket);
	printf ("\nClient is finished ... connection terminated \n");

	return 0;
}	



