#!/bin/bash
# Make sure that the data directory has the right owner
chown -R www-data /var/www/html/data/

set -e

export PHP_IDE_CONFIG="serverName=devel.netric.com"

# Make sure permissions are set in generated data files
chmod -R 777 /var/www/html/tests/tmp/
chmod -R 777 /var/www/html/tests/data/

cd /var/www/html/tests
../vendor/bin/phpunit -c phpunit.xml -d memory_limit=256M $@

# Make sure permissions are set in generated data files
chmod -R 777 /var/www/html/tests/tmp/
chmod -R 777 /var/www/html/tests/data/