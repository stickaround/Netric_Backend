#!/usr/bin/env bash

# This script should be run on each server that needs to pull and install the latest version of docker

if [ -z "$1" ]; then
    TARGET='stable'
else
    TARGET=$1
fi

docker login -u aereusdev -p p7pfsGRe dockerhub.aereus.com
docker pull dockerhub.aereus.com/netric:${TARGET}

# Run the daemon with bin/netricd start-fg (start foreground)
docker stop netricd
docker rm netricd

# Optionally use syslog for the log driver
docker run -P -d --restart=on-failure --name netricd \
   -e APPLICATION_ENV="production" --log-driver=syslog --log-opt tag=netric-${TARGET} \
   --log-opt syslog-facility=local2 dockerhub.aereus.com/netric:${TARGET} /start-daemon.sh
