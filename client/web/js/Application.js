/**
* @fileOverview Load instance of netric application
*
* @author:	Sky Stebnicki, sky.stebnicki@aereus.com; 
* 			Copyright (c) 2011 Aereus Corporation. All rights reserved.
*/
'use strict';

var Device = require("./Device");
var accountLoader = require("./account/loader");
var Router = require("./location/Router");
var MainController = require("./controller/MainController");
var location = require("./location/location");

/**
 * Application instance
 *
 * @param {netric.Account} account The current account netric is running under
 */
var Application = function(account) {
	/**
	 * Represents the actual netric account
	 *
	 * @public
	 * @var {Application.Account}
	 */
	this.account = account;

	/**
	 * Device information class
	 *
	 * @public
	 * @var {netric.Device}
	 */
	this.device = new Device();
};

/**
 * Static function used to load the application
 *
 * @param {function} cbFunction Callback function once app is loaded
 */
Application.load = function(cbFunction) {

	/*
	 * The first thing we need to do is load the current account so
	 * we can inject it as a dependency to the application instance.
	 */
	accountLoader.get(function(acct){

		// Create appliation instance for loaded account
		var app = new Application(acct);

		// Set global reference to application to enable netric.getApplication();
		netric.application_ = app;  

		// Callback passing initialized application
		if (cbFunction) {
			cbFunction(app);	
		}
	});
}

/**
 * Get the current account
 *
 * @return {netric.Account}
 */
Application.prototype.getAccount = function() {
	return this.account;
}

/**
 * Run the loaded application
 *
 * @param {DOMElement} domCon Container to render applicaiton into
 */
Application.prototype.run = function(domCon) {

	// Load up the new router
	var router = new Router();

	// Create the root route which is also the default
	router.addRoute("/", MainController, {}, domCon);

	// Setup location change listener
	location.setupRouter(router);

}

module.exports = Application;
