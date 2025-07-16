
import socket
import json
import os

DATA_FILE = "database.txt"

def save_entry(entry):
    try:
        print("Received submission:", entry)  
        with open(DATA_FILE, 'a') as f:
            f.write(json.dumps(entry) + '\n')
        return "Entry saved successfully."
    except Exception as e:
        return f"Failed to save entry: {e}"


def find_entry_by_id(entry_id):
    if not os.path.exists(DATA_FILE):
        return "No records found."

    try:
        with open(DATA_FILE, 'r') as f:
            for line in f:
                record = json.loads(line.strip())
                if record.get('id') == entry_id:
                    return record
        return "ID not found."
    except Exception as e:
        return f"Error reading file: {e}"

def main():
    host = '127.0.0.1'  # Change if needed
    port = 51234

    with socket.socket(socket.AF_INET, socket.SOCK_STREAM) as s:
        s.bind((host, port))
        s.listen(1)
        print(f"Server listening on {host}:{port}")

        conn, addr = s.accept()
        with conn:
            print(f"Connected by {addr}")
            while True:
                try:
                    data = conn.recv(4096)
                    if not data:
                        break

                    request = json.loads(data.decode())

                    if request['action'] == 'submit':
                        msg = save_entry(request)
                        conn.send(json.dumps(msg).encode())

                    elif request['action'] == 'query':
                        result = find_entry_by_id(request['id'])
                        conn.send(json.dumps(result).encode())

                    elif request['action'] == 'quit':
                        print("Client requested to quit.")
                        break

                    else:
                        conn.send(json.dumps("Invalid action.").encode())

                except Exception as e:
                    print("Error handling request:", e)
                    conn.send(json.dumps(f"Server error: {e}").encode())

if __name__ == '__main__':
    main()
