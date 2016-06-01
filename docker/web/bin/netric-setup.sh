#!/bin/bash

set -e

cd /var/www/html
php

cd /var/www/html/bin

# Run install in case this is our first run - it will exit gracefully if
# netric was previously installed
./netric setup/install --username=test@netric.com --password=password

# Run update to make sure eveything is at the latest
./netric setup/update