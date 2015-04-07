/**
* @fileOverview Account loader
*
* @author:	Sky Stebnicki, sky.stebnicki@aereus.com; 
* 			Copyright (c) 2014 Aereus Corporation. All rights reserved.
*/
'use strict';

var BackendRequest = require("../BackendRequest");
var Account = require("../account/Account");

/**
 * Global module loader
 *
 * @param {netric.Application} application Application instance
 */
var loader = {};

/**
 * Keep a reference to last loaded application to reduce requests
 *
 * @private
 * @param {Array}
 */
loader.accountCache_ = null;

/**
 * Static function used to load the module
 *
 * If no callback is set then this function will try to return the account
 * from cache. If it has not yet been loaded then it will force a non-async
 * request which will HANG THE UI so it should only be used as a last resort.
 *
 * @param {function} cbLoaded Callback function once account is loaded
 * @return {netric.account.Account|void} If no callback is provded then force a return
 */
loader.get = function(cbLoaded) {
	
	// Return (or callback callback) cached account if already loaded
	if (this.accountCache_ != null) {
		
		if (cbLoaded) {
			cbLoaded(this.accountCache_);
		}

		return this.accountCache_;
	}

	var request = new BackendRequest();

	if (cbLoaded) {
		alib.events.listen(request, "load", function(evt) {
			var account = loader.createAccountFromData(this.getResponse());
			cbLoaded(account);
		});
	} else {
		// Set request to be synchronous if no callback is set	
		request.setAsync(false);
	}

	request.send("/svr/account/get");

	// If no callback then construct netric.account.Account from request date (synchronous)
	if (!cbLoaded) {
		return this.createAccountFromData(request.getResponse());
	}
}

/**
 * Map data to an account object
 *
 * @param {Object} data The data to create an account from
 */
loader.createAccountFromData = function(data) {

	// Construct account and initialize with data	
	var account = new Account(data);
	
	// Cache it for future requests
	this.accountCache_ = account;

	return this.accountCache_;
}

module.exports = loader;