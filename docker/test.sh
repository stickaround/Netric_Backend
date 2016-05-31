#!/usr/bin/env bash

eval $(docker-machine env)

docker exec -it docker_netric_web_1 /netric-tests.sh  $@
