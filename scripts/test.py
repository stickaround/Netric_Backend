import subprocess
import os
import sys

# Change to docker directory
os.chdir("../docker")

print("Running server tests...")

# Append any arguments passed from the user
subprocessArgs = ["docker", "exec", "docker_netric_server_1", "/netric-tests.sh"]
if sys.argv.count > 1:
	userArgs = list(sys.argv)
	userArgs.pop(0)
	subprocessArgs += userArgs

# Call test within the docker container
subprocess.call(subprocessArgs)