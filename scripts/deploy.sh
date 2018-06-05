#!/usr/bin/env bash

if [ -z "$1" ]; then
    echo "First param MUST be the environment we are running in like integration or staging"
    exit 1
else
    APPLICATION_ENV=$1
fi

if [ -z "$2" ]; then
    echo "Second param must be the unique build number to deploy"
    exit 1
else
    APPLICATION_VER=$2
fi

docker login -u aereusdev -p p7pfsGRe dockerhub.aereusdev.com

docker pull dockerhub.aereusdev.com/netric:${APPLICATION_VER}

# Update the docker stack
docker stack deploy -c docker-compose-stack.yml --with-registry-auth netric_com