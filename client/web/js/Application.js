/**
* @fileOverview Load instance of netric application
*
* TODO:
*	1. Heartbeat to determine connection status through device
* 	2. Session checker to make sure it is still valid and redirect to login if needed
*	3. Universal login to get instance URL from an email address (requires key)
*
* @author:	Sky Stebnicki, sky.stebnicki@aereus.com; 
* 			Copyright (c) 2014-2015 Aereus Corporation. All rights reserved.
*/
'use strict';

var Device = require("./Device");
var server = require("./server");
var accountLoader = require("./account/loader");
var Router = require("./location/Router");
var MainController = require("./controller/MainController");
var location = require("./location/location");
var LoginController = require("./controller/LoginController");
var sessionManager = require("./sessionManager");
var BackendRequest = require("./BackendRequest");
var localData = require("./localData");

/**
 * Application instance
 */
var Application = function() {
	
	/**
	 * Represents the actual netric account
	 *
	 * @public
	 * @var {Application.Account}
	 */
	this.account = null;

	/**
	 * Device information class
	 *
	 * @public
	 * @var {netric.Device}
	 */
	this.device = new Device();

	/**
	 * DOM Node to render this applicaiton into
	 *
	 * @private
	 * @var {DOMNode}
	 */
	this.appDomNode_ = null;
};

/**
 * Static function used to load the application
 *
 * @param {function} cbFunction Callback function once app is loaded
 */
Application.load = function(cbFunction) {

	var application = new Application();

	// Update server host if not already set from the local settings
	if (!server.host) {
		// If the local setting is missing it will return null
		server.host = localData.getSetting("server.host");
	}

	/*
	 * The first thing we need to do is load the current account so
	 * we can inject it as a dependency to the application instance.
	 */
	if (sessionManager.getSessionToken()) {
		application.loadAccount(function(acct){
			// Callback passing initialized application
			if (cbFunction) {
				cbFunction(application);
			}
		});
	} else if (cbFunction) {
		/*
		 * Return right away, the user will need to authenticate before 
		 * we continue loading the account.
		 */
		 cbFunction(application);
	}
}

/**
 * Static function used to load the application
 *
 * @param {function} cbFunction Callback function once app is loaded
 */
Application.prototype.loadAccount = function(cbFunction) {

	/*
	 * The first thing we need to do is load the current account so
	 * we can inject it as a dependency to the application instance.
	 */
	accountLoader.get(function(acct){

		// TODO: check for error

		// Set the active account for this application
		this.setAccount(acct);

		// Callback passing initialized application
		if (cbFunction) {
			cbFunction(acct);
		}
	}.bind(this));
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
 * Set the current account for this application
 *
 * @param {netric.Account} account
 */
Application.prototype.setAccount = function(account) {
	this.account = account;
}

/**
 * Run the loaded application
 *
 * @param {DOMElement} domCon Container to render applicaiton into
 */
Application.prototype.run = function(domCon) {

	// Set netric applicaiton reference
	netric.setApplication(this);

	// Set the applicaiton main DOM node
	this.appDomNode_ = domCon;

	// Load up the new router
	var router = new Router();

	// Add a login route
	router.addRoute(
		"/login", 
		LoginController, 
		{application:this}, 
		this.appDomNode_
	);

	// Add a login route
	router.addRoute(
		"/logout", 
		LoginController, 
		{application:this, resetSession:true}, 
		this.appDomNode_
	);

	// Create the root route which is also the default
	router.addRoute("/", MainController, {}, this.appDomNode_);

	// Redirect to login if user is not authenticated
	if (!sessionManager.getSessionToken()) {
		location.go("/login");
	}

	// Setup location change listener
	location.setupRouter(router);

	// Queue heartbeat
	this.queueHeartBeat();
}

/**
 * Queue up a heartbeat check
 *
 * @param {int} delay Optional number of ms to delay the check
 */
Application.prototype.queueHeartBeat = function(delay) {

	// Default check every 30 seconds
	var inMs = delay || 30000;
	setTimeout(function() { this.heartBeat(); }.bind(this), inMs);
}

/**
 * Heartbeat function used to make sure the current session is still validated
 *
 * This is where netric issues and session invalidation on the server side are handled.
 */
Application.prototype.heartBeat = function() {

	// If the device is reporting as disconnected then trust it
	if (!this.device.isOnline() || !sessionManager.getSessionToken()) {
		// Check again in 30 seconds
		this.queueHeartBeat();
	} else {

		// Closure reference
		var applicationInstance = this;

		// Device is online so reach out to the server
		var request = new BackendRequest();
		alib.events.listen(request, "load", function(evt) {
			var response = this.getResponse();

			// Use session is no longer OK for some reason
			if (response.result != "OK") {
				// Clear local session token and send user to login controller
				sessionManager.clearSessionToken();
				location.go("/login", {ret:location.getCurrentPath()});
			}

			// Set flag to let the rest of the application know we are online
			server.online = true;

			// Queue next hearbeat
			applicationInstance.queueHeartBeat();

		});

		alib.events.listen(request, "error", function(evt) {
			
			// Unable to contact the server!
			console.log(request);

			// Set flag to let the rest of the application know we are offline
			server.online = false;

			// Queue next hearbeat
			applicationInstance.queueHeartBeat();
		});

		// The session token will be sent in the header by BackendRequest automatically
		request.send("svr/authentication/checkin", "POST");
	}
}

module.exports = Application;
