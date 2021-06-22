# Netric

This is the source code for both the client and the server.

## Running Local Development Server

1. Install docker

   http://www.docker.com

2. Run dev environment

   docker-compose up

### Testing

Run tests within the container by executing.

    docker-compose exec netric_server composer test

### Accessing Services

- http://localhost:80 - load netric
- http://localhost:8888 - view performance profiles
- http://localhost:5432 - connect to postgres with user vagrant and password vagrant

### Debugging

If using PHPStorm simply start listening on port 9000 for xdebug

### Server Shell Access

You can remote into the server with the follwoing command:

    docker-compose exec netric_server bash

This will spawn a new interactive process in the container and run bash which
is similar what happens when you SSH into a remote machine.

## API

We are transitioning away from hand-build SDKs and REST APIs for services. Instead, we are using apache thrift. Services must be defined in the /thrift directry, and all API processors need to be generated on each change by running:

`docker-compose exec netric_server composer build-api`

## The Client

Netric can have multiple clients. The main web client is build separately and deployed by
copying the source from the built webapp into the server directory /server/v2.

## Clients are split into separate repos:

netric.client.web: The main webapp
netric.client.hybrid: Native device applications

Each of the respective client repos will contain build and deployment instructions.

## Directory Structure

- `/src` - all classes and functions
- `/public` - all served assets and where apache will look for index files
- `/thrift` - API definition for apache thrift calls
- `/vendor` - third party libraries
- `/data` - non-executable stored data
- `/bin` - binary scripts to run on the server
- `/docker` - docker files used for building and working with containers
