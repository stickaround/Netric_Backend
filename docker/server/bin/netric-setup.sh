#!/bin/bash
set -e

composer install
composer update

# Make sure we can write to required files
chown -R www-data:www-data data/log
chown -R www-data:www-data data/tmp

cd /var/www/html/bin

# Check if system dependencies are ready prior to starting and wait up to 30 seconds
RETRIES=60
until ./netric health/test > /dev/null 2>&1 || [ $RETRIES -eq 0 ]; do
  echo "Waiting for dependencies to come up, $((RETRIES--)) remaining attempts..."
  sleep 1
done

# Do a final test and exit if systems are still not ready
./netric health/test
if [[ $? != 0 ]]; then exit $?; fi

# Run install in case this is our first run - it will exit gracefully if
# netric was previously installed
./netric setup/install --username=test@netric.com --password=password

# Run update to make sure everything is at the latest version
./netric setup/update