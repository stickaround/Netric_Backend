#!/usr/bin/env bash

# This script should be run on each server that needs to pull and install the latest version of docker

if [ -z "$1" ]; then
    TARGET='stable'
else
    TARGET=$1
fi

docker login -u aereusdev -p p7pfsGRe dockerhub.aereusdev.com
docker pull dockerhub.aereusdev.com/netric:${TARGET}

# Run setup in the background and it will die when finished
docker stop netricsetup
docker rm netricsetup
docker run -P -d --name netricsetup -e APPLICATION_ENV="production" \
    --log-driver=syslog --log-opt tag=netric-daemon \
    --log-opt syslog-facility=local2 \
    dockerhub.aereusdev.com/netric:${TARGET} /netric-setup.sh

if [ $? -eq 0 ]; then
    echo Setup and Updates Finished
else
    echo Setup Failed
    return 1
fi