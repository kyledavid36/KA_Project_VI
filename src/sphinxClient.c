/*
 * sphinxClient.c
 *
 * This program uses ALSA to listen for voice commands. It uses the Pocketsphinx
 * library to perform speech-to-text based on a defined grammar. When a valid
 * command is recognized, it sends a text command to the main elevator controller.
 *
 * PREREQUISITES:
 * 1. Pocketsphinx and Sphinxbase libraries must be installed.
 * (sudo apt-get install libsphinxbase-dev libpocketsphinx-dev pocketsphinx-en-us)
 * 2. A grammar file (e.g., commands.gram) and dictionary (commands.dic) must exist.
 *
 * COMPILE WITH:
 * gcc src/sphinxClient.c -o bin/sabbathClient -I/usr/include/sphinxbase -I/usr/include/pocketsphinx -lasound -lpocketsphinx -lsphinxbase
 */

#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <unistd.h>
#include <arpa/inet.h>
#include <sys/socket.h>
#include <alsa/asoundlib.h>
#include <pocketsphinx.h>
#include <sphinxbase/cmd_ln.h>
#include <unistd.h>  // for getcwd

// --- CONFIGURATION ---
#define SERVER_PORT 5001
#define SERVER_IP "127.0.0.1"
#define CHUNK_SIZE 1024
#define SAMPLE_RATE 16000
#define CHANNELS 1
#define FORMAT SND_PCM_FORMAT_S16_LE

// --- FUNCTION PROTOTYPES ---
int setup_alsa(snd_pcm_t **handle);
void send_command_to_server(const char *command);
ps_decoder_t* setup_pocketsphinx();

// --- MAIN FUNCTION ---
int main(void) {
    snd_pcm_t *capture_handle;
    short *buffer; 
    int err;

    // --- POCKETSPHINX INITIALIZATION ---
    ps_decoder_t *ps = setup_pocketsphinx();
    if (ps == NULL) {
        fprintf(stderr, "Error: PocketSphinx setup failed.\n");
        return 1;
    }

    // --- ALSA INITIALIZATION ---
    if (setup_alsa(&capture_handle) != 0) {
        fprintf(stderr, "ALSA setup failed.\n");
        return 1;
    }
    printf("Sabbath Mode (Sphinx) active. Listening for voice commands...\n");

    // --- MAIN RECOGNITION LOOP ---
    size_t buffer_size = CHUNK_SIZE * sizeof(short);
    buffer = malloc(buffer_size);

    ps_start_utt(ps);

    while (1) {
        err = snd_pcm_readi(capture_handle, buffer, CHUNK_SIZE);
        if (err < 0) {
            fprintf(stderr, "Error reading from audio device: %s\n", snd_strerror(err));
            snd_pcm_recover(capture_handle, err, 0);
            continue;
        }

        ps_process_raw(ps, buffer, err, FALSE, FALSE);

        const char *hyp = ps_get_hyp(ps, NULL);
        if (hyp != NULL) {
            printf("Recognized: '%s'\n", hyp);

            if (strcmp(hyp, "FLOOR ONE") == 0) send_command_to_server("GOTO_1");
            else if (strcmp(hyp, "FLOOR TWO") == 0) send_command_to_server("GOTO_2");
            else if (strcmp(hyp, "FLOOR THREE") == 0) send_command_to_server("GOTO_3");
            else if (strcmp(hyp, "OPEN DOOR") == 0) send_command_to_server("OPEN_DOOR");
            else if (strcmp(hyp, "CLOSE DOOR") == 0) send_command_to_server("CLOSE_DOOR");
            else if (strcmp(hyp, "EMERGENCY") == 0) send_command_to_server("EMERGENCY");

            ps_end_utt(ps);
            ps_start_utt(ps);
        }
    }

    // --- CLEANUP ---
    printf("Closing down.\n");
    free(buffer);
    ps_free(ps);
    snd_pcm_close(capture_handle);
    return 0;
}

/**
 * @brief Initializes and configures the Pocketsphinx decoder.
 * @return A pointer to the configured decoder, or NULL on failure.
 */
ps_decoder_t* setup_pocketsphinx() {
    char cwd[1024];
    getcwd(cwd, sizeof(cwd));

    char gram_path[2048], dict_path[2048];
    sprintf(gram_path, "%s/sphinx/commands.gram", cwd);
    sprintf(dict_path, "%s/sphinx/commands.dic", cwd);

    cmd_ln_t *config = cmd_ln_init(NULL, ps_args(), TRUE,
        "-hmm", "/usr/share/pocketsphinx/model/en-us/en-us",
        "-jsgf", gram_path,
        "-dict", dict_path,
        "-logfn", "/dev/null",
        NULL);

    if (config == NULL) {
        fprintf(stderr, "Failed to create Pocketsphinx config object\n");
        return NULL;
    }

    ps_decoder_t *ps = ps_init(config);
    if (ps == NULL) {
        fprintf(stderr, "Failed to initialize Pocketsphinx decoder.\n");
        fprintf(stderr, "Please ensure 'pocketsphinx-en-us' is installed and '%s' & '%s' exist.\n", gram_path, dict_path);
        cmd_ln_free_r(config);
        return NULL;
    }

    return ps;
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
    if ((err = snd_pcm_hw_params(*handle, params)) < 0) {
        fprintf(stderr, "cannot set hw parameters (%s)\n", snd_strerror(err));
        return -1;
    }
    return 0;
}
