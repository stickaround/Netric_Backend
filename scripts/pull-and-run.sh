#!/usr/bin/env bash

# This script should be run on each server that needs to pull and install the latest version of docker

if [ -z "${DEPLOY_TARGET}" ]; then
    TARGET='stable'
else
    TARGET=${DEPLOY_TARGET}
fi

docker login -u aereusdev -p p7pfsGRe docker.aereusdev.com:5001
docker pull docker.aereusdev.com:5001/netric:${TARGET}

# Run the webserver
docker stop netric
docker rm netric
docker run -d -p 50010:80 -p 50011:443 --restart=unless-stopped --name netric \
	-e APPLICATION_ENV="production" \
	docker.aereusdev.com:5001/netric:${TARGET}


#