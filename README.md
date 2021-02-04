# Netric

This is the source code for both the client and the server.

## Running Local Development Server

Note: On some windows installations python is invoked by typing 'py'

1. Install docker native (not docker toolbox)

   http://www.docker.com

2. Log into dockerhub

   docker login dockerhub.aereus.com
   user: aereusdev
   password: p7pfsGRe

3. Run dev environment

   docker-compose up

### Testing

Run tests within the container by executing.

    docker-compose exec netric_server APPLICATION_ENV=testing vendor/bin/phpunit

### Accessing Services

- http://localhost:80 - load netric
- http://localhost:8888 - view performance profiles
- localhost:5432 - connect to postgres with user vagrant and password vagrant

### Debugging

If using PHPStorm simply start listening on port 9000 for xdebug

### Server Shell Access

You can remote into the server with the follwoing command:

    docker-compose exec netric_server bash

This will spawn a new interactive process in the container and run bash which
is similar what happens when you SSH into a remote machine.

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
- `/vendor` - third party libraries
- `/data` - non-executable stored data
- `/bin` - binary scripts to run on the server
- `/docker` - docker files used for building and working with containers
