#!/bin/bash

set -e

export PHP_IDE_CONFIG="serverName=devel.netric.com"

cd /var/www/html/tests
../vendor/bin/phpunit -c phpunit.xml -d memory_limit=256M $@

# Make sure permissions are set in generated data files
chmod -R 777 ./tmp/
chmod -R 777 ./data/