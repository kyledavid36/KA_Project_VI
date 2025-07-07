/*
 * voskServer.c
 *
 * This is the main elevator controller. It listens for simple text commands
 * on a TCP socket and logs floor requests to a MySQL database before
 * simulating the action.
 *
 * PREREQUISITES:
 * 1. MySQL C Connector library must be installed (`sudo apt-get install libmysqlclient-dev`)
 *
 * COMPILE WITH:
 * gcc src/voskServer.c -o bin/voskServer `mysql_config --cflags --libs`
 * OR
 * gcc src/voskServer.c -o bin/voskServer -lmysqlclient
 */

#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <unistd.h>
#include <arpa/inet.h>
#include <sys/socket.h>
#include <mysql/mysql.h> // Include MySQL C Connector library

// --- CONFIGURATION ---
#define SERVER_PORT 5001
#define MAX_COMMAND_SIZE 1024

// --- DATABASE CONFIGURATION (from your PHP script) ---
#define DB_HOST "127.0.0.1"
#define DB_USER "Alanhpm"
#define DB_PASS "Alanhpm1382"
#define DB_NAME "elevator"

// --- FUNCTION PROTOTYPES ---
void handle_client_connection(int client_socket);
void execute_elevator_command(const char *command);
void log_floor_to_db(int floor);

// --- MAIN FUNCTION ---
int main(void) {
    int server_sock, client_sock;
    struct sockaddr_in server_addr, client_addr;
    socklen_t client_len;

    server_sock = socket(AF_INET, SOCK_STREAM, 0);
    if (server_sock < 0) {
        perror("Socket creation failed");
        exit(EXIT_FAILURE);
    }

    int opt = 1;
    setsockopt(server_sock, SOL_SOCKET, SO_REUSEADDR, &opt, sizeof(opt));

    memset(&server_addr, 0, sizeof(server_addr));
    server_addr.sin_family = AF_INET;
    server_addr.sin_addr.s_addr = INADDR_ANY;
    server_addr.sin_port = htons(SERVER_PORT);

    if (bind(server_sock, (struct sockaddr *)&server_addr, sizeof(server_addr)) < 0) {
        perror("Socket bind failed");
        close(server_sock);
        exit(EXIT_FAILURE);
    }

    if (listen(server_sock, 5) < 0) {
        perror("Listen failed");
        close(server_sock);
        exit(EXIT_FAILURE);
    }

    printf("Elevator Command Server is active, listening on port %d...\n", SERVER_PORT);

    while (1) {
        client_len = sizeof(client_addr);
        client_sock = accept(server_sock, (struct sockaddr *)&client_addr, &client_len);
        if (client_sock < 0) {
            perror("Accept failed");
            continue;
        }
        
        char client_ip[INET_ADDRSTRLEN];
        inet_ntop(AF_INET, &client_addr.sin_addr, client_ip, INET_ADDRSTRLEN);
        printf("\nAccepted connection from client: %s\n", client_ip);

        handle_client_connection(client_sock);
    }

    close(server_sock);
    return 0;
}

void handle_client_connection(int client_socket) {
    char buffer[MAX_COMMAND_SIZE];
    ssize_t bytes_read;

    bytes_read = read(client_socket, buffer, sizeof(buffer) - 1);

    if (bytes_read > 0) {
        buffer[bytes_read] = '\0';
        printf("Received command: '%s'\n", buffer);
        execute_elevator_command(buffer);
    } else if (bytes_read == 0) {
        printf("Client disconnected without sending a command.\n");
    } else {
        perror("Read from client failed");
    }

    close(client_socket);
    printf("Connection closed. Waiting for next command...\n");
}

/**
 * @brief Logs the requested floor to the MySQL database.
 * @param floor The floor number to log.
 */
void log_floor_to_db(int floor) {
    MYSQL *con = mysql_init(NULL);

    if (con == NULL) {
        fprintf(stderr, "mysql_init() failed\n");
        return;
    }

    if (mysql_real_connect(con, DB_HOST, DB_USER, DB_PASS, DB_NAME, 0, NULL, 0) == NULL) {
        fprintf(stderr, "DB Connect Error: %s\n", mysql_error(con));
        mysql_close(con);
        return;
    }

    char query[256];
    // Create the query string, replicating the logic from updateFloor.php
    sprintf(query, "INSERT INTO elevatorNetwork (nodeID, status, currentFloor, requestedFloor, otherInfo) VALUES (0x0101, 1, %d, %d, 'User request via Voice')", floor, floor);

    if (mysql_query(con, query)) {
        fprintf(stderr, "DB Insert Error: %s\n", mysql_error(con));
    } else {
        printf("DATABASE: Successfully logged request for floor %d.\n", floor);
    }

    mysql_close(con);
}

void execute_elevator_command(const char *command) {
    if (strcmp(command, "GOTO_1") == 0) {
        printf("ACTION: Moving elevator to Floor 1.\n");
        log_floor_to_db(1);
    } else if (strcmp(command, "GOTO_2") == 0) {
        printf("ACTION: Moving elevator to Floor 2.\n");
        log_floor_to_db(2);
    } else if (strcmp(command, "GOTO_3") == 0) {
        printf("ACTION: Moving elevator to Floor 3.\n");
        log_floor_to_db(3);
    } else if (strcmp(command, "OPEN_DOOR") == 0) {
        printf("ACTION: Opening elevator doors.\n");
    } else if (strcmp(command, "CLOSE_DOOR") == 0) {
        printf("ACTION: Closing elevator doors.\n");
    } else if (strcmp(command, "EMERGENCY") == 0) {
        printf("ACTION: EMERGENCY! Activating alarm and calling for help.\n");
    } else {
        printf("WARNING: Received unknown command: '%s'\n", command);
    }
}
