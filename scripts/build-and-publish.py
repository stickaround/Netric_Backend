import os
import sys
import time
import subprocess

buildTarget = os.environ.get('BUILD_TARGET')
currentPath = os.path.dirname(os.path.realpath(__file__ + "/.."))

# Change to root directory
os.chdir("../")

# Make sure the build target is set
if buildTarget is None:
    print('BUILD_TARGET has not been set.')
    sys.exit(1)

# Login to docker registry
subprocess.call(["docker", "login", "-u", "aereusdev", "-p", "p7pfsGRe", "docker.aereusdev.com:5001"])

# The below should be used to build the webapp and put the compiled source in ./server/mobile
subprocess.call(["docker", "pull", "docker.aereusdev.com:5001/netric-client-web:" + buildTarget])
subprocess.call([
    "docker", "run", "--rm",
    "-v", currentPath + "/server/mobile:/var/www/app/build",
    "-w", "/var/www/app",
    "docker.aereusdev.com:5001/netric-client-web:" + buildTarget,
    "npm", "run", "build"
]);
subprocess.call([
    "docker", "run", "--rm",
    "-v", currentPath + "/server/mobile:/var/www/app/build",
    "-w", "/var/www/app",
    "docker.aereusdev.com:5001/netric-client-web:" + buildTarget,
    "chmod", "-R", "777", "./build/"
]);
print('Built webapp into:' + currentPath + '/server/mobile')
r
# Not build the server which will include the built webapp above
subprocess.call(["docker", "build", "-t", "docker.aereusdev.com:5001/netric:" + buildTarget, "."])
subprocess.call(["docker", "push", "docker.aereusdev.com:5001/netric:" + buildTarget])

# Cleanup
subprocess.call(["docker", "system", "prune", "-f"])