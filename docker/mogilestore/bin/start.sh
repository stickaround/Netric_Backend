#!/bin/bash

if [ -z "${MGFS_STORE_PORT}" ]; then
    STORAGE_PORT='7500'
else
    STORAGE_PORT=${MGFS_STORE_PORT}
fi

if [ -z "${MGFS_TRACKER_PORT}" ]; then
    TRACKER_PORT='7001'
else
    TRACKER_PORT=${MGFS_TRACKER_PORT}
fi

# Replace any ports in the configs
sed -i "s/{{STORAGE_PORT}}/$STORAGE_PORT/g" /etc/mogilefs/mogstored.conf
sed -i "s/{{TRACKER_PORT}}/TRACKER_PORT/g" /etc/mogilefs/mogilefsd.conf

# Run tracker for setup
sudo -u mogile mogilefsd --daemon -c /etc/mogilefs/mogilefsd.conf

# Setup devices
mogadm host add node1 --ip=mogilestorage --port=${STORAGE_PORT} --status alive
mogadm host list
mogadm device add node1 1
mogadm device add node1 2

# Add netric classes
mogadm domain add netric
mogadm class add netric userfiles --mindevcount=2

# Stop daemonized verison of tracker
pkill mogilefsd

mogadm check

# Run tracker
sudo -u mogile mogilefsd -c /etc/mogilefs/mogilefsd.conf