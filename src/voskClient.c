/*
 * voskClient.c
 *
 * This program waits for a trigger from the GUI, then activates, listening
 * for voice commands and sending them to the main elevator controller.
 *
 * COMPILE WITH:
 * gcc src/voskClient.c -o bin/sabbathClient -lasound -lvosk -L/path/to/vosk/lib -I/path/to/vosk/include
 */

#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <unistd.h>
#include <arpa/inet.h>
#include <sys/socket.h>
#include <alsa/asoundlib.h>
#include "vosk_api.h"

// --- CONFIGURATION ---
#define SERVER_PORT 5001
#define SERVER_IP "127.0.0.1"
#define TRIGGER_PORT 8081      // New, separate port for the Sabbath mode trigger
#define CHUNK_SIZE 256
#define SAMPLE_RATE 16000
#define CHANNELS 1
#define FORMAT SND_PCM_FORMAT_S16_LE
#define VOSK_MODEL_PATH "model"

// --- FUNCTION PROTOTYPES ---
int setup_alsa(snd_pcm_t **handle);
void send_command_to_server(const char *command);
void wait_for_trigger();

// --- MAIN FUNCTION ---
int main(void) {
    snd_pcm_t *capture_handle;
    char *buffer;
    int err;

    // Wait for the GUI to activate Sabbath Mode
    wait_for_trigger();

    VoskModel *model = vosk_model_new(VOSK_MODEL_PATH);
    if (!model) {
        fprintf(stderr, "Error: Could not create Vosk model. Make sure path '%s' is correct.\n", VOSK_MODEL_PATH);
        return 1;
    }
    const char *grammar = "[\"floor one\", \"floor two\", \"floor three\", \"open door\", \"close door\", \"emergency\"]";
    VoskRecognizer *recognizer = vosk_recognizer_new_grm(model, (float)SAMPLE_RATE, grammar);

    if (setup_alsa(&capture_handle) != 0) {
        fprintf(stderr, "ALSA setup failed.\n");
        return 1;
    }
    printf("Sabbath Mode active. Listening for voice commands...\n");

    size_t buffer_size = CHUNK_SIZE * sizeof(short);
    buffer = malloc(buffer_size);

    while (1) {
        err = snd_pcm_readi(capture_handle, buffer, CHUNK_SIZE);
        if (err < 0) {
            snd_pcm_recover(capture_handle, err, 0);
            continue;
        }
        if (vosk_recognizer_accept_waveform(recognizer, buffer, err * sizeof(short))) {
            const char *result_json = vosk_recognizer_result(recognizer);
            char *text_start = strstr(result_json, "\"text\" : \"");
            if (text_start) {
                text_start += 10;
                char *text_end = strchr(text_start, '"');
                if (text_end) {
                    *text_end = '\0';
                    printf("Recognized: '%s'\n", text_start);
                    if (strcmp(text_start, "floor one") == 0) send_command_to_server("GOTO_1");
                    else if (strcmp(text_start, "floor two") == 0) send_command_to_server("GOTO_2");
                    else if (strcmp(text_start, "floor three") == 0) send_command_to_server("GOTO_3");
                    else if (strcmp(text_start, "open door") == 0) send_command_to_server("OPEN_DOOR");
                    else if (strcmp(text_start, "close door") == 0) send_command_to_server("CLOSE_DOOR");
                    else if (strcmp(text_start, "emergency") == 0) send_command_to_server("EMERGENCY");
                }
            }
        }
    }

    free(buffer);
    vosk_recognizer_free(recognizer);
    vosk_model_free(model);
    snd_pcm_close(capture_handle);
    return 0;
}

void wait_for_trigger() {
    int listener_sock, conn_sock;
    struct sockaddr_in trigger_addr;
    listener_sock = socket(AF_INET, SOCK_STREAM, 0);
    if (listener_sock < 0) {
        perror("Failed to create sabbath trigger socket");
        exit(EXIT_FAILURE);
    }
    int opt = 1;
    setsockopt(listener_sock, SOL_SOCKET, SO_REUSEADDR, &opt, sizeof(opt));
    memset(&trigger_addr, 0, sizeof(trigger_addr));
    trigger_addr.sin_family = AF_INET;
    trigger_addr.sin_addr.s_addr = INADDR_ANY;
    trigger_addr.sin_port = htons(TRIGGER_PORT);
    if (bind(listener_sock, (struct sockaddr *)&trigger_addr, sizeof(trigger_addr)) < 0) {
        perror("Failed to bind sabbath trigger socket");
        close(listener_sock);
        exit(EXIT_FAILURE);
    }
    listen(listener_sock, 1);
    printf("Sabbath Mode client ready. Waiting for activation signal on port %d...\n", TRIGGER_PORT);
    conn_sock = accept(listener_sock, NULL, NULL);
    if (conn_sock < 0) {
        perror("Failed to accept sabbath trigger connection");
        close(listener_sock);
        exit(EXIT_FAILURE);
    }
    close(conn_sock);
    close(listener_sock);
    printf("Sabbath Mode activated by GUI.\n");
}

void send_command_to_server(const char *command) {
    int sock;
    struct sockaddr_in server_addr;
    sock = socket(AF_INET, SOCK_STREAM, 0);
    if (sock < 0) {
        perror("Command socket creation failed");
        return;
    }
    memset(&server_addr, 0, sizeof(server_addr));
    server_addr.sin_family = AF_INET;
    server_addr.sin_port = htons(SERVER_PORT);
    if (inet_pton(AF_INET, SERVER_IP, &server_addr.sin_addr) <= 0) {
        perror("Invalid command server IP address");
        close(sock);
        return;
    }
    if (connect(sock, (struct sockaddr *)&server_addr, sizeof(server_addr)) < 0) {
        close(sock);
        return;
    }
    printf("Sending command: %s\n", command);
    write(sock, command, strlen(command));
    close(sock);
}

int setup_alsa(snd_pcm_t **handle) {
    int err;
    snd_pcm_hw_params_t *params;
    snd_pcm_uframes_t period_size = CHUNK_SIZE;
    snd_pcm_uframes_t buffer_size = period_size * 4;
    if ((err = snd_pcm_open(handle, "default", SND_PCM_STREAM_CAPTURE, 0)) < 0) {
        fprintf(stderr, "cannot open audio device %s (%s)\n", "default", snd_strerror(err));
        return -1;
    }
    snd_pcm_hw_params_alloca(&params);
    snd_pcm_hw_params_any(*handle, params);
    snd_pcm_hw_params_set_access(*handle, params, SND_PCM_ACCESS_RW_INTERLEAVED);
    snd_pcm_hw_params_set_format(*handle, params, FORMAT);
    snd_pcm_hw_params_set_channels(*handle, params, CHANNELS);
    unsigned int rate = SAMPLE_RATE;
    snd_pcm_hw_params_set_rate_near(*handle, params, &rate, 0);
    if ((err = snd_pcm_hw_params_set_period_size_near(*handle, params, &period_size, 0)) < 0) {
        fprintf(stderr, "cannot set period size (%s)\n", snd_strerror(err));
        return -1;
    }
    if ((err = snd_pcm_hw_params_set_buffer_size_near(*handle, params, &buffer_size)) < 0) {
        fprintf(stderr, "cannot set buffer size (%s)\n", snd_strerror(err));
        return -1;
    }
    if ((err = snd_pcm_hw_params(*handle, params)) < 0) {
        fprintf(stderr, "cannot set hw parameters (%s)\n", snd_strerror(err));
        return -1;
    }
    return 0;
}
