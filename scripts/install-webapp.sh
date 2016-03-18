#!/bin/sh

# This handles copying the built webapp to the root of the server locally for development

echo "Installing WebApp...";
cd ../client/web
npm install
grunt compile
cp -r dist/* ../../server/
echo "[done]";