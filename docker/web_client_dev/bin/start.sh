#!/bin/bash

set -e

cd /var/www/html
npm install
npm update
npm start