# Multi connection server code based on code from https://realpython.com/python-sockets/#multi-connection-server

import selectors                                                # import the selectors library to handle multiple connections simultaneously
import socket                                                   # import python sockets library
import types                                                    # dynamic type creation

sel = selectors.DefaultSelector()                               # global selector object


def Main():           
    host = "127.0.0.1"                                          # listen for new connections on this IP and port: define host variable as IP address of server (use IPCONFIG to get your IP address on the network) - Use 127.0.0.1 if client/server on same computer
    port = 51234                                                # define port to listen to for incoming connections (> 1024)
    lsock = socket.socket(socket.AF_INET, socket.SOCK_STREAM)   # define mySocket as an instance of a python socket, socket.socket(AF_INET, SOCK_DGRAM for UDP or SOCK_STREAM for TCP)
    lsock.bind((host, port))                                    # bind take a tuple as input (bind the host and port to the socket created above) 
    lsock.listen(1)                                             # 1 means perpetually listen for incoming connections until the connection is closed

    print('listening on ', (host, port))                        # indicate the listening IP and port
    lsock.setblocking(False)                                    # Configure the socket.accept() to work in NON-BLOCKING mode (so can listen for multiple incoming connections)
     
    sel.register(lsock, selectors.EVENT_READ, data=None)        # Registers the socket to be monitored with sel.select() to slect the read events

    while True:
        events = sel.select(timeout=None)                       # this function (a selector) BLOCKS until there are sockets ready for I/O. Returns a list of (key, event) tuples, one for each socket. If key.data=None then it is a listening socket and need to accept() the connection
        for key, mask in events:
            if key.data is None:                                # the connection is coming from the listening socket so its a new connection request that we need to accept()
                accept_wrapper(key.fileobj)                     # wrapper for the accept() function
            else:
                service_connection(key, mask)                   # otherwise (if not None) the request is coming from an existing connection (data is being sent to the server from the client) so we need to read it



def accept_wrapper(sock):                                       # accept a new connection (creates a new socket called 'conn')
    conn, addr = sock.accept()                                  # returns the IP/port of the connecting client. Is a blocking function
    print('accepted connection from ', addr)
    conn.setblocking(False)                                     # Put the new socket (called 'conn' here) (created to connect with a client) into non-blocking mode
    data = types.SimpleNamespace(addr=addr, inb=b'', outb=b'')  # create an object to hold the data we want
    events = selectors.EVENT_READ | selectors.EVENT_WRITE       # indicate when the client connection is ready for reading and writing 
    sel.register(conn, events, data=data)                       # register the conn, events and data with the BLOCKING selector 



def service_connection(key, mask):              # Service an existing socket connection with a client. key contains the socket and data, mask contains the events that are ready
    sock = key.fileobj                          # the socket object
    data = key.data                             # the data
    if mask & selectors.EVENT_READ:             # socket is ready for reading
        recv_data = sock.recv(1024)             # read up to 1024 bytes
        if recv_data:
            data.outb = recv_data
        else:                                   # if no data is received, client has closed their socket so server should too
            print('closing connection to ', data.addr)
            sel.unregister(sock)                # stop select() from monitoring this socket
            sock.close()                        # close this socket connection

    if mask & selectors.EVENT_WRITE:            # Date ready for writing in client
        if data.outb:                           # If there is data in data.outb (was received from client) write any data back (echo) to the client
            print('echoing', repr(data.outb), 'to', data.addr)
            sent = sock.send(data.outb)
            data.outb = False                   # Clear already sent data



if __name__ == '__main__':
    Main()
