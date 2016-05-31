#!/usr/bin/env bash

eval $(docker-machine env)

rm -rf web/src/
cp -R ../server/ web/src/

docker-compose build