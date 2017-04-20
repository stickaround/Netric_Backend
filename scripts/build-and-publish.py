import os
import sys
import time
import subprocess

buildTarget = os.environ.get('BUILD_TARGET')

# Make sure the build target is set
if buildTarget is None:
    print('BUILD_TARGET has not been set.')
    sys.exit(1)

# Change to root directory
os.chdir("../")

# Login to docker registry
subprocess.call(["docker", "login", "-u", "aereusdev", "-p", "p7pfsGRe", "docker.aereusdev.com:5001"])

# The below should be used to build the webapp and put the compiled source in ./server/mobile
subprocess.call(["docker", "pull", "docker.aereusdev.com:5001/netric-client-web:" + buildTarget])
subprocess.call([
    "docker", "run", "--rm", "-v", "$PWD/server/mobile:/var/www/app/build",
    "-w", "/var/www/app",
    "--name=netric_web_client",
    "netric_web_client",
    "npm", "run", "build"
]);
subprocess.call([
    "docker", "run", "--rm", "-v", "$PWD/server/mobile:/var/www/app/build",
    "-w", "/var/www/app",
    "--name=netric_web_client",
    "netric_web_client",
    "chmod", "-R", "777", "./build/"
]);

# Not build the server which will include the built webapp above
subprocess.call(["docker", "build", "-t", "docker.aereusdev.com:5001/netric:" + buildTarget, "."])
subprocess.call(["docker", "push", "docker.aereusdev.com:5001/netric:" + buildTarget])

# Cleanup
call(["docker", "system", "prune", "-f"])