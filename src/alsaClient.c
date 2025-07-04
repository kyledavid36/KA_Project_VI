/*
 * client_alsa_http.c
 *
 * This program does two things:
 * 1. Listens on a local HTTP port (8080) for a connection from the GUI.
 * 2. Once triggered, it connects to the audio server, captures audio using ALSA,
 * and streams it over the network.
 *
 * COMPILE WITH: gcc client_alsa_http.c -o client_alsa_http -lasound
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
#define CHUNK_SIZE 1024        // How many audio frames to read at a time
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

    // 1. Wait for the "go" signal from the HTML GUI
    wait_for_trigger();

    // 2. Once triggered, start the main audio streaming logic
    printf("Trigger received! Starting audio stream to %s...\n", argv[1]);
    start_audio_stream(argv[1]);

    return 0;
}

/**
 * @brief Sets up a simple socket to listen for a connection on TRIGGER_PORT.
 * This function blocks until a connection is made (e.g., from the HTML button).
 */
void wait_for_trigger() {
    int listener_sock, conn_sock;
    struct sockaddr_in trigger_addr;

    listener_sock = socket(AF_INET, SOCK_STREAM, 0);
    if (listener_sock < 0) {
        perror("Failed to create trigger socket");
        exit(EXIT_FAILURE);
    }

    // Allow reuse of the address to avoid "Address already in use" errors
    int opt = 1;
    setsockopt(listener_sock, SOL_SOCKET, SO_REUSEADDR, &opt, sizeof(opt));

    memset(&trigger_addr, 0, sizeof(trigger_addr));
    trigger_addr.sin_family = AF_INET;
    trigger_addr.sin_addr.s_addr = INADDR_ANY; // Listen on localhost
    trigger_addr.sin_port = htons(TRIGGER_PORT);

    if (bind(listener_sock, (struct sockaddr *)&trigger_addr, sizeof(trigger_addr)) < 0) {
        perror("Failed to bind trigger socket");
        close(listener_sock);
        exit(EXIT_FAILURE);
    }

    listen(listener_sock, 1);
    printf("Waiting for emergency call signal from GUI on http://localhost:%d\n", TRIGGER_PORT);

    // Accept one connection and then close. We don't need to read/write anything.
    conn_sock = accept(listener_sock, NULL, NULL);
    if (conn_sock < 0) {
        perror("Failed to accept trigger connection");
        close(listener_sock);
        exit(EXIT_FAILURE);
    }
    
    // We got the signal, so we can close these sockets.
    close(conn_sock);
    close(listener_sock);
}


/**
 * @brief Connects to the audio server, captures audio via ALSA, and sends it.
 * @param server_ip The IP address of the server to connect to.
 */
void start_audio_stream(const char *server_ip) {
    int sock;
    struct sockaddr_in server_addr;
    snd_pcm_t *capture_handle;
    char *buffer;
    int err;

    // --- Setup Network Socket ---
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

    // --- Setup ALSA for Capture ---
    if (setup_alsa(&capture_handle) != 0) {
        fprintf(stderr, "ALSA setup failed.\n");
        close(sock);
        exit(EXIT_FAILURE);
    }
    printf("ALSA capture device opened successfully.\n");

    // --- Main Streaming Loop ---
    buffer = malloc(CHUNK_SIZE * snd_pcm_format_width(FORMAT) / 8 * CHANNELS);
    printf("Recording and streaming... Press Ctrl+C to stop.\n");

    while (1) {
        // Read a chunk of audio from the microphone
        err = snd_pcm_readi(capture_handle, buffer, CHUNK_SIZE);
        if (err == -EPIPE) {
            fprintf(stderr, "Overrun occurred\n");
            snd_pcm_prepare(capture_handle);
        } else if (err < 0) {
            fprintf(stderr, "Error from read: %s\n", snd_strerror(err));
        } else if (err != CHUNK_SIZE) {
            fprintf(stderr, "Short read, read %d frames\n", err);
        }

        // Send the audio chunk over the network
        if (write(sock, buffer, err * snd_pcm_format_width(FORMAT) / 8 * CHANNELS) < 0) {
            perror("Failed to write to socket");
            break;
        }
    }

    // --- Cleanup ---
    printf("Closing connections.\n");
    free(buffer);
    snd_pcm_close(capture_handle);
    close(sock);
}

/**
 * @brief Initializes the ALSA capture device (microphone).
 * @param handle A pointer to the ALSA handle that will be initialized.
 * @return 0 on success, -1 on failure.
 */
int setup_alsa(snd_pcm_t **handle) {
    int err;
    snd_pcm_hw_params_t *params;

    // Open PCM device for recording (capture).
    if ((err = snd_pcm_open(handle, "default", SND_PCM_STREAM_CAPTURE, 0)) < 0) {
        fprintf(stderr, "cannot open audio device %s (%s)\n", "default", snd_strerror(err));
        return -1;
    }

    // Allocate a hardware parameters object.
    snd_pcm_hw_params_alloca(&params);

    // Fill it in with default values.
    snd_pcm_hw_params_any(*handle, params);

    // Set the desired hardware parameters.
    snd_pcm_hw_params_set_access(*handle, params, SND_PCM_ACCESS_RW_INTERLEAVED);
    snd_pcm_hw_params_set_format(*handle, params, FORMAT);
    snd_pcm_hw_params_set_channels(*handle, params, CHANNELS);
    snd_pcm_hw_params_set_rate_near(*handle, params, (unsigned int[]){SAMPLE_RATE}, 0);
    snd_pcm_hw_params_set_period_size_near(*handle, params, (snd_pcm_uframes_t[]){CHUNK_SIZE}, 0);

    // Write the parameters to the driver.
    if ((err = snd_pcm_hw_params(*handle, params)) < 0) {
        fprintf(stderr, "cannot set hw parameters (%s)\n", snd_strerror(err));
        return -1;
    }

    return 0;
}
