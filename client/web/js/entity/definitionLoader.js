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
 * @param {Array}
 */
definitionLoader.requests_ = new Array();

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

    // Check if we do not have a backend request for this type of object
    if (!definitionLoader.requests_[objType]) {

        // Create a new backend request instance for this object
        definitionLoader.requests_[objType] = new BackendRequest();
    }

    var request = definitionLoader.requests_[objType];

    // If ths object's backend request still in-progress, we do not need to send another request
    if (!definitionLoader.requests_[objType].isInProgress()) {

        if (cbLoaded) {
            alib.events.listen(request, "load", function (evt) {
                var def = definitionLoader.createFromData(this.getResponse());
                cbLoaded(def);
            });

            alib.events.listen(request, "error", function (evt) {
                log.error("Failed to load request", evt);
            });
        } else {
            // Set request to be synchronous if no callback is set
            request.setAsync(false);
        }

        request.send("svr/entity/getDefinition", "GET", {obj_type: objType});

        // If no callback then construct Definition from request date (synchronous)
        if (!cbLoaded) {
            return this.createFromData(request.getResponse());
        }
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
