#!/usr/bin/env bash

DOCKER_IP="127.0.0.1"

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

# move to the docker directory
cd ../docker

# Run docker compose daemon
docker-compose -f docker-compose-dev.yml up -d

# Wait, then run setup
echo "Waiting 10 seconds before running setup..."
sleep 10
docker exec -it docker_netric_server_1 /netric-setup.sh

# Show logs in terminal
docker-compose -f docker-compose-dev.yml logs