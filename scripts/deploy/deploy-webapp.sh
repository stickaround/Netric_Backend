#!/bin/bash
# Handle copying everything in /server to to a remote server
# 
# USAGE:
# 
# deply-webapp target-server [target-dir]
# 
# The target-server must be a valid netric web server and the local user must have
# SSH access to that server with a remote user who is a member of the webadmin group.
#
# [target-dir] is an optional qualifier that will be appended to the standard install
# directory. For exmaple, if the standard install dir were /var/www/html/netric then
# a target-dir of "beta" would install the files in /var/www/html/netric-beta
#
# WARNING: This script does not build the webapp so first go to the client/web directory
# and run grunt compile first.

TARGET_SERVER=""
LOCAL_DIR="../../client/web/dist/"
REMOTE_DIR="/var/www/html/netric"

# Make sure a server was passed
if [ -z "$1" ] 
then
    echo "No target server specified. Please read the header of this script."
    exit
else
	TARGET_SERVER=$1
fi

# Add optinal target-dir postfix to standard install dir
if [ "$2" ] 
then
	REMOTE_DIR=$REMOTE_DIR"-"$2
fi

# Deploy the webapp if it is built
if [ "$(ls -A $LOCAL_DIR)" ]; then
	echo "Deploying to $TARGET_SERVER:$REMOTE_DIR"
	rsync -rvzh --chmod=ug+rwx,o=rx $LOCAL_DIR "$TARGET_SERVER:$REMOTE_DIR"
else
    echo "$LOCAL_DIR is empty. Please build the webapp first by running 'grunt compile'"
fi

