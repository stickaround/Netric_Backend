import os
import time
import subprocess

os.environ["DEBUSSY"] = "127.0.0.1"

# Get IP of host which is used in the docker-compose-dev.yml for setting debug
os.environ["HOST_IP"] = ""

# Change to docker directory
os.chdir("../docker")

# Run docker compose daemon
subprocess.call(["docker-compose", "-f", "docker-compose-dev.yml", "up", "-d"])

# Run setup
subprocess.call(["docker", "exec", "-it", "docker_netric_server_1", "/netric-setup.sh", "-d"])

print("Done. Go to http://devel.netric.com and use 'test@netric.com' and 'password' to log in.")
#print("Waiting 10 seconds to note the above, then we'll tail the logs")
#time.sleep(10)
#subprocess.call(["docker-compose", "-f", "docker-compose-dev.yml", "logs", "-f"])

