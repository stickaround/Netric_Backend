#!/bin/bash

set -e

echo $@

cd /var/www/html

php composer.phar update --dev

vendor/bin/phpunit -c tests/phpunit.xml