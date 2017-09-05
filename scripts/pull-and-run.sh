#!/usr/bin/env bash

# This script should be run on each server that needs to pull and install the latest version of docker

if [ -z "${DEPLOY_TARGET}" ]; then
    TARGET='stable'
else
    TARGET=${DEPLOY_TARGET}
fi

docker login -u aereusdev -p p7pfsGRe dockerhub.aereusdev.com
docker pull dockerhub.aereusdev.com/netric:${TARGET}

# Rename the old container so we can keep it running while we bring up the new container
docker rename netric netric_dep

# Run the webserver
docker run -d -p 80 -p 443 --restart=unless-stopped --name netric \
    -e APPLICATION_ENV="production" \
    -e VIRTUAL_HOST=aereus.netric.com \ 
    -e LETSENCRYPT_HOST=aereus.netric.com \ 
    -e LETSENCRYPT_EMAIL=sky.stebnicki@netric.com \ 
	dockerhub.aereusdev.com/netric:${TARGET}

# Stop the old container and cleanup
docker stop netric_dep
docker rm netric_dep