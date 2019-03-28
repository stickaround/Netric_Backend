#!/bin/bash
# Make sure that the data directory has the right owner
chown -R www-data /var/www/html/data/

set -e

export PHP_IDE_CONFIG="serverName=devel.netric.com"

# Make sure permissions are set in generated data files
chmod -R 777 /var/www/html/.reports/
chmod -R 777 /var/www/html/test/data/

cd /var/www/html
vendor/bin/phpunit -d memory_limit=256M $@

# Perform static analysis
composer lint-phpcs || true
composer lint-phpmd || true

# Make sure permissions are set in generated data files
chmod -R 777 /var/www/html/.reports/
chmod -R 777 /var/www/html/test/data/