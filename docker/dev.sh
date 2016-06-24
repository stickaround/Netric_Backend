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

# Fix IP if cisco VPN broke it
# This does not appear to be working so we need to check on it
echo "sudo route -nv add -net '$DOCKER_IP/24' -interface vboxnet0"
sudo route -nv add -net "$DOCKER_IP/24" -interface vboxnet0

# Run docker compose
docker-compose up -d