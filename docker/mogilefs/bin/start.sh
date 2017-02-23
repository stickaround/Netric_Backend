#!/bin/bash

if [ -z "${MGFS_STORE_PORT}" ]; then
    STORAGE_PORT='7500'
else
    STORAGE_PORT=${MGFS_STORE_PORT}
fi

if [ -z "${MGFS_STORE_HOST}" ]; then
    STORAGE_HOST='mogilestore'
else
    STORAGE_HOST=${MGFS_STORE_HOST}
fi

# Wait until the node is available
until $(curl --output /dev/null --silent --head --fail http://${STORAGE_HOST}:${STORAGE_PORT}); do
  >&2 echo "MogileStorage node (http://${STORAGE_HOST}:${STORAGE_PORT}) is unavailable - sleeping"
  sleep 1
done

# Replace any ports in the configs
sed -i "s/{{STORAGE_PORT}}/$STORAGE_PORT/g" /etc/mogilefs/mogstored.conf

# Run tracker for setup
sudo -u mogile mogilefsd --daemon -c /etc/mogilefs/mogilefsd.conf

# Setup devices
mogadm host add node1 --ip=${STORAGE_HOST} --port=${STORAGE_PORT} --status alive
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