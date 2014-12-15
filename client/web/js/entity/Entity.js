/**
* @fileOverview Base entity may be extended
*
* @author:	Sky Stebnicki, sky.stebnicki@aereus.com; 
* 			Copyright (c) 2014 Aereus Corporation. All rights reserved.
*/
alib.declare("netric.entity.Entity");

alib.require("netric");

/**
 * Make sure entity namespace is initialized
 */
netric.entity = netric.entity || {};

/**
 * Entity represents a netric object
 *
 * @constructor
 * @param {string} objType Name of object type
 */
netric.entity.Entity = function(objType, sObjId) {
}