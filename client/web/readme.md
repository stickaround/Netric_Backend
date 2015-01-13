WebApp
===========

Installation
------------

First, install [node.js](http://nodejs.org/).

Then, use NPM to install grunt client

    sudo npm -g install grunt-cli

Install react command line tools

	npm install -g react-tools

Next, clone this repository using Git:

    git clone ...
    cd ...

And then install the remaining build dependencies locally:

    npm install

This will read the dependencies and the devDependencies (which contains our build requirements) from package.json and install everything needed into a folder called node_modules/.

Grunt is used for automating builds and invoking livereload to make dev much easier and cooler

	sudo npm install -g grunt-cli

Now install bower to add third party vendor dependencies
	
	sudo npm install -g bower
	bower install

To build the project for testing run:
	
	grunt

This will build the project and start a liveupdate server locally for dynamic refreshes and compilation every
time you make a change to a file.

To begin developement just start the node http server with.

    npm start

Then load http://localhost:8000/src/

To run automated tests automatically as you make changes then run

	npm test

Which will launch a browser window in the background (don't minimize it on mac because mac will limit memory)

When you're ready to push the app into production, just run the compile command:

    grunt compile
