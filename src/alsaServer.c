/*
 * alsaServer.c
 *
 * This program listens for a connection from the client, receives raw
 * audio data, and plays it through the speakers using ALSA.
 *
 * COMPILE WITH: gcc src/alsaServer.c -o bin/server_alsa -lasound
 */

#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <unistd.h>
#include <arpa/inet.h>
#include <sys/socket.h>
#include <alsa/asoundlib.h>

// --- CONFIGURATION (must match client) ---
#define SERVER_PORT 5000
#define CHUNK_SIZE 256
#define SAMPLE_RATE 44100
#define CHANNELS 1
#define FORMAT SND_PCM_FORMAT_S16_LE

// --- FUNCTION PROTOTYPE ---
int setup_alsa_playback(snd_pcm_t **handle);

// --- MAIN FUNCTION ---
int main(void) {
    int server_sock, client_sock;
    struct sockaddr_in server_addr, client_addr;
    socklen_t client_len;

    snd_pcm_t *playback_handle;
    short *buffer;
    int err;

    server_sock = socket(AF_INET, SOCK_STREAM, 0);
    if (server_sock < 0) {
        perror("Socket creation failed");
        exit(EXIT_FAILURE);
    }

    memset(&server_addr, 0, sizeof(server_addr));
    server_addr.sin_family = AF_INET;
    server_addr.sin_addr.s_addr = INADDR_ANY;
    server_addr.sin_port = htons(SERVER_PORT);

    if (bind(server_sock, (struct sockaddr *)&server_addr, sizeof(server_addr)) < 0) {
        perror("Socket bind failed");
        close(server_sock);
        exit(EXIT_FAILURE);
    }

    listen(server_sock, 1);
    printf("Audio server listening on port %d...\n", SERVER_PORT);

    client_len = sizeof(client_addr);
    client_sock = accept(server_sock, (struct sockaddr *)&client_addr, &client_len);
    if (client_sock < 0) {
        perror("Accept failed");
        close(server_sock);
        exit(EXIT_FAILURE);
    }
    printf("Client connected.\n");

    if (setup_alsa_playback(&playback_handle) != 0) {
        fprintf(stderr, "ALSA playback setup failed.\n");
        close(client_sock);
        close(server_sock);
        exit(EXIT_FAILURE);
    }
    printf("ALSA playback device opened successfully.\n");

    // --- FIXED Main Playback Loop ---
    // Calculate the buffer size once and use it everywhere.
    size_t buffer_size = CHUNK_SIZE * snd_pcm_format_width(FORMAT) / 8 * CHANNELS;
    buffer = (short*)malloc(buffer_size);
    printf("Receiving and playing audio...\n");
    
    ssize_t bytes_read;
    // The read call now uses the correct buffer_size.
    while ((bytes_read = read(client_sock, buffer, buffer_size)) > 0) {
        // Write the received audio chunk to the speakers
        err = snd_pcm_writei(playback_handle, buffer, bytes_read / (snd_pcm_format_width(FORMAT) / 8 * CHANNELS));
        if (err == -EPIPE) {
            fprintf(stderr, "Underrun occurred\n");
            snd_pcm_prepare(playback_handle);
        } else if (err < 0) {
            fprintf(stderr, "Error from writei: %s\n", snd_strerror(err));
        }
    }

    if (bytes_read == 0) {
        printf("Client disconnected.\n");
    } else {
        perror("Read from socket failed");
    }

    printf("Closing connections.\n");
    free(buffer);
    snd_pcm_drain(playback_handle);
    snd_pcm_close(playback_handle);
    close(client_sock);
    close(server_sock);

    return 0;
}

int setup_alsa_playback(snd_pcm_t **handle) {
    int err;
    snd_pcm_hw_params_t *params;

    if ((err = snd_pcm_open(handle, "default", SND_PCM_STREAM_PLAYBACK, 0)) < 0) {
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
