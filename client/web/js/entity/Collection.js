/**
 * @fileOverview Collection of entities
 *
 * Example:
 *
 * 	var collection = new entity.Collection("customer");
 *	collection.where("first_name").isEqaulTo("sky");
 *	collection.setLimit(100);
 *	alib.events.listen(collection, "change", function(collection) {
 *		// Load entities into the view
 *		this.loadEntities(collection);
 *	}.bind(this));
 *
 *	// This will trigger a 'change' event if any entities are loaded
 *	collection.load();
 *
 *	// To extend the limit, just go
 *	var lastLoadedOffset = collection.getLastLoadedOffset();
 *	if (lastLoadedOffset < collection.getTotalNum())
 *	{
 *	  var nextLimit = lastLoadedOffset + 100;
 *	
 *	  // If we are beyond the boundaries of the query, just point to the end
 *	  if (nextLimit >= collection.getTotalNum())
 *		nextLimit = collection.getTotalNum() - 1;
 *	
 *	  collection.setLimit(nextLimit);
 *	  collection.load();
 *	}
 *
 *	// Refresh will trigger change if needed
 *	collection.refresh(function() {  
 *	 // Finished refresh
 *	});
 *
 *
 *
 * @author:	Sky Stebnicki, sky.stebnicki@aereus.com;
 * 			Copyright (c) 2014-2015 Aereus Corporation. All rights reserved.
 */
'use strict';

var definitionLoader = require("./definitionLoader");
var BackendRequest = require("../BackendRequest");
var Entity = require("./Entity");
var Where = require("./Where");

/**
 * Represents a collection of entities
 *
 * @constructor
 * @param {string} objType The name of the object type we are collecting
 */
var Collection = function(objType) {

    /**
     * Object type for this list
     *
     * @type {string}
     * @private
     */
    this.objType_ = objType;

    /**
     * Entity definition
     *
     * @type {EntityDefinition}
     */
    this.entityDefinition_ = null;

    /**
     * Array of where conditions
     *
     * @type {Where[]}
     * @private
     */
    this.conditions_ = new Array();


    /**
     * Array of sort order objects
     *
     * @type {Array}
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
    this.limit_ = 25;

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
     * @type {Collection.orderByDir}
     */
    this.orderByDir = Collection.orderByDir;

    /**
     * Array of entities in this collection
     *
     * @private
     * @type {Entity[]}
     */
    this.entities_ = new Array();

}

/**
 * Static order by direction
 *
 * @const
 */
Collection.orderByDir = {
    ASC : "ASC",
    DESC : "DESC"
}

/**
 * Load the entities for this collection
 *
 * @param {function} opt_callback Optional callback to be called when finished loading
 */
Collection.prototype.load = function(opt_callback) {

    // First get the entity definition
    if (null === this.entityDefinition_) {

        definitionLoader.get(this.objType_, function(def){
            this.entityDefinition_ = def;

            if (opt_callback) {
                this.load(opt_callback);
            } else {
                this.load();
            }
        }.bind(this));

        // Leave until we get the definition loaded
        return;
    }

    // Triger loading event
    alib.events.triggerEvent(this, "loading");

    // TODO: first try to load cached

    // Setup the request
    var requestParams = {
        obj_type:this.objType_,
        limit:this.limit_
    };

    var request = new BackendRequest();

    var collection = this;
    alib.events.listen(request, "load", function(evt) {
        var resp = this.getResponse();
        collection.totalNum_ = resp.total_num;
        collection.setEntitiesData(resp.entities);

        // Call the optional callback if set
        if (opt_callback) {
            opt_callback(collection);
        }

        // Triger loaded event
        alib.events.triggerEvent(collection, "loaded");
    });

    /*
     * Add each condition as a 'where' that is csv encoded
     * in the format blogic,field_name,operator,value
     */

    var whereConditions = this.getConditions();

    // If there are where conditions then initialize the param in the request object
    if (whereConditions.length > 0) {
        requestParams.where = [];
    }

    for (var i in whereConditions) {
        requestParams.where.push(
            whereConditions[i].bLogic + "," +
            whereConditions[i].fieldName + "," +
            whereConditions[i].operator + "," +
            '"' + whereConditions[i].value + '"' // Escape for csv quotes
        );
    }

    // Send request to the server (listeners attached above will handle onload or error)
    request.send("svr/entity/query", "GET", requestParams);
}

/**
 * Set the entities for this collection from raw data
 *
 * @param {Array} data
 */
Collection.prototype.setEntitiesData = function(data) {

    if (this.offset_ == 0) {
        // Cleanup
        this.entities_ = new Array();
    } else {
        // Remove everything from this.offset_ on
        for (var i = (this.entities_.length - 1); i > this.offset_; i--) {
            this.entities_.pop();
        }
    }

    // Initialize entities
    for (var i in data) {
        this.entities_[this.offset_ + parseInt(i)] = new Entity(this.entityDefinition_, data[i]);
    }

    // Triger change event
    alib.events.triggerEvent(this, "change");
}

/**
 * Get total number of entities in this collection
 *
 * @returns {integer}
 */
Collection.prototype.getTotalNum = function() {
    return this.totalNum_;
}

/**
 * Get array of all entities in this collection
 */
Collection.prototype.getEntities = function() {
    return this.entities_;
}

/**
 * Get updates from the backend and refresh the collection
 */
Collection.prototype.refresh = function() {
    // Reload which will trigger a load event
    this.load();
}

/**
 * Add a Where object directly
 *
 * @param {Where} where The where objet to add to conditions
 */
Collection.prototype.addWhere = function(where) {
    this.conditions_.push(where);
}

/**
 * Proxy used to add the first where condition to this query
 *
 * @param {string} fieldName The name of the field to query
 * @return {Where}
 */
Collection.prototype.where = function(fieldName) {
    return this.andWhere(fieldName);
}

/**
 * Add a where condition using the logical 'and' operator
 *
 * @param {string} fieldName The name of the field to query
 * @return {Where}
 */
Collection.prototype.andWhere = function(fieldName) {
    var where = new Where(fieldName);
    this.conditions_.push(where);
    return where;
}

/**
 * Add a where condition using the logical 'and' operator
 *
 * @param {string} fieldName The name of the field to query
 * @return {Where}
 */
Collection.prototype.orWhere = function(fieldName) {
    var where = new Where(fieldName);
    where.operator = Where.boolOperators.OR;
    this.conditions_.push(where);
    return where;
}

/**
 * Get the conditions for this entity query
 *
 * @return {Array}
 */
Collection.prototype.getConditions = function() {
    return this.conditions_;
}

/**
 * Clear all where conditions
 */
Collection.prototype.clearConditions = function() {
    this.conditions_ = [];
}

/**
 * Add an order by condition
 *
 * @param {string} fieldName The name of the field to sort by
 * @param {Collection.orderByDir} The direction of the sort
 */
Collection.prototype.setOrderBy = function(fieldName, direction) {
   this.orderBy_.push({field:fieldName, direction:direction});
}

/**
 * Get the order for this entity query
 *
 * @return {Array}
 */
Collection.prototype.getOrderBy = function() {
    return this.orderBy_;
}

/**
 * Clear the order for this entity query
 */
Collection.prototype.clearOrderBy = function() {
    this.orderBy_ = [];
}

/**
 * Set the offset for this query
 *
 * @param {int} offset
 */
Collection.prototype.setOffset = function(offset) {
    this.offset_ = offset;
}
/**
 * Get the current offset
 *
 * @return {int}
 */
Collection.prototype.getOffset = function() {
    return this.offset_;
}

/**
 * Set the limit for this query
 *
 * @param {int} limit
 */
Collection.prototype.setLimit = function(limit) {
    this.limit_ = limit;
}

/**
 * Get the current limit
 *
 * @return {int}
 */
Collection.prototype.getLimit = function() {
    return this.limit_;
}

module.exports = Collection;
