/**
 * @fileOverview Object represents the netric account user
 *
 * @author:	Sky Stebnicki, sky.stebnicki@aereus.com; 
 * 			Copyright (c) 2014 Aereus Corporation. All rights reserved.
 */
alib.declare("netric.User");
alib.require("netric");

/**
 * User instance
 *
 * @param {Object} opt_data Optional data used to initialize the user
 */
netric.User = function(opt_data)
{
	// Initialize empty object if opt_data was not set
	var initData = opt_data || new Object();

	/**
	 * Unique id for this user
	 * 
	 * @public
	 * @type {string}
	 */
	this.id = initData.id || "";

	/**
	 * Unique username for this user
	 * 
	 * @public
	 * @type {string}
	 */
	this.name = initData.name || "";

	/**
	 * Full name is usually combiation of first and last name
	 * 
	 * @public
	 * @type {string}
	 */
	this.fullName = initData.fullName || "";
}