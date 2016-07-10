#!/usr/bin/env bash
export DOCKER_HOST=tcp://localhost:2375

# Move up to the docker directory
cd ../docker

# Just in case docker is running, stop any instances
docker-compose -f docker-compose-test.yml down
./build.sh
docker-compose -f docker-compose-test.yml up -d
sleep 30
docker exec docker_netric_server_1 /netric-setup.sh
./test.sh
docker-compose -f docker-compose-test.yml down