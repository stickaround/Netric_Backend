#!/usr/bin/env bash

if [ -z "$1" ]; then
    APP_ENV='production'
else
    APP_ENV=$1
fi

docker login -u aereusdev -p p7pfsGRe dockerhub.aereusdev.com
docker pull dockerhub.aereusdev.com/netric:latest

# Run setup in the background and it will die when finished
docker stop netricsetup
docker rm netricsetup

# Production has all containers linked via DNS as opposed to a created network like in integ/stage
if [ APP_ENV="production" ];  then
    docker run -P -it --name netricsetup -e APPLICATION_ENV="${APP_ENV}" \
        dockerhub.aereusdev.com/netric:latest /netric-setup.sh
else
    docker run -P -it --name netricsetup -e APPLICATION_ENV="${APP_ENV}" \
        --network=aereusdev_default \
        --link memcached:memcached \
        --link gearmand:gearmand \
        --link mail:mail \
        --link pgsql:pgsql \
        --link mogilefs:mogilefs \
        --link mogilestore:mogilestore \
        --link statsd:statsd \
        dockerhub.aereusdev.com/netric:latest /netric-setup.sh
fi

if [ $? -eq 0 ]; then
    echo Setup and Updates Finished
else
    echo Setup Failed
    return 1
fi