## To Run Netric Services
    
1. Install docker toolbox

2. Edit your /etc/hosts file and make devel.netric.com point to the server address printed in
    
    docker-machine env
    
3. Run docker compose to start all services

    docker-compose up

4. Load netric in the browser

    http://delvel.netric.com
    
## To Stop Netric Services
    
    docker-compose down

## To log into docker container for running unit tests and setup
    
    docker exec -it docker_netric_web_1 /bin/bash