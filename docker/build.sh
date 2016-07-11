#!/usr/bin/env bash

# Remove stopped containers
docker rm $(docker ps -a -q)

# Remove unused images
docker rmi $(docker images | grep "^<none>" | awk "{print $3}")

# Copy source from server into container
echo "Copying source"
rm -rf server/dist/
cp -R ../server/ server/dist/

echo "Building containers"
docker-compose -f docker-compose-dev.yml build

echo "Cleaning"
rm -rf server/dist/

echo "DONE! Run ./dev.sh to begin development"