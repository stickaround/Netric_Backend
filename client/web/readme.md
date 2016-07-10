WebApp
===========

Installation
------------

First, install [node.js](http://nodejs.org/).

Then, use NPM to install webpack

    sudo npm -g install webpack

Install react command line tools

	sudo npm install -g react-tools

And then install the remaining build dependencies locally:

    npm install

Install bower using npm (if bower is not installed):

    sudo npm install -g bower

Open an additional shell window (leave the above command running) and run

    npm start
    
This will build the project and start a hotupdate server locally for dynamic refreshes and compilation every time you make a change to a file.

Then load http://localhost:8080 in your browser.

To run automated tests automatically as you make changes, open another shell and type:

	npm test

Which will launch a browser window in the background (don't minimize it on mac because mac will limit memory)

When you're ready to push the app into production, just run the compile command:

    npm run build

This will build the app and put it into ./dist which can be copied to the server and to ../devices/web
