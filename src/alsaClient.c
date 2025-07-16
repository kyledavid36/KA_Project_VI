/*
 * alsaClient.c
 *
 * This program does two things:
 * 1. Listens on a local HTTP port (8080) for a connection from the GUI.
 * 2. Once triggered, it connects to the audio server, captures audio using ALSA,
 * and streams it over the network.
 *
 * COMPILE WITH: gcc src/alsaClient.c -o bin/client_alsa_http -lasound
 */

#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <unistd.h>
#include <arpa/inet.h>
#include <sys/socket.h>
#include <alsa/asoundlib.h>

// --- CONFIGURATION ---
#define SERVER_PORT 5000       // Port of the main audio server
#define TRIGGER_PORT 8080      // Local port to listen for the GUI trigger
#define CHUNK_SIZE 256        // How many audio frames to read at a time
#define SAMPLE_RATE 44100      // Audio sample rate
#define CHANNELS 1             // Mono audio
#define FORMAT SND_PCM_FORMAT_S16_LE // 16-bit signed little-endian audio

// --- FUNCTION PROTOTYPES ---
void start_audio_stream(const char *server_ip);
int setup_alsa(snd_pcm_t **handle);
void wait_for_trigger();

// --- MAIN FUNCTION ---
int main(int argc, char *argv[]) {
    if (argc < 2) {
        fprintf(stderr, "Usage: %s <server_ip_address>\n", argv[0]);
        return 1;
    }

    wait_for_trigger();

    printf("Trigger received! Starting audio stream to %s...\n", argv[1]);
    start_audio_stream(argv[1]);

    return 0;
}

void wait_for_trigger() {
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

    listen(listener_sock, 1);
    printf("Waiting for emergency call signal from GUI on http://localhost:%d\n", TRIGGER_PORT);

    conn_sock = accept(listener_sock, NULL, NULL);
    if (conn_sock < 0) {
        perror("Failed to accept trigger connection");
        close(listener_sock);
        exit(EXIT_FAILURE);
    }
    
    close(conn_sock);
    close(listener_sock);
}


void start_audio_stream(const char *server_ip) {
    int sock;
    struct sockaddr_in server_addr;
    snd_pcm_t *capture_handle;
    short *buffer;
    int err;

    sock = socket(AF_INET, SOCK_STREAM, 0);
    if (sock < 0) {
        perror("Socket creation failed");
        exit(EXIT_FAILURE);
    }

    memset(&server_addr, 0, sizeof(server_addr));
    server_addr.sin_family = AF_INET;
    server_addr.sin_port = htons(SERVER_PORT);
    if (inet_pton(AF_INET, server_ip, &server_addr.sin_addr) <= 0) {
        perror("Invalid server IP address");
        close(sock);
        exit(EXIT_FAILURE);
    }

    if (connect(sock, (struct sockaddr *)&server_addr, sizeof(server_addr)) < 0) {
        perror("Connection to audio server failed");
        close(sock);
        exit(EXIT_FAILURE);
    }
    printf("Connected to audio server.\n");

    if (setup_alsa(&capture_handle) != 0) {
        fprintf(stderr, "ALSA setup failed.\n");
        close(sock);
        exit(EXIT_FAILURE);
    }
    printf("ALSA capture device opened successfully.\n");

    size_t buffer_size = CHUNK_SIZE * snd_pcm_format_width(FORMAT) / 8 * CHANNELS; // 2 bytes
    buffer = (short*)malloc(buffer_size); // set to 0 using calloc, buffer is in bits not bytes 
    printf("Recording and streaming... Press Ctrl+C to stop.\n");

    // --- FIXED Main Streaming Loop ---
    while (1) {
        // Read a chunk of audio from the microphone
        err = snd_pcm_readi(capture_handle, buffer, CHUNK_SIZE);

        if (err == -EPIPE) {
            // This is an overrun. It's recoverable.
            fprintf(stderr, "Overrun occurred\n");
            snd_pcm_prepare(capture_handle);
        } else if (err < 0) {
            // This is a more serious error.
            fprintf(stderr, "Error from read: %s\n", snd_strerror(err));
            break; // Break the loop on error
        } else {
            // Only write to the socket if we successfully read data (err > 0)
            ssize_t bytes_to_write = err * snd_pcm_format_width(FORMAT) / 8 * CHANNELS;
            if (write(sock, buffer, bytes_to_write) < 0) {
                perror("Failed to write to socket");
                break; // Break loop on write failure
            }
        }
    }

    printf("Closing connections.\n");
    free(buffer);
    snd_pcm_close(capture_handle);
    close(sock);
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

    return 0;
}
