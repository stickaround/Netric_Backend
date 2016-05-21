#!/bin/bash

set -e

cd /var/www/html/tests
../vendor/bin/phpunit -c phpunit.xml $@