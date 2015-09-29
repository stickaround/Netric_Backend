#!/bin/bash
# Build the webapp and copy it to the devices directory and run cordova build
#
# USAGE:
#
# ./deplay-devoces.sh
WEBAPP_DIR="../../client/web/"

# First build the webapp
grunt compile

# Copy everything in dist to the device web directory
cp -rf ./dist/* ../devices/wwww/

# Go to devices directory
cd ../devices

# Build
cordova build --release

# Clean android release
rm platforms/android/build/outputs/apk/android-release.apk

# Sign android
jarsigner -verbose -sigalg SHA1withRSA -digestalg SHA1 -keystore keys/netric-release-key.keystore platforms/android/build/outputs/apk/android-release-unsigned.apk netric
~/Library/Android/sdk/build-tools/22.0.1/zipalign -v 4 platforms/android/build/outputs/apk/android-release-unsigned.apk platforms/android/build/outputs/apk/android-release.apk