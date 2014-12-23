/**
 * @fileOverview Entity query
 *
 * Example:
 * <code>
 * 	var query = new netric.entity.Query("customer");
 * 	query.where('first_name').equals("sky");
 *  query.andWhere('last_name').contains("steb");
 *	query.orderBy("last_name", netric.entity.Query.orderByDir.desc);
 * </code>
 *
 * @author:	Sky Stebnicki, sky.stebnicki@aereus.com; 
 * 			Copyright (c) 2014 Aereus Corporation. All rights reserved.
 */
alib.declare("netric.entity.Query");

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
 * @param {string} objType Name of the object type we are querying
 */
netric.entity.Query = function(objType) {
	/**
	 * Object type for this list
	 *
	 * @type {string}
	 * @private
	 */
	this.objType_ = obj_type;

	/**
	 * Array of condition objects {blogic, fieldName, operator, condValue}
	 *
	 * @type {array}
	 * @private
	 */
	this.conditions_ = new Array();


	/**
	 * Array of sort order objects
	 *
	 * @type {array}
	 * @private
	 */
	this.orderBy_ = new Array();

	/**
	 * The current offset of the total number of items
	 *
	 * @type {number}
	 * @private
	 */
	this.offset_ = 0;

	/**
	 * Number of items to pull each query
	 *
	 * @type {number}
	 * @private
	 */
	this.limit_ = 100;

	/**
	 * Total number of objects in this query set
	 *
	 * @type {number}
	 * @private
	 */
	this.totalNum = 0;

	/**
	 * Copy static order by direction to this so we can access through this.orderByDir
	 *
	 * @public
	 * @type {netric.entity.Query.orderByDir}
	 */
	this.orderByDir = netric.entity.Query.orderByDir;
}

/**
 * Static order by direction
 * 
 * @const
 */
netric.entity.Query.orderByDir = {
	asc : "ASC",
	desc : "DESC"
}

/**
 * Proxy used to add the first where condition to this query
 *
 * @param {string} fieldName The name of the field to query
 * @return {netric.entity.query.Where}
 */
netric.entity.Query.prototype.where = function(fieldName) {
	return this.andWhere(fieldName);
}

/**
 * Add a where condition using the logical 'and' operator
 * 
 * @param {string} fieldName The name of the field to query
 * @return {netric.entity.query.Where}
 */
netric.entity.Query.prototype.andWhere = function(fieldName) {
	// TODO: return netri.entity.query.Where
}

/**
 * Add a where condition using the logical 'and' operator
 * 
 * @param {string} fieldName The name of the field to query
 * @return {netric.entity.query.Where}
 */
netric.entity.Query.prototype.orWhere = function(fieldName) {
	// TODO: return netri.entity.query.Where
}

/**
 * Add an order by condition
 * 
 * @param {string} fieldName The name of the field to sort by
 * @param {netric.entity.Query.orderByDir} The direction of the sort
 */
netric.entity.Query.prototype.orderBy = function(fieldName, direction) {
	// TODO: add order by condition
}

/** 
 * Get the conditions for this entity query
 * 
 * @return {Array}
 */
netric.entity.Query.prototype.getConditions = function() {
	return this.conditions_;
}

/** 
 * Get the order for this entity query
 * 
 * @return {Array}
 */
netric.entity.Query.prototype.getOrderBy = function() {
	return this.orderBy_;
}

/**
 * Set the offset for this query
 * 
 * @param {int} offset
 */
netric.entity.Query.prototype.setOffset = function(offset) {
	this.offset_ = offset;
}
/**
 * Get the current offset
 * 
 * @return {int}
 */
netric.entity.Query.prototype.getOffset = function() {
	return this.offset_;
}

/**
 * Set the limit for this query
 * 
 * @param {int} limit
 */
netric.entity.Query.prototype.setLimit = function(limit) {
	this.limit_ = limit;
}
/**
 * Get the current limit
 * 
 * @return {int}
 */
netric.entity.Query.prototype.getLimit = function() {
	return this.limit_;
}