## To Start Netric Services
    
1. Install docker toolbox

    https://www.docker.com/products/docker-toolbox

2. Build the environment
    
    ./build.sh
    
3. Run dev environment

    ./dev.sh

4. Run Netric Setup (first time & when updating the database)

    docker exec -it docker_netric_web_1 /netric-setup.sh
    
5. Load netric in the browser

    http://devel.netric.com
    
## To Stop All Services
    
    docker-compose down

## To Run Server Tests

    docker exec -it docker_netric_web_1 /netric-tests.sh

You can pass any command line options for phpunit like
    
    docker exec -it docker_netric_web_1 /netric-tests.sh NetricTest/Entity

which will run all unit tests in the NetricTest/Entity folder.

## To log into docker container
    
    docker exec -it docker_netric_web_1 /bin/bash
    
Now you can run phpunit just like you would from a VM
    
    cd /var/www/html/tests
    ../vendor/bin/phpunit -c phpunit.xml