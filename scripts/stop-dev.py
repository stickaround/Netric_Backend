import shutil
from subprocess import call
from distutils import dir_util
import os

# Change to docker directory
os.chdir("..")
os.environ["HOST_IP"] = "127.0.0.1"

# Build the containers
print("Stopping docker")
call(["docker-compose", "-f", "docker-compose.yml", "down"])

# Cleanup
call(["docker", "system", "prune", "-f"])
