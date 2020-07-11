#!/bin/bash
set -e

# Update all dependencies
# composer install
# composer update

# Make sure we can write to required files
chown -R www-data:www-data data/log
chown -R www-data:www-data data/tmp

cd /var/www/html/bin

# Check if system dependencies are ready prior to starting and wait
RETRIES=480
echo "Checking dependencies"
until ./netric health/test-dependencies > /dev/null 2>&1 || [ $RETRIES -eq 0 ]; do
  echo "Waiting for dependencies to come up, $((RETRIES--)) remaining attempts..."
  sleep 1
done
echo "All dependencies are up"

# Run install in case this is our first run - it will exit gracefully if
# netric was previously installed
./netric setup/install --account=localtest --email=test@netric.com --username=test --password=password
