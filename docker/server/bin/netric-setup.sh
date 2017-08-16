#!/bin/bash
set -e

cd /var/www/html/
php composer.phar install
php composer.phar update

# Make sure we can write to required files
chown -R www-data:www-data data/log
chown -R www-data:www-data data/tmp

cd /var/www/html/bin

# Run install in case this is our first run - it will exit gracefully if
# netric was previously installed
./netric setup/install --username=test@netric.com --password=password

# Run update to make sure everything is at the latest version
./netric setup/update