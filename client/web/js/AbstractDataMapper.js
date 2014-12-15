/**
* @fileOverview Base DataMapper to be used throughout netric for loading server data into objects
*
* @author:	Sky Stebnicki, sky.stebnicki@aereus.com; 
* 			Copyright (c) 2014 Aereus Corporation. All rights reserved.
*/
alib.declare("netric.AbstractDataMapper");

alib.require("netric");

/**
 * Abstract base datamapper used for all datamappers throughout netric
 */
netric.AbstractDataMapper = function() {
	// All datamappers should first try to load locally
	//	If it exists then start an update sync if we are connected and one is not running
	//  If it does not exist, then try to connect
}

/**
 * Get data from a local or remote store
 *
 * Example:
 * <code>
 *	dm.get("entity/email_message/100001");
 *	dm.get("objdefs/email_message");
 * </code>
 *
 * @private
 * @param {string} sourcePath The unique path of the data to get
 */
netric.AbstractDataMapper.prototype.open = function(sourcePath) {
}

/**
 * Save data
 * 
 * Example:
 * <code>
 *	var data = {amount:"100", name: "Text Name"};
 *	dm.save("entity/customer/100", data);
 * </code>
 *
 * @private
 * @param {string} sourcePath The unique path of the data to get
 */
netric.AbstractDataMapper.prototype.save = function(sourcePath, data) {

}

/**
 * Query a list of data
 *
 * @private
 * @param {string} sourcePath The path to the list to query
 * @param {int} offset What page to start on
 * @param {int} limit The maximum number of items to return
 * @param {Object} conditions QueryDSL(?) conditions object
 */
netric.AbstractDataMapper.prototype.query = function(sourcePath, offset, limit, conditions) {

}