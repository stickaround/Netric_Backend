/**
* @fileOverview Module loader
*
* @author:	Sky Stebnicki, sky.stebnicki@aereus.com; 
* 			Copyright (c) 2014 Aereus Corporation. All rights reserved.
*/
alib.declare("netric.module.loader");

alib.require("netric");
alib.require("netric.module.Module")

/**
 * Make sure module namespace is initialized
 */
netric.module = netric.module || {};

/**
 * Global module loader
 *
 * @param {netric.Application} application Application instance
 */
netric.module.loader = netric.module.loader || {};

/**
 * Loaded applications
 *
 * @private
 * @param {Array}
 */
netric.module.loader.loadedModules_ = new Array();

/**
 * Static function used to load the module
 *
 * @param {string} moduleName The name of the module to load
 * @param {function} cbLoaded Callback function once module is loaded
 */
netric.module.loader.get = function(moduleName, cbLoaded)
{
	// Return (or callback callback) cached module if already loaded
	if (this.loadedModules_[moduleName]) {
		
		if (cbLoaded) {
			cbLoaded(this.loadedModules_[moduleName]);
		}

		return this.loadedModules_[moduleName];
	}

	var request = new netric.BackendRequest();

	if (cbLoaded) {
		alib.events.listen(request, "load", function(evt) {
			var module = netric.module.loader.createModuleFromData(this.getResponse());
			cbLoaded(module);
		});
	} else {
		// Set request to be synchronous if no callback is set	
		request.setAsync(false);
	}

	request.send("svr/module/get", "GET", {name:moduleName});

	// If no callback then construct netric.module.Module from request date (synchronous)
	if (!cbLoaded) {
		return this.createModuleFromData(request.getResponse());
	}
}

/** 
 * Map data to an module object
 *
 * @param {Object} data The data to create an module from
 */
netric.module.loader.createModuleFromData = function(data) {
	
	var module = new netric.module.Module(data);

	// Make sure the name was set to something other than ""
	if (module.name.length) {
		this.loadedModules_[module.name] = module;		
	}

	return module;
}

/** 
 * Preload/cache modules from data
 *
 * Use data to preload or cache modules by name
 *
 * @param {Object[]} modulesData
 */
netric.module.loader.preloadFromData = function(modulesData) {
	for (var i in modulesData) {
		this.createModuleFromData(modulesData[i]);
	}
}