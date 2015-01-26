/**
* @fileOverview Proxy to handle errors and logging
*
* @author:	Sky Stebnicki, sky.stebnicki@aereus.com; 
* 			Copyright (c) 2014 Aereus Corporation. All rights reserved.
*/

alib.declare("netric.log");

alib.require("netric");

/**
 * Create global namespace for server settings
 */
netric.log = netric.log || {};

/**
 * Write an error to the log
 *
 * @public
 * @var {string} message
 */
netric.log.error = function(message) {
	// Get the name of the calling function
	var myName = arguments.callee.toString();
	/*
	myName = myName.substr('function '.length);
	myName = myName.substr(0, myName.indexOf('('));
	*/

	console.log(myName + ":" + message);
}