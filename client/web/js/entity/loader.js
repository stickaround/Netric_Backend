/**
* @fileOverview Entity loader / identity mapper
*
* @author:	Sky Stebnicki, sky.stebnicki@aereus.com; 
* 			Copyright (c) 2014 Aereus Corporation. All rights reserved.
*/
alib.declare("netric.entity.loader");

alib.require("netric");

/**
 * Make sure entity namespace is initialized
 */
netric.entity = netric.entity || {};

/**
 * Global entity loader namespace
 */
netric.entity.loader = netric.entity.loader || {};

/**
 * Array of already loaded entities
 *
 * @private
 * @var {Array}
 */
netric.entity.loader.entities_ = new Array();

/**
 * Static function used to load the module
 *
 * @param {string} objType The object type to load
 * @param {string} entId The unique entity to load
 * @param {function} cbLoaded Callback function once entity is loaded
 */
netric.entity.loader.get = function(objType, entId, cbLoaded) {
	// Return (or callback callback) cached module if already loaded
	var ent = this.getCached(objType, entId);
	if (ent) {

		if (cbLoaded) {
			cbLoaded(ent);
		}

		return ent;
	}

	/*
	 * Load the entity data
	 */
	var request = new netric.BackendRequest();

	if (cbLoaded) {
		alib.events.listen(request, "load", function(evt) {
			var module = netric.module.loader.createFromData(this.getResponse());
			cbLoaded(module);
		});
	} else {
		// Set request to be synchronous if no callback is set	
		request.setAsync(false);
	}

	request.send("svr/Entity/get", "GET", {obj_type:objType, id:entId});

	// If no callback then construct netric.module.Module from request date (synchronous)
	if (!cbLoaded) {
		return this.createFromData(request.getResponse());
	}
}

/**
 * Static function used to create a new object entity
 *
 * @param {string} objType The object type to load
 * @param {function} cbCreated Callback function once entity is initialized
 */
netric.entity.loader.create = function(objType, cbCreated) {
}

/** 
 * Map data to an module object
 *
 * @param {Object} data The data to create an module from
 */
netric.entity.loader.createFromData = function(data) {
	
	var module = new netric.module.Module(data);

	// Make sure the name was set to something other than ""
	if (module.name.length) {
		this.loadedModules_[module.name] = module;		
	}

	return module;
}

/** 
 * Get an object entity from cache
 *
 * @param {string} objType The object type to load
 * @param {string} entId The unique entity to load
 * @return {netric.entity.Entity} or null if not cached
 */
netric.entity.loader.getCached = function(objType, entId) {
}