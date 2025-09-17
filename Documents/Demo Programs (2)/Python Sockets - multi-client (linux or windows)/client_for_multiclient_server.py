# client code from: https://shakeelosmani.wordpress.com/2015/04/13/python-3-socket-programming-example/

import selectors
import socket
import types

sel = selectors.DefaultSelector()


def Main():
    host = '127.0.0.1'                                        # CHANGE THIS TO THE IP ADDRESS OF THE SERVER (found via ipconfig/ifconfig or ip addr) - IP address of the server to connect to
    port = 51234                                                 # port on server to connect to 

    sock = socket.socket(socket.AF_INET, socket.SOCK_STREAM)    # define mySocket as an instance of a python socket, socket.socket(AF_INET, SOCK_DGRAM for UDP or SOCK_STREAM for TCP)
    sock.connect_ex((host, port))
    message = input(" -> ")

    while message != 'q':
        sock.send(message.encode())
        data = sock.recv(1024).decode()                         # 1024 is buffer size, BLOCKING until message is received
        print('Received from server: ' + data)
        message = input(" -> ")

    sock.close()

if __name__ == '__main__':
    Main()
    
