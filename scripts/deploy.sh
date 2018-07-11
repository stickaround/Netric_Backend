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

# Update the docker stack
export APPLICATION_ENV=$APPLICATION_ENV
export APPLICATION_VER=$APPLICATION_VER
echo "APPLICATION_ENV ${APPLICATION_ENV}"
echo "APPLICATION_VER ${APPLICATION_VER}"

docker stack deploy -c docker-compose-stack.yml --with-registry-auth netric_com