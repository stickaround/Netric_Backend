#!/usr/bin/env bash

cd ../docker/

echo "Running server tests..."
docker exec docker_netric_server_1 /netric-tests.sh  $@