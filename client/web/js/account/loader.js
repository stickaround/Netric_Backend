/**
* @fileOverview Account loader
*
* @author:	Sky Stebnicki, sky.stebnicki@aereus.com; 
* 			Copyright (c) 2014 Aereus Corporation. All rights reserved.
*/
'use strict';

var BackendRequest = require("../BackendRequest");
var Account = require("../account/Account");
var localData = require("../localData");

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
	
	if (typeof cbLoaded == "undefined")
		var cbLoaded = null;

	// Return (or callback callback) cached account if already loaded
	if (this.accountCache_ != null) {
		
		if (cbLoaded) {
			cbLoaded(this.accountCache_);
		}

		return this.accountCache_;
	}

	return this.getFromLocalDb_(cbLoaded);
}

/**
 * Get an account from the local database cache
 *
 * @param {function} cbLoaded Callback function once account is loaded
 */
loader.getFromLocalDb_ = function(cbLoaded) {
	
	if (typeof cbLoaded == "undefined")
		var cbLoaded = null;

	// If no callback was sent and we are forcing async then go to server
	if (!cbLoaded)
		return this.getFromServer_(null);
	
	// First try to get from local store
	localData.dbGetItem("account", function(err, val){
		if (val) {
			// Create/update the account
			var account = loader.createAccountFromData(val);

			// Call finished callback
			cbLoaded(account);

			// Load from the server anyway to get updates
			this.getFromServer_(function(){ });
		} else {
			this.getFromServer_(cbLoaded);
		}
	}.bind(this));
}

/**
 * Get an account from the server
 *
 * @param {function} cbLoaded Callback function once account is loaded
 */
loader.getFromServer_ = function(cbLoaded) {
	var request = new BackendRequest();

	if (cbLoaded) {
		alib.events.listen(request, "load", function(evt) {
			// Create/update the account
			var account = loader.createAccountFromData(this.getResponse());
			
			// Save in local db
			localData.dbSetItem("account", this.getResponse(), function(err, val){});

			// Call finished callback
			cbLoaded(account);
		});
	} else {
		// Set request to be synchronous if no callback is set	
		request.setAsync(false);
	}

	request.send("svr/account/get");

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

	if (!this.accountCache_) {
		// Construct account and initialize with data	
		var account = new Account(data);
		
		// Cache it for future requests
		this.accountCache_ = account;
	} else {
		// Update
		this.accountCache_.loadData(data);
	}

	

	return this.accountCache_;
}

module.exports = loader;