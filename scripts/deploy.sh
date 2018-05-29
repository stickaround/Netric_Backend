#!/usr/bin/env bash

if [ -z "$1" ]; then
    APPLICATION_ENV='production'
else
    APPLICATION_ENV=$1
fi

if [ -z "$2" ]; then
    APPLICATION_VER='latest'
else
    APPLICATION_VER=$2
fi

docker login -u aereusdev -p p7pfsGRe dockerhub.aereusdev.com

docker pull dockerhub.aereusdev.com/netric:${APPLICATION_VER}

# Update the docker stack
docker stack deploy -c docker-compose-stack.yml --with-registry-auth netric_com