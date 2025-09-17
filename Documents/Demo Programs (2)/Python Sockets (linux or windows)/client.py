# client code from: https://shakeelosmani.wordpress.com/2015/04/13/python-3-socket-programming-example/

import socket
def Main():
    host = '127.0.0.1'        # CHANGE THIS!!! - IP address of the server to connect to (This should be the actual IP address of the server (using ipconfig/ifconfig or ip a))
    port = 51234                 # port on server to connect to 

    mySocket = socket.socket(socket.AF_INET, socket.SOCK_STREAM)  # define mySocket as an instance of a python socket, socket.socket(AF_INET, SOCK_DGRAM for UDP or SOCK_STREAM for TCP)
    mySocket.connect((host, port))

    message = input(" -> ")

    while message != 'q':
        mySocket.send(message.encode())
        data = mySocket.recv(1024).decode()   # 1024 is buffer size

        print('Received from server: ' + data)

        message = input(" -> ")

    mySocket.close()

if __name__ == '__main__':
    Main()
    
