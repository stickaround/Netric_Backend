import subprocess
import os
import sys
import argparse
import socket

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

print("Running server tests...")

#parser = argparse.ArgumentParser()
#parser.add_argument('--watch', '-w')
#parser.add_argument('--integration', '-i')
#parser.add_argument('--unit', '-u')
#rgs = parser.parse_args()

# Append any arguments passed from the user
subprocessArgs = ["docker-compose", "-f", "docker-compose-dev.yml",
                  "exec", "netric_server", "/netric-tests.sh"]
if sys.argv.count:
    userArgs = list(sys.argv)
    userArgs.pop(0)
    subprocessArgs += userArgs


# Call test within the docker container
subprocess.call(subprocessArgs)
