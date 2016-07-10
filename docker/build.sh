#!/usr/bin/env bash

# Get any local server variables (if using docker machine)
eval $(docker-machine env)

# Step into server directory
cd ../server

#echo "Running composer to make sure everything is updated"
#php ./composer.phar install
#php ./composer.phar update

# Remove stopped containers
docker rm $(docker ps -a -q)

# Remove unused images
docker rmi $(docker images | grep "^<none>" | awk "{print $3}")

# Move back to docker
cd ../docker/

# Copy source from server into container
echo "Copying source"
rm -rf server/dist/
cp -R ../server/ server/dist/

echo "Building containers"
docker-compose -f docker-compose-dev.yml build

echo "Cleaning"
rm -rf server/dist/

echo "DONE! Run ./dev.sh to begin development"