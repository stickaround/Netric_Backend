#!/bin/bash

set -e

cd /var/www/html
rm -r -f node_modules
npm install
npm update
npm start