import shutil
from subprocess import call
from distutils import dir_util
import os;

# Change to docker directory
os.chdir("../docker")

# Copy source from server into container for building
# With the Dockerfile beeing in the root now, we don't need to do this
#print("Copying source")
#distDir = "./server/dist"
#try:
#    os.stat(os.path.dirname(distDir))
#except:
#    os.mkdir(os.path.dirname(distDir))
    
#dir_util.copy_tree("../server/", "./server/dist")

# Build the containers
print("Building containers")
call(["docker-compose", "-f", "docker-compose-dev.yml", "build"])

# Cleanup
#shutil.rmtree('./server/dist/')

print("DONE! Run 'python ./start-dev.py' to begin development")