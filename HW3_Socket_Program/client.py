
import socket
import json

def send_data(sock, data):
    try:
        sock.send(json.dumps(data).encode())
    except Exception as e:
        print(f"Error sending data: {e}")

def receive_data(sock):
    try:
        return json.loads(sock.recv(4096).decode())
    except Exception as e:
        print(f"Error receiving data: {e}")
        return None

def main():
    host = '127.0.0.1'  # Change to server IP if on separate machines
    port = 51234

    try:
        with socket.socket(socket.AF_INET, socket.SOCK_STREAM) as s:
            s.connect((host, port))

            while True:
                print("\n1. Submit new entry")
                print("2. Query by ID")
                print("q. Quit")
                choice = input("Enter choice: ").strip()

                if choice == '1':
                    entry = {
                        'action': 'submit',
                        'name': input("Name: "),
                        'id': input("ID (6-digit): "),
                        'street_number': input("Street Number: "),
                        'street_name': input("Street Name: "),
                        'city': input("City: "),
                        'postal_code': input("Postal Code: "),
                        'province': input("Province: "),
                        'country': input("Country: ")
                    }
                    send_data(s, entry)
                    response = receive_data(s)
                    print("Server response:", response)

                elif choice == '2':
                    query = {
                        'action': 'query',
                        'id': input("Enter ID to search: ")
                    }
                    send_data(s, query)
                    response = receive_data(s)
                    print("Server response:", response)

                elif choice == 'q':
                    send_data(s, {'action': 'quit'})
                    break

                else:
                    print("Invalid option. Try again.")
    except Exception as e:
        print(f"Connection error: {e}")

if __name__ == '__main__':
    main()
