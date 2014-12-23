/**
 * @fileOverview Collection of entities
 *
 * Example:
 * <code>
 * 	var query = new netric.entity.Query("customer");
 * 	query.where('first_name').equals("sky");
 *  query.andWhere('last_name').contains("steb");
 *	query.orderBy("last_name", "desc");
 *	netric.entity.collectionLoader.get(query, function(collection) {
 *		// TODO: do something with the data in collection
 *	});
 *	
 * </code>
 *
 * @author:	Sky Stebnicki, sky.stebnicki@aereus.com; 
 * 			Copyright (c) 2014 Aereus Corporation. All rights reserved.
 */
alib.declare("netric.entity.Collection");

alib.require("netric");
alib.require("netric.entity.Definition");

/**
 * Make sure entity namespace is initialized
 */
netric.entity = netric.entity || {};

/**
 * Entity represents a netric object
 *
 * @constructor
 * @param {netric.entity.Definition} entityDef Required definition of this entity
 * @param {Object} opt_data Optional data to load into this object
 */
netric.entity.Collection = function(entityDef, opt_data) {
}
