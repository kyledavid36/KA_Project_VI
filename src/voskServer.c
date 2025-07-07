/*
 * voskServer.c
 *
 * This is the main elevator controller. It listens for simple text commands
 * on a TCP socket and simulates taking action. This program would be extended
to control the actual elevator motors and lights via GPIO pins.
 *
 * COMPILE WITH:
 * gcc src/voskServer.c -o bin/voskServer
 */

#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <unistd.h>
#include <arpa/inet.h>
#include <sys/socket.h>

// --- CONFIGURATION ---
#define SERVER_PORT 5001
#define MAX_COMMAND_SIZE 1024

// --- FUNCTION PROTOTYPES ---
void handle_client_connection(int client_socket);
void execute_elevator_command(const char *command);

// --- MAIN FUNCTION ---
int main(void) {
    int server_sock, client_sock;
    struct sockaddr_in server_addr, client_addr;
    socklen_t client_len;

    // --- Create the main server socket ---
    server_sock = socket(AF_INET, SOCK_STREAM, 0);
    if (server_sock < 0) {
        perror("Socket creation failed");
        exit(EXIT_FAILURE);
    }

    // Allow reuse of the address to avoid "Address already in use" errors on restart
    int opt = 1;
    setsockopt(server_sock, SOL_SOCKET, SO_REUSEADDR, &opt, sizeof(opt));

    // --- Bind the socket to the port ---
    memset(&server_addr, 0, sizeof(server_addr));
    server_addr.sin_family = AF_INET;
    server_addr.sin_addr.s_addr = INADDR_ANY; // Listen on all available network interfaces
    server_addr.sin_port = htons(SERVER_PORT);

    if (bind(server_sock, (struct sockaddr *)&server_addr, sizeof(server_addr)) < 0) {
        perror("Socket bind failed");
        close(server_sock);
        exit(EXIT_FAILURE);
    }

    // --- Start listening for incoming connections ---
    if (listen(server_sock, 5) < 0) {
        perror("Listen failed");
        close(server_sock);
        exit(EXIT_FAILURE);
    }

    printf("Elevator Command Server is active, listening on port %d...\n", SERVER_PORT);

    // --- Main Server Loop: Continuously accept new clients ---
    while (1) {
        client_len = sizeof(client_addr);
        client_sock = accept(server_sock, (struct sockaddr *)&client_addr, &client_len);
        if (client_sock < 0) {
            perror("Accept failed");
            continue; // Continue to the next iteration to wait for another client
        }
        
        char client_ip[INET_ADDRSTRLEN];
        inet_ntop(AF_INET, &client_addr.sin_addr, client_ip, INET_ADDRSTRLEN);
        printf("\nAccepted connection from client: %s\n", client_ip);

        handle_client_connection(client_sock);
    }

    // --- Cleanup (though this part is unreachable in the infinite loop) ---
    close(server_sock);
    return 0;
}

/**
 * @brief Reads a command from a connected client and executes it.
 * @param client_socket The socket descriptor for the connected client.
 */
void handle_client_connection(int client_socket) {
    char buffer[MAX_COMMAND_SIZE];
    ssize_t bytes_read;

    // Read the command from the client
    bytes_read = read(client_socket, buffer, sizeof(buffer) - 1);

    if (bytes_read > 0) {
        // Null-terminate the received string
        buffer[bytes_read] = '\0';
        printf("Received command: '%s'\n", buffer);
        execute_elevator_command(buffer);
    } else if (bytes_read == 0) {
        printf("Client disconnected without sending a command.\n");
    } else {
        perror("Read from client failed");
    }

    // Close the connection to this specific client
    close(client_socket);
    printf("Connection closed. Waiting for next command...\n");
}

/**
 * @brief Parses the command string and simulates the corresponding action.
 * @param command The command string received from a client.
 */
void execute_elevator_command(const char *command) {
    if (strcmp(command, "GOTO_1") == 0) {
        printf("ACTION: Moving elevator to Floor 1.\n");
        // TODO: Add GPIO logic to control motors
    } else if (strcmp(command, "GOTO_2") == 0) {
        printf("ACTION: Moving elevator to Floor 2.\n");
        // TODO: Add GPIO logic to control motors
    } else if (strcmp(command, "GOTO_3") == 0) {
        printf("ACTION: Moving elevator to Floor 3.\n");
        // TODO: Add GPIO logic to control motors
    } else if (strcmp(command, "OPEN_DOOR") == 0) {
        printf("ACTION: Opening elevator doors.\n");
        // TODO: Add GPIO logic to control door motor
    } else if (strcmp(command, "CLOSE_DOOR") == 0) {
        printf("ACTION: Closing elevator doors.\n");
        // TODO: Add GPIO logic to control door motor
    } else if (strcmp(command, "EMERGENCY") == 0) {
        printf("ACTION: EMERGENCY! Activating alarm and calling for help.\n");
        // TODO: Add GPIO logic for alarm and trigger emergency call client
    } else {
        printf("WARNING: Received unknown command: '%s'\n", command);
    }
}
