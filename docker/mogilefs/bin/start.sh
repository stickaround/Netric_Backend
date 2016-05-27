#!/bin/bash

# Wait until the node is available
until $(curl --output /dev/null --silent --head --fail http://mogilestorage:7500); do
  >&2 echo "MogileStorage node is unavailable - sleeping"
  sleep 1
done

# Run tracker for setup
sudo -u mogile mogilefsd --daemon -c /etc/mogilefs/mogilefsd.conf

# Setup devices
mogadm host add node1 --ip=mogilestorage --port=7500 --status alive
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