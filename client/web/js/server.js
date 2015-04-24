/**
* @fileOverview Server settings object
*
* @author:	Sky Stebnicki, sky.stebnicki@aereus.com; 
* 			Copyright (c) 2014 Aereus Corporation. All rights reserved.
*/
'use strict';

/**
 * Create global namespace for server settings
 */
var server = {};

/**
 * Server host
 * 
 * If = "" then assume server is hosted from the same origin
 * as the client, as in from the web server.
 *
 * If this is set, then make sure the auth token has been
 * negotiated and set.
 *
 * @public
 * @var {string}
 */
server.host = "";

/**
 * Connection status used to indicate if we are able to query the server
 *
 * Example"
 * <code>
 *	if (netric.server.online)
 *		server.getData();
 *	else
 * 		localStore.getData();
 * </code>
 *
 * @public
 * @var {bool}
 */
server.online = true;

module.exports = server;
