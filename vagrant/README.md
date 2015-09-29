We use a vagrant image for all local development. Follow the directions in this document and you will be up and running with your very own copy of the netric server in no time.

# First Time Setup

## 1. Install vagrant
Go to http://www.vagrantup.com/downloads.html, download and install vagrant.

## 2. Install vagrant hostupdater plugin

We use this plugin to automatically update your machines host file so that when you spin up the virtual machine, it will change your local host to point devel.awayable.com to your vm instance. Pretty cool huh?

	vagrant plugin install vagrant-hostsupdater

## 3. Add the vagrant box

Add the base vagrant box from our development servers. You only need to do this once.

	vagrant box add netric/centos67 http://tools.aereusdev.com/vagrant-centos-6.7.box 


# Running Vagrant

Congratulations, you now have vagrant setup. All you have to do is type:

	vagrant up

from the command line and a full-fledged version of netric will be accessible through devel.netric.com.

It automatically mounts the server to ../server which is pretty cool because you can edit files locally and have them served automatically by the VM.

If you need to log into the VM for anything (such as running unit tests), simply type:

	vagrant ssh

You can navigate to the server directory by going to /var/www/html/netric once you are in the VM.
