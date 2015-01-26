/**
* @fileOverview Load instance of netric application
*
* @author:	Sky Stebnicki, sky.stebnicki@aereus.com; 
* 			Copyright (c) 2011 Aereus Corporation. All rights reserved.
*/
netric.declare("netric.Application");

netric.require("netric");
netric.require("netric.account.loader");
netric.require("netric.location");
netric.require("netric.location.Router");
netric.require("netric.Device");
netric.require("netric.controller.MainController");

/**
 * Application instance
 *
 * @param {netric.Account} account The current account netric is running under
 */
netric.Application = function(account) {
	/**
	 * Represents the actual netric account
	 *
	 * @public
	 * @var {netric.Application.Account}
	 */
	this.account = account;

	/**
	 * Device information class
	 *
	 * @public
	 * @var {netric.Device}
	 */
	this.device = new netric.Device();
};

/**
 * Static function used to load the application
 *
 * @param {function} cbFunction Callback function once app is loaded
 */
netric.Application.load = function(cbFunction) {

	/*
	 * The first thing we need to do is load the current account so
	 * we can inject it as a dependency to the application instance.
	 */
	netric.account.loader.get(function(acct){

		// Create appliation instance for loaded account
		var app = new netric.Application(acct);

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
netric.Application.prototype.getAccount = function() {
	return this.account;
}

/**
 * Run the loaded application
 *
 * @param {DOMElement} domCon Container to render applicaiton into
 */
netric.Application.prototype.run = function(domCon) {

	// Load up the new router
	var router = new netric.location.Router();

	// Create the root route which is also the default
	router.addRoute("/", netric.controller.MainController, {}, domCon);

	// Setup location change listener
	netric.location.setupRouter(router);

	// Create root application view
	//var appView = new netric.ui.ApplicationView(this);

	/*
	 * Setup the router so that any change to the URL will route through
	 * the redner action for the front contoller which will propogate the new
	 * url path down through all children contollers as well.
	 */
	//var router = new netric.mvc.Router();
	//router.onchange = function(path) {
	//	appView.load(path);
	//}

	// Render application
	//appView.render(domCon);
}