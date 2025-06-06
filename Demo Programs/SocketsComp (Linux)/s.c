/*
 * server.c
 * Written by Peter Roeser, adapted by Michael Galle
 * This is a sample internet server application that will respond
 * to requests on port 5000
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
#include <signal.h>
#include <sys/wait.h>

#define PORT 5000

int main (void)
{

	char buffer[BUFSIZ];										// Data buffer for communications
	int server_socket, client_socket;
	int client_len;
	struct sockaddr_in client_addr, server_addr;
	int len;

	// obtain a socket for the server to listen for incoming connections
	if ((server_socket = socket (AF_INET, SOCK_STREAM, 0)) < 0) {
		printf ("Error, can't create a server socket\n");
		return 1;
	}	/* endif */

	
	// Set up server address to listen for incoming connections
	memset (&server_addr, 0, sizeof (server_addr));
	server_addr.sin_family = AF_INET;
	server_addr.sin_addr.s_addr = htonl (INADDR_ANY);
	server_addr.sin_port = htons (PORT);

	// Bind the address to the server_socket handle
	if (bind (server_socket, (struct sockaddr *)&server_addr, 
	sizeof (server_addr)) < 0) {
		printf ("grrr, can't bind server socket\n");
		close (server_socket);
		return 2;
	}	/* endif */

	// Listen for incoming connections
	if (listen (server_socket, 10) < 0) {
		printf ("grrr, can't listen on socket\n");
		close (server_socket);
		return 3;
	}	/* endif */

	// Accept incoming connections for client and create a socket for communicating with client (Note: for multiple clients, this will have to be inside the while loop and the client_socket would have to be an array)
	client_len = sizeof (client_addr);
	if ((client_socket = accept (server_socket, (struct sockaddr *)&client_addr, &client_len)) < 0) {
	  printf ("Error, can't create a socket for incoming client\n");
	  close (server_socket);						// Failed so close listening socket
	return 4;
	}

	// Success - keep exchanging messages with the (single) client
    while(1) {
      memset(buffer, 0, sizeof(buffer));			// clear read buffer
	  read (client_socket, buffer, BUFSIZ);
	  printf("Message from client: %s\n", buffer);
	  if (strcmp(buffer, "quit") == 0) {			// Exit if client sends 'quit' message.
	    printf("Client has requested to quit, exiting application\n");
	    break;
	  }

	  // Server has opportunity to respond with a message
	  memset(buffer, 0, sizeof(buffer));
	  printf ("Enter a message to send to the client (type 'quit' to exit): ");
	  fflush (stdout); 
	  fgets (buffer, sizeof (buffer), stdin); 
	  if (buffer[strlen (buffer) - 1] == '\n') {
	    buffer[strlen (buffer) - 1] = '\0'; 
          }
	  // write data to client 
	  write (client_socket, buffer, strlen(buffer));
	  if (strcmp(buffer, "quit") == 0) {
	    printf("Exiting application\n");
	    break;
	  }  
	}

	// While loop exits so close sockets
	close (server_socket);
	close (client_socket); 
	return 0;
}



