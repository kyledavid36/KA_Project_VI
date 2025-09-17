#!/usr/bin/env python3
"""
maintenance_trigger.py
Author: Alan Hosseinpour
Date: 2025-07-23

Purpose:
Trigger Maintenance Mode (Option 5) by executing `main` binary in src/
"""

import http.server
import socketserver
import subprocess

PORT = 8090

# Path to the executable and working directory
EXECUTABLE = "./main"  # ✅ This is compiled main.cpp binary
WORKDIR = "/home/pi/Desktop/ElevatorDemoLoop/src"  # ✅ Location of that binary

class MaintenanceRequestHandler(http.server.SimpleHTTPRequestHandler):
    def do_GET(self):
        if self.path == '/start':
            try:
                subprocess.Popen([EXECUTABLE, '5'], cwd=WORKDIR)
                self.send_response(200)
                self.end_headers()
                self.wfile.write(b"✅ Maintenance mode (Option 5) started from main.cpp")
            except Exception as e:
                self.send_response(500)
                self.end_headers()
                self.wfile.write(f"❌ Error: {str(e)}".encode())
        else:
            self.send_response(404)
            self.end_headers()
            self.wfile.write(b"404 - Not Found")

if __name__ == '__main__':
    print(f"🚀 Listening on port {PORT} for maintenance trigger...")
    with socketserver.TCPServer(("", PORT), MaintenanceRequestHandler) as httpd:
        httpd.serve_forever()
