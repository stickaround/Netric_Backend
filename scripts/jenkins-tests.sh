#!/usr/bin/env bash
#export DOCKER_HOST=tcp://localhost:2375

# Move up to the docker directory
cd ../docker

# Just in case docker is running, stop any instances
docker-compose -f docker-compose-test.yml down

# Build containers
cd ../scripts
./build-dev.sh

# Bring docker up
cd ../docker
docker-compose -f docker-compose-test.yml up -d

# Wait 30 seconds, then run setup
sleep 30
docker exec docker_netric_server_1 /netric-setup.sh

# Now run tests
cd ../scripts
./test.sh

# Cleanup
cd ../docker
docker-compose -f docker-compose-test.yml down