/**
* @fileOverview Base namespace for netric
*
* @author:	Sky Stebnicki, sky.stebnicki@aereus.com; 
* 			Copyright (c) 2014 Aereus Corporation. All rights reserved.
*/

alib.declare("netric");

/**
 * @define {boolean} Overridden to true by the compiler when --closure_pass
 *     or --mark_as_compiled is specified.
 */
var COMPILED = false;

/**
 * The root namespace for all netric code
 */
var netric = netric || {};

/**
 * Set version
 *
 * @public
 * @type {string}
 */
netric.version = "2.0.1";

/**
 * Connection status used to indicate if we are able to query the server
 *
 * Example"
 * <code>
 *	if (netric.online)
 *		server.getData();
 *	else
 * 		localStore.getData();
 * </code>
 *
 * @public
 * @var {bool}
 */
netric.online = false;

/**
 * Private reference to initialized applicaiton
 *
 * This will be set in netric.Application.load and should be used
 * with caution making sure all supporting code is called after the
 * main applicaiton has been initialized.
 *
 * @private
 * @var {netric.Application}
 */
 netric.application_ = null;

 /**
  * Get account base uri for building links
  * 
  * We need to do this because accounts are represented with
  * third level domains, like aereus.netric.com, where 'aereus'
  * is the name of the account.
  * 
  * @public
  * @return {string} URI
  */
netric.getBaseUri = function()
{
	var uri = window.location.protocol+'//'+window.location.hostname+(window.location.port 
		? ':' + window.location.port
		: '');
	return uri;
}

/**
 * Get initailized application
 *
 * @throws {Exception} If application has not yet been loaded
 * @return {netric.Application|bool}
 */
netric.getApplication = function() {
	if (this.application_ === null) {
		throw new Error("An instance of netric.Application has not yet been loaded.");
	}

	return this.application_;
}