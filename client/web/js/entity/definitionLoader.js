/**
 * @fileOverview Definition loader
 *
 * @author:    Sky Stebnicki, sky.stebnicki@aereus.com;
 *            Copyright (c) 2014 Aereus Corporation. All rights reserved.
 */
'use strict';

var BackendRequest = require("../BackendRequest");
var Definition = require("./Definition");
var log = require("../log");

/**
 * Entity definition loader
 */
var definitionLoader = {};

/**
 * Keep a reference to loaded definitions to reduce requests
 *
 * @private
 * @param {Array}
 */
definitionLoader.definitions_ = new Array();

/**
 * Keep the reference of the request per objType
 *
 * Every objType will only have one BackendRequest instance.
 * With this, we can make sure that we do not have multiple requests for getting
 *  the object's entity definition
 *
 * @private
 * @param {Object}
 */
definitionLoader.requests_ = {};

/**
 * Static function used to load an entity definition
 *
 * If no callback is set then this function will try to return the definition
 * from cache. If it has not yet been loaded then it will force a non-async
 * request which will HANG THE UI so it should only be used as a last resort.
 *
 * @param {string} objType The object type we are loading a definition for
 * @param {function} cbLoaded Callback function once definition is loaded
 * @return {Definition|void} If no callback is provded then force a return
 */
definitionLoader.get = function (objType, cbLoaded) {

    if (!objType) {
        throw "The first param {objType} is required and cannot be blank or null";
    }

    // Return (or callback callback) cached definition if already loaded
    if (this.definitions_[objType] != null) {

        if (cbLoaded) {
            cbLoaded(this.definitions_[objType]);
        }

        return this.definitions_[objType];
    }

    /*
     * Setup the request
     *
     * If the request is synchronous, then we create a new one each time.
     * If it is asynchronous then we only want one request for this object
     * being sent at a time.
     */
    var request = null;

    if (cbLoaded) {
      // Check if we do not have a backend request for this type of object
      if (!definitionLoader.requests_[objType]) {
          // Create a new backend request instance for this object
          definitionLoader.requests_[objType] = new BackendRequest();
      }

      request = definitionLoader.requests_[objType];
    } else {
      // Not an asynchronous request, just make a new one for each call
      request = new BackendRequest();
    }

    // Log errors
    alib.events.listen(request, "error", function (evt) {
        log.error("Failed to load request", evt);
    });

    if (cbLoaded) {
        alib.events.listen(request, "load", function (evt) {
            var def = definitionLoader.createFromData(this.getResponse());
            cbLoaded(def);
        });
    } else {
        // Set request to be synchronous if no callback is set
        request.setAsync(false);
    }

    /*
     * If this is an async request and there is already another request in
     * progress for this object type, then we do not need to send another
     * request. Instead piggy-back on the previous request but adding the
     * callback above but just wait for the in-progress request previously
     * running to return.
     *
     * If we are either (1) not asynchronous or (2) not in progress then send
     */
    if (!cbLoaded || !definitionLoader.requests_[objType].isInProgress()) {
      request.send("svr/entity/getDefinition", "GET", {obj_type: objType});
    }


    // If no callback then construct Definition from request date (synchronous)
    if (!cbLoaded) {
        return this.createFromData(request.getResponse());
    }
}

/**
 * Map data to an entity definition object
 *
 * @param {Object} data The data to create the definition from
 */
definitionLoader.createFromData = function (data) {

    // Construct definition and initialize with data
    var def = new Definition(data);

    // Cache it for future requests
    this.definitions_[def.objType] = def;

    return this.definitions_[def.objType];
}

/**
 * Get a pre-loaded / cached object definition
 *
 * @param {string} objType The uniqy name of the object entity type
 * @return {Definition} Entity defintion on success, null if not cached
 */
definitionLoader.getCached = function (objType) {
    if (this.definitions_[objType]) {
        return this.definitions_[objType];
    }

    return null;
}

module.exports = definitionLoader;
