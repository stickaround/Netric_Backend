# Netric
This is the source code for both the client and the server.

## Installation & Development

To get started developing, you will need to have installed VirtualBox and Vagrant.

Once both those are installed, perform the following:

1. Follow instructions in ./vagrant/README.md
2. vagrant ssh
3. cd /var/www/html/netric
4. php composer.phar install
5. cd ./system
6. php schema_updates.php
7. php createdefaultaccount.php
8. sudo service memcached restart

That's all there is to it, you should now be able to navigate to devel.netric.com on your workstation.

Log in with "test@myaereus.com" and "test" as the password (or whatever createdefaultaccount.php says)

## The Web Client

We are in the process of splitting out all the server compoenents from the client.

V2 of the netric UI is being built in the ./client/web directory of this repo.

Once you have vagrant running the server (see Installation & Development above), navigate to the client directory
and follow the instructions in READEME.md.
