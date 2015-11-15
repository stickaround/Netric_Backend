/**
* @fileOverview Entity loader / identity mapper
*
* @author:	Sky Stebnicki, sky.stebnicki@aereus.com; 
* 			Copyright (c) 2014 Aereus Corporation. All rights reserved.
*/
'use strict';

var definitionLoader = require("./definitionLoader");
var BackendRequest = require("../BackendRequest");
var Entity = require("./Entity");
var events = require('../util/events');

/**
 * Global entity loader namespace
 */
var loader = {};

/**
 * Array of already loaded entities
 *
 * @private
 * @var {Array}
 */
loader.entities_ = new Object();

/**
 * Static function used to load the entity
 *
 * @param {string} objType The object type to load
 * @param {string} entId The unique entity to load
 * @param {function} cbLoaded Callback function once entity is loaded
 * @param {bool} force If true then force the entity to reload even if cached
 */
loader.get = function(objType, entId, cbLoaded, force) {
	// Return (or callback callback) cached entity if already loaded
	var ent = this.getCached(objType, entId);
	if (ent && !force) {

		if (cbLoaded) {
			cbLoaded(ent);
		}

		return ent;
	}

    /*
     * Create entity to fill and put it in cache.
     * This allows us to immediate return an entity object
     * to be worked with while the entity is loading.
     */
    var entityToFill = this.factory(objType);
    entityToFill.setValue("id", entId);
    // Flag the entity as pending load
    entityToFill.isLoading = true;
    this.cacheEntity(entityToFill);

	/*
	 * Load the entity data
	 */
	var request = new BackendRequest();

	if (cbLoaded) {
		events.listen(request, "load", function(evt) {
			var entity = loader.createFromData(this.getResponse());
			cbLoaded(entity);
		});
	} else {
		// Set request to be synchronous if no callback is set	
		request.setAsync(false);
	}

	// Create request data
	var requestData = {
		obj_type:objType, 
		id:entId
	}

	// Add definition if it is not loaded already.
	// This will cause the backend to include a .definition property in the resp
	if (definitionLoader.getCached(objType) == null) {
		requestData.loadDef = 1;
	}

	request.send("svr/entity/get", "GET", requestData);

	// If no callback then construct Entity from request date (synchronous)
	if (!cbLoaded) {
		return this.createFromData(request.getResponse());
	} else {
		// Return an entity that has not yet been populated
        // This is basically a promise but the calling function
        // will need to listen for a change event to refresh
        // its data.
        return entityToFill;
	}
}

/**
 * Static function used to create a new object entity
 *
 * This function may need to get the definition from the server. If it is called
 * with no opt_cbCreated it will do a non-async request which could hang the entire UI
 * until the request returns so be careful in this instance because users don't much
 * like that. Try to include the callback param as much as possible. 
 *
 * @param {string} objType The object type to load
 * @param {function} opt_cbCreated Optional callback function once entity is initialized
 */
loader.factory = function(objType, opt_cbCreated) {

	var entDef = definitionLoader.getCached(objType);

	if (opt_cbCreated) {
		definitionLoader.get(objType, function(def) {
			var ent = new Entity(def);
			opt_cbCreated(ent);
		});
	} else {
		// Force a syncronous request with no second param (callback)
		var def = definitionLoader.get(objType);
		return new Entity(def);
	}
}

/** 
 * Map data to an entity object
 *
 * @param {Object} data The data to create an entity from
 */
loader.createFromData = function(data) {

	if (typeof data === 'undefined') {
		throw "data is a required param to create an object";
	}

	// Get cached object definition
	var entDef = definitionLoader.getCached(data.obj_type);
	
	// If cached definition is not found then the data object should include a .definition prop
	if (entDef == null && data.definition) {
		entDef = definitionLoader.createFromData(data.definition);
	}

	// If we don't have a definition to work with we should throw an error
	if (entDef == null) {
		throw "Could not load a definition for " + data.obj_type;
	}

	// Check to see if we have previously already loaded this object
	var ent = this.getCached(entDef.objType, data.id);

	if (ent != null) {
        // Clean any loading flags that were set
        ent.isLoading = false;
		ent.loadData(data);
	} else {
		ent = new Entity(entDef, data);

		// Make sure the name was set to something other than "" and place it in cache
		if (ent.id && ent.objType) {
			this.cacheEntity(ent);	
		}
	}

	
	return ent;
}

/**
 * Put an entity in the local cache for future quick loading
 *
 * @param {Entity} ent The entity to store
 */
loader.cacheEntity = function(ent) {

	if (!this.entities_[ent.objType]) {
		this.entities_[ent.objType] = new Object();	
	}

	this.entities_[ent.objType][ent.id] = ent;

}

/** 
 * Get an object entity from cache
 *
 * @param {string} objType The object type to load
 * @param {string} entId The unique entity to load
 * @return {Entity} or null if not cached
 */
loader.getCached = function(objType, entId) {

	// Check to see if the entity is already loaded and return it
	if (this.entities_[objType]) {
		if (this.entities_[objType][entId]) {
			return this.entities_[objType][entId];
		}
	}

	return null;
}

module.exports = loader;
