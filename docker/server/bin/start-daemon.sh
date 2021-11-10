#!/usr/bin/env bash

chown -R www-data /var/www/html/data/
chmod +x /var/www/html/bin/netricd
chmod +x /var/www/html/bin/netric
chmod +x /var/www/html/bin/worker

# start the daemon in the foreground
cd /var/www/html/bin
#./netricd start-fg
./worker