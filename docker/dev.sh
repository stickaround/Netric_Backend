#!/usr/bin/env bash

# Setup Docker Machine and Hosts
docker-machine start
eval $(docker-machine env)

DOCKER_IP=$(docker-machine ip default)
echo "default ip: $DOCKER_IP"

#Remove existing lines from hosts
while IFS='' read -r line || [[ -n "$line" ]]; do
  echo "Removing existing domain $line"
  sudo sed -i '' '/'$line'/d' /etc/hosts
done < "hosts.conf"

#Add new hosts to the bottom of the file as root
while IFS='' read -r line || [[ -n "$line" ]]; do
  echo "Adding entry $DOCKER_IP $line"
  sudo bash -c "echo \"$DOCKER_IP $line\" >>/etc/hosts"
done < "hosts.conf"

# Run docker compose
docker-compose up -d