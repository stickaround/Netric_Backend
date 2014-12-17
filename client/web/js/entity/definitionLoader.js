/**
* @fileOverview Definition loader
*
* @author:	Sky Stebnicki, sky.stebnicki@aereus.com; 
* 			Copyright (c) 2014 Aereus Corporation. All rights reserved.
*/
alib.declare("netric.entity.definitionLoader");

alib.require("netric");

alib.require("netric.entity.Definition");
alib.require("netric.BackendRequest");

/**
 * Make sure entity namespace is initialized
 */
netric.entity = netric.entity || {};

/**
 * Entity definition loader
 *
 * @param {netric.Application} application Application instance
 */
netric.entity.definitionLoader = netric.entity.definitionLoader || {};

/**
 * Keep a reference to loaded definitions to reduce requests
 *
 * @private
 * @param {Array}
 */
netric.entity.definitionLoader.definitions_ = new Array();

/**
 * Static function used to load an entity definition
 *
 * If no callback is set then this function will try to return the definition
 * from cache. If it has not yet been loaded then it will force a non-async
 * request which will HANG THE UI so it should only be used as a last resort.
 *
 * @param {string} objType The object type we are loading a definition for
 * @param {function} cbLoaded Callback function once definition is loaded
 * @return {netric.entity.Definition|void} If no callback is provded then force a return
 */
netric.entity.definitionLoader.get = function(objType, cbLoaded) {
	
	// Return (or callback callback) cached definition if already loaded
	if (this.definitions_[objType] != null) {
		
		if (cbLoaded) {
			cbLoaded(this.definitions_[objType]);
		}

		return this.definitions_[objType];
	}

	var request = new netric.BackendRequest();

	if (cbLoaded) {
		alib.events.listen(request, "load", function(evt) {
			var def = netric.entity.definitionLoader.createFromData(this.getResponse());
			cbLoaded(def);
		});
	} else {
		// Set request to be synchronous if no callback is set	
		request.setAsync(false);
	}

	request.send("svr/entity/getDefinition", "GET", {obj_type:objType});

	// If no callback then construct netric.entity.Definition from request date (synchronous)
	if (!cbLoaded) {
		return this.createFromData(request.getResponse());
	}
}

/**
 * Map data to an entity definition object
 *
 * @param {Object} data The data to create the definition from
 */
netric.entity.definitionLoader.createFromData = function(data) {

	// Construct definition and initialize with data	
	var def = new netric.entity.Definition(data);
	
	// Cache it for future requests
	this.definitions_[def.objType] = def;

	return this.definitions_[def.objType];
}

/**
 * Get a pre-loaded / cached object definition
 *
 * @param {string} objType The uniqy name of the object entity type
 * @return {netric.entity.Definition} Entity defintion on success, null if not cached
 */
netric.entity.definitionLoader.getCached = function(objType) {
	if (this.definitions_[objType]) {
		return this.definitions_[objType];
	}

	return null;
}