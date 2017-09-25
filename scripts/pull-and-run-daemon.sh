#!/usr/bin/env bash

# This script should be run on each server that needs to pull and install the latest version of docker

if [ -z "$1" ]; then
    TARGET='stable'
else
    TARGET=$1
fi

docker login -u aereusdev -p p7pfsGRe dockerhub.aereusdev.com
docker pull dockerhub.aereusdev.com/netric:${TARGET}

# Run the daemon with bin/netricd start-fg (start foreground)
docker stop netricd
docker rm netricd

docker run -P -d --restart=on-failure --name netricd \
    -e APPLICATION_ENV="production" \
    dockerhub.aereusdev.com/netric:${TARGET} /start-daemon.sh

# Optionally use syslog for the log driver
#docker run -P -d --restart=on-failure --name netricd \
#    -e APPLICATION_ENV="production" --log-driver=syslog --log-opt tag=netric-${TARGET} \
#    --log-opt syslog-facility=local2 dockerhub.aereusdev.com/netric:${TARGET} /start-daemon.sh

# Run setup in the background and it will die when finished
docker stop netricsetup
docker rm netricsetup
docker run -P -d --name netricsetup -e APPLICATION_ENV="production" \
    dockerhub.aereusdev.com/netric:${TARGET} /netric-setup.sh