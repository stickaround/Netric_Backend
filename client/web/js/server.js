/**
* @fileOverview Server settings object
*
* @author:	Sky Stebnicki, sky.stebnicki@aereus.com; 
* 			Copyright (c) 2014 Aereus Corporation. All rights reserved.
*/

alib.declare("netric.server");

alib.require("netric");

/**
 * Create global namespace for server settings
 */
netric.server = netric.server || {};

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
netric.server.host = "";