#!/bin/bash

set -e

export PHP_IDE_CONFIG="serverName=devel.netric.com"

cd /var/www/html/tests
../vendor/bin/phpunit -c phpunit.xml $@