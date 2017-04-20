import shutil
from subprocess import call
from distutils import dir_util
import os;

# Change to docker directory
os.chdir("../docker")

# Build the containers
print("Stopping docker")
call(["docker-compose", "-f", "docker-compose-dev.yml", "down"])

# Cleanup
call(["docker", "system", "prune", "-f"])