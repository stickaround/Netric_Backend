#!/usr/bin/env bash

# Make sure that the data directory has the right owner
chown -R www-data /var/www/html/data/

# If run setup was passed as an environment variable then execute before starting the server
if [ "${RUN_SETUP_INSTALL}" ]; then
	echo "Running netric setup"
	/netric-setup.sh
fi

# Check if we should run updates prior to starting
if [ "${RUN_SETUP_UPDATE}" ]; then
    cd /var/www/html/bin
    ./netric setup/update
    cd /var/www/html
fi

# start apache
apache2-foreground