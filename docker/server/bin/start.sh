#!/usr/bin/env bash

# Wait until the node is available
#until $(curl --output /dev/null --silent --head --fail http://elk:9200); do
#  >&2 echo "ELK node (http://elk:${STORAGE_PORT}) is unavailable - sleeping"
#  sleep 1
#done

#curl -XPUT 'http://elk:9200/_template/filebeat?pretty' -d@/etc/filebeat/filebeat.template.json

# Enable debugging
#HOST_IP=$(/sbin/ip route|awk '/default/ { print $3 }')
#HOST_IP="207.66.231.9"
#echo "xdebug.remote_host=$HOST_IP" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
#echo "xdebug.remote_enable=1"  >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
#echo "xdebug.remote_connect_back=1" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
#echo "xdebug.remote_autostart=1" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

# Make sure that the data directory has the right owner
chown -R www-data /var/www/html/data/

# If run setup was passed as an environment variable then execute before starting the server
if [ "${RUN_SETUP_INSTALL}" ]; then
	echo "Running netric setup after resting for 10 seconds"
	sleep 30
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