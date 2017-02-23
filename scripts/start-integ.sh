#!/usr/bin/env bash

DOCKER_IP="127.0.0.1"

# move to the docker directory
cd ../docker

# Run docker compose daemon
docker-compose -f docker-compose-integ.yml up -d

# Wait, then run setup
echo "Waiting 10 seconds before running setup..."
sleep 10
docker exec -it docker_netric_server_integ_1 /netric-setup.sh

echo "Done. Go to http://devel.netric.com and use 'test@netric.com' and 'password' to log in."