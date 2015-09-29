WebApp
===========

Installation
------------

First, install [node.js](http://nodejs.org/).

Then, use NPM to install grunt client

    sudo npm -g install grunt-cli

Install react command line tools

	npm install -g react-tools

And then install the remaining build dependencies locally:

    npm install

This will read the dependencies and the devDependencies (which contains our build requirements) from package.json and install everything needed into a folder called node_modules/.

Grunt is used for automating builds and invoking livereload to make dev much easier and cooler

	sudo npm install -g grunt-cli

Now Build the Project
	
	grunt

This will build the project and start a liveupdate server locally for dynamic refreshes and compilation every
time you make a change to a file.

Open an additional shell window (leave the above command running) and run

    npm start

Then load http://localhost:8000 in your browser.

To run automated tests automatically as you make changes, open another shell and type:

	npm test

Which will launch a browser window in the background (don't minimize it on mac because mac will limit memory)

When you're ready to push the app into production, just run the compile command:

    grunt compile

This will build the app and put it into ./dist which can be copied to the server and to ../devices/web