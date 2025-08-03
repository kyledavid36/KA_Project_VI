/*
 * alsaClient.c
 *
 * This program does two things:
 * 1. Listens on a local HTTP port (8080) for a connection from the GUI.
 * 2. Once triggered, it connects to the audio server, captures audio using ALSA,
 * and streams it over the network.
 *
 * COMPILE WITH: gcc src/alsaClient.c -o bin/alsaClient -lasound -pthread
 *
 * IMPORTANT: You need to link with -pthread for threading to work.
 */

#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <unistd.h>
#include <arpa/inet.h>
#include <sys/socket.h>
#include <alsa/asoundlib.h>
#include <pthread.h> // For threading
#include <sys/time.h> // For SO_RCVTIMEO

// --- CONFIGURATION ---
#define SERVER_PORT 5000       // Port of the main audio server
#define TRIGGER_PORT 8080      // Local port to listen for the GUI trigger
#define CHUNK_SIZE 256         // How many audio frames to read at a time
#define SAMPLE_RATE 44100      // Audio sample rate
#define CHANNELS 1             // Mono audio
#define FORMAT SND_PCM_FORMAT_S16_LE // 16-bit signed little-endian audio
#define SOCKET_READ_TIMEOUT_SEC 1 // Timeout for socket read in seconds

// --- GLOBAL VARIABLES ---
volatile int streaming_active = 0; // Flag to control audio streaming loop
char global_server_ip[INET_ADDRSTRLEN]; // Store server IP globally
pthread_mutex_t stream_mutex = PTHREAD_MUTEX_INITIALIZER; // Mutex for streaming_active flag
pthread_t stream_thread; // Thread ID for audio streaming

// --- FUNCTION PROTOTYPES ---
void *start_audio_stream_thread(void *arg); // Thread function for audio streaming
int setup_alsa(snd_pcm_t **handle);
void handle_trigger_request(int client_sock);
void wait_for_trigger_loop();

// --- MAIN FUNCTION ---
int main(int argc, char *argv[]) {
    if (argc < 2) {
        fprintf(stderr, "Usage: %s <server_ip_address>\n", argv[0]);
        return 1;
    }

    // Store the server IP globally
    strncpy(global_server_ip, argv[1], sizeof(global_server_ip) - 1);
    global_server_ip[sizeof(global_server_ip) - 1] = '\0';

    printf("Starting trigger listener on http://localhost:%d\n", TRIGGER_PORT);
    printf("Audio server target: %s:%d\n", global_server_ip, SERVER_PORT);

    wait_for_trigger_loop(); // Start listening for triggers in the main thread

    return 0;
}

// Function to handle HTTP requests
void handle_trigger_request(int client_sock) {
    char request_buffer[1024];
    memset(request_buffer, 0, sizeof(request_buffer));
    // Use MSG_PEEK to check if there's data without consuming it, then read fully
    // This is a common pattern to ensure you don't read partial headers
    if (recv(client_sock, request_buffer, sizeof(request_buffer) - 1, MSG_PEEK | MSG_DONTWAIT) <= 0) {
        // No data or error, just close and return
        close(client_sock);
        return;
    }
    // Now read the actual request
    read(client_sock, request_buffer, sizeof(request_buffer) - 1);


    // Basic HTTP response headers
    char *response_header = "HTTP/1.1 200 OK\r\nContent-Type: text/plain\r\nConnection: close\r\n\r\n";
    write(client_sock, response_header, strlen(response_header));

    if (strstr(request_buffer, "GET /start_emergency HTTP/1.1") != NULL) {
        pthread_mutex_lock(&stream_mutex);
        if (!streaming_active) {
            streaming_active = 1;
            printf("GUI: Start emergency call triggered!\n");
            write(client_sock, "Starting emergency call...\n", 27);
            // Create a new thread for audio streaming
            if (pthread_create(&stream_thread, NULL, start_audio_stream_thread, NULL) != 0) {
                perror("Failed to create audio streaming thread");
                streaming_active = 0; // Reset flag on thread creation failure
                write(client_sock, "Error starting audio stream thread.\n", 36);
            }
        } else {
            printf("GUI: Emergency call already active.\n");
            write(client_sock, "Emergency call already active.\n", 31);
        }
        pthread_mutex_unlock(&stream_mutex);
    } else if (strstr(request_buffer, "GET /end_emergency HTTP/1.1") != NULL) {
        pthread_mutex_lock(&stream_mutex);
        if (streaming_active) {
            streaming_active = 0; // Signal the audio streaming loop to stop
            printf("GUI: End emergency call triggered!\n");
            write(client_sock, "Ending emergency call...\n", 25);
            pthread_mutex_unlock(&stream_mutex); // Unlock before join, as join waits
            // Join the thread to wait for it to finish gracefully
            if (pthread_join(stream_thread, NULL) != 0) {
                perror("Failed to join audio streaming thread");
            }
            printf("Audio streaming stopped.\n");
        } else {
            printf("GUI: Emergency call not active.\n");
            write(client_sock, "Emergency call not active.\n", 27);
            pthread_mutex_unlock(&stream_mutex);
        }
    } else {
        printf("GUI: Unknown request received.\n");
        write(client_sock, "Unknown request.\n", 17);
    }
    close(client_sock);
}

void wait_for_trigger_loop() {
    int listener_sock, conn_sock;
    struct sockaddr_in trigger_addr;

    listener_sock = socket(AF_INET, SOCK_STREAM, 0);
    if (listener_sock < 0) {
        perror("Failed to create trigger socket");
        exit(EXIT_FAILURE);
    }

    int opt = 1;
    setsockopt(listener_sock, SOL_SOCKET, SO_REUSEADDR, &opt, sizeof(opt));

    memset(&trigger_addr, 0, sizeof(trigger_addr));
    trigger_addr.sin_family = AF_INET;
    trigger_addr.sin_addr.s_addr = INADDR_ANY;
    trigger_addr.sin_port = htons(TRIGGER_PORT);

    if (bind(listener_sock, (struct sockaddr *)&trigger_addr, sizeof(trigger_addr)) < 0) {
        perror("Failed to bind trigger socket");
        close(listener_sock);
        exit(EXIT_FAILURE);
    }

    listen(listener_sock, 5); // Allow a backlog of 5 connections
    printf("Listening for GUI triggers...\n");

    while (1) {
        conn_sock = accept(listener_sock, NULL, NULL);
        if (conn_sock < 0) {
            perror("Failed to accept trigger connection");
            continue; // Continue listening for other connections
        }
        handle_trigger_request(conn_sock);
    }
    close(listener_sock); // This part will technically not be reached in an infinite loop
}

void *start_audio_stream_thread(void *arg) {
    int sock;
    struct sockaddr_in server_addr;
    snd_pcm_t *capture_handle;
    short *buffer;
    int err;

    sock = socket(AF_INET, SOCK_STREAM, 0);
    if (sock < 0) {
        perror("Socket creation failed in thread");
        pthread_mutex_lock(&stream_mutex);
        streaming_active = 0;
        pthread_mutex_unlock(&stream_mutex);
        pthread_exit(NULL);
    }

    // Set a timeout for the socket read operation
    struct timeval tv;
    tv.tv_sec = SOCKET_READ_TIMEOUT_SEC;
    tv.tv_usec = 0;
    if (setsockopt(sock, SOL_SOCKET, SO_RCVTIMEO, (const char*)&tv, sizeof tv) < 0) {
        perror("setsockopt SO_RCVTIMEO failed");
        close(sock);
        pthread_mutex_lock(&stream_mutex);
        streaming_active = 0;
        pthread_mutex_unlock(&stream_mutex);
        pthread_exit(NULL);
    }

    memset(&server_addr, 0, sizeof(server_addr));
    server_addr.sin_family = AF_INET;
    server_addr.sin_port = htons(SERVER_PORT);
    if (inet_pton(AF_INET, global_server_ip, &server_addr.sin_addr) <= 0) {
        perror("Invalid audio server IP address in thread");
        close(sock);
        pthread_mutex_lock(&stream_mutex);
        streaming_active = 0;
        pthread_mutex_unlock(&stream_mutex);
        pthread_exit(NULL);
    }

    if (connect(sock, (struct sockaddr *)&server_addr, sizeof(server_addr)) < 0) {
        perror("Connection to audio server failed in thread");
        close(sock);
        pthread_mutex_lock(&stream_mutex);
        streaming_active = 0;
        pthread_mutex_unlock(&stream_mutex);
        pthread_exit(NULL);
    }
    printf("Connected to audio server from thread.\n");

    if (setup_alsa(&capture_handle) != 0) {
        fprintf(stderr, "ALSA setup failed in thread.\n");
        close(sock);
        pthread_mutex_lock(&stream_mutex);
        streaming_active = 0;
        pthread_mutex_unlock(&stream_mutex);
        pthread_exit(NULL);
    }
    printf("ALSA capture device opened successfully in thread.\n");

    size_t buffer_size = CHUNK_SIZE * snd_pcm_format_width(FORMAT) / 8 * CHANNELS;
    buffer = (short*)malloc(buffer_size);
    if (!buffer) {
        perror("Failed to allocate buffer");
        snd_pcm_close(capture_handle);
        close(sock);
        pthread_mutex_lock(&stream_mutex);
        streaming_active = 0;
        pthread_mutex_unlock(&stream_mutex);
        pthread_exit(NULL);
    }

    printf("Recording and streaming... (Thread)\n");

    // --- Main Streaming Loop ---
    while (1) {
        pthread_mutex_lock(&stream_mutex);
        int current_streaming_active = streaming_active;
        pthread_mutex_unlock(&stream_mutex);

        if (!current_streaming_active) {
            printf("Stopping audio stream (Thread).\n");
            break; // Exit loop if streaming_active is false
        }

        err = snd_pcm_readi(capture_handle, buffer, CHUNK_SIZE);

        if (err == -EPIPE) {
            fprintf(stderr, "Overrun occurred (Thread)\n");
            snd_pcm_prepare(capture_handle);
        } else if (err < 0) {
            // Check for EWOULDBLOCK or EAGAIN if socket was non-blocking
            // With SO_RCVTIMEO, a timeout will return -EAGAIN on some systems
            if (err == -EAGAIN || err == -EWOULDBLOCK) {
                // Timeout occurred on ALSA read, loop back to check streaming_active
                continue;
            }
            fprintf(stderr, "Error from read (Thread): %s\n", snd_strerror(err));
            break;
        } else {
            ssize_t bytes_to_write = err * snd_pcm_format_width(FORMAT) / 8 * CHANNELS;
            if (send(sock, buffer, bytes_to_write, 0) < 0) {
                // Check for EWOULDBLOCK or EAGAIN for send if socket was non-blocking
                if (errno == EAGAIN || errno == EWOULDBLOCK) {
                    // This means the send buffer is full, could wait or log
                    // For now, we'll just continue and try again next loop
                    continue;
                }
                perror("Failed to write to socket (Thread)");
                break;
            }
        }
    }

    printf("Cleaning up and closing connections (Thread).\n");
    free(buffer);
    if (capture_handle) {
        snd_pcm_drain(capture_handle); // Drain any pending frames
        snd_pcm_close(capture_handle);
    }
    if (sock != -1) {
        close(sock);
    }

    pthread_exit(NULL); // Exit the thread cleanly
}


int setup_alsa(snd_pcm_t **handle) {
    int err;
    snd_pcm_hw_params_t *params;

    if ((err = snd_pcm_open(handle, "default", SND_PCM_STREAM_CAPTURE, 0)) < 0) {
        fprintf(stderr, "cannot open audio device %s (%s)\n", "default", snd_strerror(err));
        return -1;
    }

    snd_pcm_hw_params_alloca(&params);
    snd_pcm_hw_params_any(*handle, params);
    snd_pcm_hw_params_set_access(*handle, params, SND_PCM_ACCESS_RW_INTERLEAVED);
    snd_pcm_hw_params_set_format(*handle, params, FORMAT);
    snd_pcm_hw_params_set_channels(*handle, params, CHANNELS);
    snd_pcm_hw_params_set_rate_near(*handle, params, (unsigned int[]){SAMPLE_RATE}, 0);
    snd_pcm_hw_params_set_period_size_near(*handle, params, (snd_pcm_uframes_t[]){CHUNK_SIZE}, 0);

    if ((err = snd_pcm_hw_params(*handle, params)) < 0) {
        fprintf(stderr, "cannot set hw parameters (%s)\n", snd_strerror(err));
        return -1;
    }

    // Prepare the PCM device for use
    if ((err = snd_pcm_prepare(*handle)) < 0) {
        fprintf(stderr, "cannot prepare audio interface for use (%s)\n", snd_strerror(err));
        return -1;
    }


    return 0;
}