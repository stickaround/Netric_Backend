#!/usr/bin/env bash

# Get any local server variables (if using docker machine)
eval $(docker-machine env)

# Step into server directory
cd ../server

#echo "Running composer to make sure everything is updated"
#php ./composer.phar install
#php ./composer.phar update

# Move back to docker
cd ../docker/

# Copy source from server into container
echo "Copying source"
rm -rf web/dist/
cp -R ../server/ web/dist/

echo "Building containers"
docker-compose build

echo "Cleaning"
rm -rf web/dist/

echo "DONE! Run ./dev.sh to begin development"