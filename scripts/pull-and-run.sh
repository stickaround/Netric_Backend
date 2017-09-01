#!/usr/bin/env bash

# This script should be run on each server that needs to pull and install the latest version of docker

if [ -z "${DEPLOY_TARGET}" ]; then
    TARGET='stable'
else
    TARGET=${DEPLOY_TARGET}
fi

docker login -u aereusdev -p p7pfsGRe dockerhub.aereusdev.com
docker pull dockerhub.aereusdev.com/netric:${TARGET}

# Run the webserver
docker stop netric
docker rm netric
docker run -d -p 50010:80 -p 50011:443 --restart=unless-stopped --name netric \
	-e APPLICATION_ENV="production" \
   --log-driver=syslog --log-opt tag=netric-${TARGET} --log-opt syslog-facility=local2 \
	dockerhub.aereusdev.com/netric:${TARGET}