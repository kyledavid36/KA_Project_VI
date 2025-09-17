/*
 * client.c
 *
 * This is a sample internet client application that will talk
 * to the server s.c via port 5000
 */

#include <stdio.h>
#include <string.h>
#include <ctype.h>
#include <stdlib.h>
#include <unistd.h>
#include <sys/types.h>
#include <string.h>
#include <sys/socket.h>
#include <netinet/in.h>
#include <arpa/inet.h>
#include <netdb.h>

#define PORT 5000


int
main (int argc, char *argv[])
{
	char buffer[BUFSIZ];	// data buffer for communications
	int client_socket, len;
	int addr;
	struct sockaddr_in server_addr;
	struct hostent *host;

	// Check argument count to make sure there are 2
	if (argc != 2) {
		printf ("Please enter ./client and an IP address to connect to\n");
		return 1;
	}	

	// Determine host IP address for server name supplied 
	if ((host = gethostbyname (argv[1])) == NULL) {
		printf ("Please enter a valid server address\n");
		return 2;
	}	/* endif */
	memcpy (&addr, host->h_addr, host->h_length);

	// Create a socket for communications with server
	if ((client_socket = socket (AF_INET, SOCK_STREAM, 0)) < 0) {
		printf ("Error setting up a client socket!\n");
		return 3;
	}	/* endif */

	// initialize struct with server IP & Port number info
	memset (&server_addr, 0, sizeof (server_addr));
	server_addr.sin_family = AF_INET;
	server_addr.sin_addr.s_addr = addr;
	server_addr.sin_port = htons (PORT);

	// Attempt a connection to server 
	if (connect (client_socket, (struct sockaddr *)&server_addr, sizeof (server_addr)) < 0) {		// < 0 indicates failure to connect
		printf ("Error, can't connect to server!\n");
		close (client_socket);
		return 4;
	}	/* endif */

	
	// Get multiple messages from client and send to server 
	while(1) {
	  memset(buffer, 0, sizeof(buffer));															// Clear buffer data
	  
	  printf ("Enter a message to send to the server (type 'quit' to exit): ");
	  fflush (stdout); 
	  fgets (buffer, sizeof (buffer), stdin); 
	  
	  if (buffer[strlen (buffer) - 1] == '\n') {													// Replace '\n' with '\0' to terminate the string
	    buffer[strlen (buffer) - 1] = '\0'; 
      }

	  write (client_socket, buffer, strlen (buffer));												// write the message to the server (unless it is 'quit')
	  if (strcmp(buffer, "quit") == 0) {
	    printf("Exiting application\n");
	    break;
	  }

	  memset(buffer, 0, sizeof(buffer));															// Clear the buffer
	  len = read (client_socket, buffer, sizeof (buffer));											// Read the response from the server
	  printf ("Response from server: %s\n\n", buffer);

	  if (strcmp(buffer, "quit") == 0) {															// If server message is quit then close connection
	    printf("Server has decided to quit this connection, exiting application\n");
	    break;
	  }
	}

	close (client_socket);																			// After exiting loop, close connection to server
	return 0;
}	/* end main */



