#!/usr/bin/env bash

DOCKER_IP="127.0.0.1"

# move to the docker directory
cd ../docker

# Run docker compose daemon
docker-compose -f docker-compose-integ.yml down