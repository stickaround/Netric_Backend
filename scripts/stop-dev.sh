#!/usr/bin/env bash

DOCKER_IP="127.0.0.1"

#Remove existing lines from hosts
while IFS='' read -r line || [[ -n "$line" ]]; do
  echo "Removing existing domain $line"
  sudo sed -i '' '/'$line'/d' /etc/hosts
done < "hosts.conf"

# move to the docker directory
cd ../docker

# Run docker compose daemon
docker-compose -f docker-compose-dev.yml down