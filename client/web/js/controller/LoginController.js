/**
 * @fileoverview This is a test controller used primarily for unit tests
 */
'use strict';

var React = require('react');
var netric = require("../base");
var AbstractController = require("./AbstractController");
var UiLogin = require("../ui/Login.jsx");
var BackendRequest = require("../BackendRequest");
var sessionManager = require("../sessionManager");
var accountLoader = require("../account/loader");
var server = require("../server");
var localData = require("../localData");

/**
 * Test controller
 */
var LoginController = function() { /* Should have details */ }

/**
 * Extend base controller class
 */
netric.inherits(LoginController, AbstractController);

/**
 * Handle to root ReactElement where the UI is rendered
 *
 * @private
 * @type {ReactElement}
 */
LoginController.prototype.rootReactNode_ = null;

/**
 * Function called when controller is first loaded
 */
LoginController.prototype.onLoad = function(opt_callback) { 
	if (opt_callback) {
		opt_callback();
	}
}

/**
 * Render the contoller into the dom
 */
LoginController.prototype.render = function() {

	if (this.props.resetSession) {

		// Get rid of the session
		sessionManager.clearSessionToken();

		// Clear the instanceUri
		localData.setSetting("server.host", null);
		server.host = null;

		// TODO: Should we just clear all settings?
	}

	// Set outer application container
	var domCon = this.domNode_;

	// Setup application data
	var data = {
		onLogin: function(username, password) {
			this.login(username, password)
		}.bind(this),
		onSetAccount: function(instanceUri) {
			this.setAccountUri(instanceUri);
		}.bind(this)
	}

	// Render application component
	this.rootReactNode_= React.render(
		React.createElement(UiLogin, data),
		domCon
	);
}

/**
 * Select which account we are logging into
 *
 * @param {string} instanceUri The server URI to connect to for account instance
 */
LoginController.prototype.setAccountUri = function(instanceUri) {
	server.host = instanceUri;
	localData.setSetting("server.host", instanceUri);
}

/**
 * Perform login with supplied credentials
 *
 * @param {string} username The unique name of the user logging in
 * @param {string} password The clear-text password of the user logging in
 */
LoginController.prototype.login = function(username, password) {

	this.rootReactNode_.setProps({processing: true});

	if (!server.host) {

		// Call the universal login with the email address to get available accounts
		this.getLoginAccounts(username, password);

		// This will be called again by the UI once the user selects an account
		return;
	}

	// Setup callback reference
	var loginController = this;
	var credentials = {"username": username, "password": password};

	var request = new BackendRequest();
	alib.events.listen(request, "load", function(evt) {
		var response = this.getResponse();
		var viewProps = {processing: false };
		switch (response.result) {
			case "SUCCESS":
				// If the server returns success it will also send a session token
				loginController.accessAccepted(response.session_token, username);
				break;
			case "FAIL":
			default:
				viewProps.errorText = (response.reason)
					? response.reason : "Invalid username and/or password";
				break;
		}

		// Update UI to display any errors
		loginController.rootReactNode_.setProps(viewProps);
	});

	alib.events.listen(request, "error", function(evt) {
		// TODO: Unable to contact the server. Handle gracefully.
		loginController.rootReactNode_.setProps({processing: false});
		var response = this.getResponse();
		console.error(response);
		// TODO: we should log this
	});

	request.send("svr/authentication/authenticate", "POST", credentials);
}

/**
 * Perform login with supplied credentials
 *
 * @param {string} username The unique name of the user logging in
 * @param {string} password The clear-text password of the user logging in
 */
LoginController.prototype.getLoginAccounts = function(username, password) {

	if (!server.universalLoginUri) {
		throw "A critical param server.universalLoginUri was not set";
	}

	// Setup callback reference
	var loginController = this;

	var request = new BackendRequest();
	alib.events.listen(request, "load", function(evt) {
		var response = this.getResponse();

		// If there is only one account then just call login again
		if (1 == response.length) {
			loginController.setAccountUri(response[0].instanceUri);
			loginController.login(username, password);
		} else {
			// Update the view with accounts to select from
			var viewProps = {
				processing: false, 
				accounts: response
			};

			if (response.length == 0) {
				viewProps.errorText =  "Invalid username and/or password";
				viewProps.accounts = null;
			}

			// Update UI to display any errors
			loginController.rootReactNode_.setProps(viewProps);
		}	
	});

	alib.events.listen(request, "error", function(evt) {
		// TODO: Unable to contact the server. Handle gracefully.
		loginController.rootReactNode_.setProps({processing: false});
		var response = this.getResponse();
		console.error(response);
		// TODO: we should definitely log this
	});

	// Make a request to the universal login endpoint to get accounts
	var url = server.universalLoginUri;
	url += "/svr/authentication/get-accounts";
	log.info("Sending:" + url);
	request.send(url, "POST", { "email": username });
}

/**
 * Login succeeded proceed to main application or wherever the user previously was
 *
 * @param {string} sessionToken Session token returned from the server
 * @param {string} userName The name/email of the user who just logged in
 */
LoginController.prototype.accessAccepted = function(sessionToken, userName) {
	
	// If this is a new user or account then we should clear the old cached data
	if (server.host != localData.setSetting("lastAccountUri") 
		|| userName != localData.setSetting("lastUsername")) {

		// Clear the localdb cached data since it will be for
		// a different user or account.
		localData.dbClear();
	}

	// Set the server session token
	sessionManager.setSessionToken(sessionToken);

	// Save last logged in data
	localData.setSetting("lastUsername", userName);
	localData.setSetting("lastAccountUri", server.host);

	/*
	 * Now that the user is authenticated we need to load/reload
	 * the account so any changes to the application definition
	 * can be applied.
	 */
	this.props.application.loadAccount(function(acct){

		// If the application redirected us here there should be a return param
		if (this.props.ret) {
			netric.location.go(this.props.ret);
		} else {
			netric.location.go("/"); // Or just start over			
		}
	}.bind(this));

}

module.exports = LoginController;
