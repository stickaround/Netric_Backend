import shutil
from subprocess import call
from distutils import dir_util
from os import chdir

# TODO: Remove stopped containers
#call(["docker", "rm", "$(docker ps -a -q)"])

# TODO: Remove unused images - this does not work on widnwos because of the commands
#call(["docker", "rmi", "$(docker images | grep "^<none>" | awk "{print $3}")"])

# Change to docker directory
chdir("../docker")

# Copy source from server into container for building
print("Copying source")
dir_util.copy_tree("../server/", "./server/dist")

# Build the containers
print("Building containers")
call(["docker-compose", "-f", "docker-compose-dev.yml", "build"])

# Cleanup
shutil.rmtree('./server/dist/')

print("DONE! Run ./start-dev.py to begin development")