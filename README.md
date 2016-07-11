# Netric
This is the source code for both the client and the server.

## Running Local Development Server

1. Install docker native (not docker toolbox)

    http://www.docker.com/products/overview

2. Build the environment
    
    ./server/build-dev.sh
    
3. Run dev environment

    ./start-dev.sh
    
4. Load netric in the browser

    http://devel.netric.com

That's all there is to it, you should now be able to navigate to 
devel.netric.com on your workstation.

Log in with "test@netric.com" and "password" as the password.

## The Client

We are in the process of splitting out all the server components from the client. 
Once this is done we will actually split it into a separate repo.

V2 of the netric UI is being built in the ./client/web directory of this repo.

Once you have vagrant running the server (see Installation & Development above), 
navigate to the client directory and follow the instructions in READEME.md.

## Scripts found in /scripts

build-dev.sh - build a local development environment
start-dev.sh - start a local development server
test.sh - run server tests

jenkins-tests.sh - script to execute all tests in jenkins build