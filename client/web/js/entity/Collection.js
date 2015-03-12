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
alib.declare("netric.entity.Collection");

alib.require("netric");
alib.require("netric.entity.Definition");
alib.require("netric.entity.Where");
alib.require("netric.BackendRequest");

/**
 * Make sure entity namespace is initialized
 */
netric.entity = netric.entity || {};

/**
 * Represents a collection of entities
 *
 * @constructor
 * @param {string} objType The name of the object type we are collecting
 */
netric.entity.Collection = function(objType) {

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
     * @type {netric.entity.Definition}
     */
    this.entityDefinition_ = null;

    /**
     * Array of where conditions
     *
     * @type {netric.entity.Where[]}
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
     * @type {netric.entity.Collection.orderByDir}
     */
    this.orderByDir = netric.entity.Collection.orderByDir;

    /**
     * Array of entities in this collection
     *
     * @private
     * @type {netric.entity.Entity[]}
     */
    this.entities_ = new Array();

}

/**
 * Static order by direction
 *
 * @const
 */
netric.entity.Collection.orderByDir = {
    ASC : "ASC",
    DESC : "DESC"
}

/**
 * Load the entities for this collection
 *
 * @param {function} opt_callback Optional callback to be called when finished loading
 */
netric.entity.Collection.prototype.load = function(opt_callback) {

    // First get the entity definition
    if (null === this.entityDefinition_) {

        netric.entity.definitionLoader.get(this.objType_, function(def){
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

    // TODO: first try to load cached

    var request = new netric.BackendRequest();

    var collection = this;
    alib.events.listen(request, "load", function(evt) {
        var resp = this.getResponse();
        collection.totalNum_ = resp.totalNum;
        collection.setEntitiesData(resp.entities);

        // Call the optional callback if set
        if (opt_callback) {
            opt_callback(collection);
        }
    });

    request.send("svr/entity/query", "GET", {obj_type:this.objType_});
}

/**
 * Set the entities for this collection from raw data
 *
 * @param {Array} data
 */
netric.entity.Collection.prototype.setEntitiesData = function(data) {

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
        this.entities_[this.offset_ + parseInt(i)] = new netric.entity.Entity(this.entityDefinition_, data[i]);
    }

    // Triger change event
    alib.events.triggerEvent(this, "change");
}

/**
 * Get total number of entities in this collection
 *
 * @returns {integer}
 */
netric.entity.Collection.prototype.getTotalNum = function() {
    return this.totalNum_;
}

/**
 * Get array of all entities in this collection
 */
netric.entity.Collection.prototype.getEntities = function() {
    return this.entities_;
}

/**
 * Get updates from the backend and refresh the collection
 */
netric.entity.Collection.prototype.refresh = function() {
    // if this.syncCollectionId then call entitySync conctroller to look for changes
    // Otherwise load the query
}

/**
 * Proxy used to add the first where condition to this query
 *
 * @param {string} fieldName The name of the field to query
 * @return {netric.entity.Where}
 */
netric.entity.Collection.prototype.where = function(fieldName) {
    return this.andWhere(fieldName);
}

/**
 * Add a where condition using the logical 'and' operator
 *
 * @param {string} fieldName The name of the field to query
 * @return {netric.entity.Where}
 */
netric.entity.Collection.prototype.andWhere = function(fieldName) {
    var where = new netric.entity.Where(fieldName);
    this.conditions_.push(where);
    return where;
}

/**
 * Add a where condition using the logical 'and' operator
 *
 * @param {string} fieldName The name of the field to query
 * @return {netric.entity.Where}
 */
netric.entity.Collection.prototype.orWhere = function(fieldName) {
    var where = new netric.entity.Where(fieldName);
    where.operator = netric.entity.Where.boolOperator.OR;
    this.conditions_.push(where);
    return where;
}

/**
 * Get the conditions for this entity query
 *
 * @return {Array}
 */
netric.entity.Collection.prototype.getConditions = function() {
    return this.conditions_;
}


/**
 * Add an order by condition
 *
 * @param {string} fieldName The name of the field to sort by
 * @param {netric.entity.Collection.orderByDir} The direction of the sort
 */
netric.entity.Collection.prototype.setOrderBy = function(fieldName, direction) {
   this.orderBy_.push({field:fieldName, direction:direction});
}

/**
 * Get the order for this entity query
 *
 * @return {Array}
 */
netric.entity.Collection.prototype.getOrderBy = function() {
    return this.orderBy_;
}

/**
 * Set the offset for this query
 *
 * @param {int} offset
 */
netric.entity.Collection.prototype.setOffset = function(offset) {
    this.offset_ = offset;
}
/**
 * Get the current offset
 *
 * @return {int}
 */
netric.entity.Collection.prototype.getOffset = function() {
    return this.offset_;
}

/**
 * Set the limit for this query
 *
 * @param {int} limit
 */
netric.entity.Collection.prototype.setLimit = function(limit) {
    this.limit_ = limit;
}
/**
 * Get the current limit
 *
 * @return {int}
 */
netric.entity.Collection.prototype.getLimit = function() {
    return this.limit_;
}