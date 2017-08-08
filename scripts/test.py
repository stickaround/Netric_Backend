import subprocess
import os
import sys
import argparse

# Change to docker directory
os.chdir("../docker")

print("Running server tests...")

#parser = argparse.ArgumentParser()
#parser.add_argument('--watch', '-w')
#parser.add_argument('--integration', '-i')
#parser.add_argument('--unit', '-u')
#rgs = parser.parse_args()

#if args.watch is not None:
#    # Append any arguments passed from the user
#    subprocessArgs = ["pywatch", "'docker exec docker_netric_server_1 /netric-tests.sh'", "../lib", "../tests"]
#else:

# Append any arguments passed from the user
subprocessArgs = ["docker", "exec", "docker_netric_server_1", "/netric-tests.sh"]
if sys.argv.count:
    userArgs = list(sys.argv)
    userArgs.pop(0)
    subprocessArgs += userArgs


# Call test within the docker container
subprocess.call(subprocessArgs)