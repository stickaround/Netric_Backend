import os
import time
import subprocess
import socket
import sys

os.environ["DEBUSSY"] = "127.0.0.1"

# Get IP of host which is used in the docker-compose-dev.yml for setting debug
# We do this with a dummy socket
if os.name == 'nt':
    sock = socket.socket(socket.AF_INET, socket.SOCK_DGRAM)
    sock.connect(('google.com', 0))
    os.environ["HOST_IP"] = sock.getsockname()[0]
else:
    os.environ["HOST_IP"] = socket.gethostbyname(socket.gethostname())

print("IP" + os.environ["HOST_IP"])
# Change to docker directory
os.chdir("../docker")

# Cause a rebuild
#subprocess.call(["docker-compose", "-f", "docker-compose-dev.yml", "build"])

# Run docker compose daemon
subprocess.call(["docker-compose", "-f", "docker-compose-dev.yml", "up"])

print("Done. The netric service is now running. To use the UI, clone netric.client.web and follow the instructions in the README.")
