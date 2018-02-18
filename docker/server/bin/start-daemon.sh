#!/usr/bin/env bash

chown -R www-data /var/www/html/data/
chmod +x /var/www/html/bin/netricd
chmod +x /var/www/html/bin/netric

# If run update was passed as an environment variable then execute before starting the server
if [ "${RUN_UPDATE}" ]; then
	echo "Running netric update before starting the daemon"
	/netric-update.sh
fi

# start the daemon in the foreground
cd /var/www/html/bin
./netricd start-fg