#!/usr/bin/env bash

# move to the docker directory
cd ../docker

# Login to the aereus docker host
docker login -u aereusdev -p p7pfsGRe docker.aereusdev.com:5001

# Pull the latest versions
docker-compose -f docker-compose-integ.yml pull

# First rebuild
docker-compose -f docker-compose-integ.yml build

# Now stop
# First rebuild
docker-compose -f docker-compose-integ.yml down

# Start the newly built images
docker-compose -f docker-compose-integ.yml up -d

# Wait, then run setup
echo "Waiting 10 seconds before running setup..."
sleep 10

docker exec docker_netric_server_1 /netric-setup.sh

echo "Done. Go to http://integ.netric.com and use 'test@netric.com' and 'password' to log in."