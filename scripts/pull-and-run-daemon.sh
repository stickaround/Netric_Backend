#!/usr/bin/env bash

# This script should be run on each server that needs to pull and install the latest version of docker

if [ -z "${DEPLOY_TARGET}" ]; then
    TARGET='stable'
else
    TARGET=${DEPLOY_TARGET}
fi

docker login -u aereusdev -p p7pfsGRe docker.aereusdev.com:5001
docker pull docker.aereusdev.com:5001/netric:${TARGET}

# Run the daemon with bin/netricd start-fg (start foreground)
docker stop netricd
docker rm netricd
docker run -P -d --restart=on-failure--name netricd \
    -e APPLICATION_ENV="production" --log-driver=syslog --log-opt tag=netric-${TARGET} \
    --log-opt syslog-facility=local2 docker.aereusdev.com:5001/netric:${TARGET} /start-daemon.sh