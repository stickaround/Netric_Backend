/**
* @fileOverview Proxy to handle errors and logging
*
* @author:	Sky Stebnicki, sky.stebnicki@aereus.com; 
* 			Copyright (c) 2014 Aereus Corporation. All rights reserved.
*/
'use strict';

/**
 * Create global namespace for logging
 */
var log = {}

/**
 * Write an error to the log
 *
 * @public
 * @var {string} message
 */
log.error = function(message) {
	// Get the name of the calling function
	var myName = arguments.callee.toString();
	/*
	myName = myName.substr('function '.length);
	myName = myName.substr(0, myName.indexOf('('));
	*/

	console.log(myName + ":" + message);
}

module.exports = log;
