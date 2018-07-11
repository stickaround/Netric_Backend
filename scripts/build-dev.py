import shutil
from subprocess import call
from distutils import dir_util
import os;

# Change to docker directory
os.chdir("../docker")

# Build the containers
print("Building containers")
call(["docker-compose", "-f", "docker-compose-dev.yml", "build"])

# Cleanup
#shutil.rmtree('./server/dist/')

print("DONE! Run 'python ./start-dev.py' to begin development")