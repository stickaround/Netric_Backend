/**
 * @fileOverview Entity Objects Loader
 *
 * @author =    Marl Tumulak; marl.tumulak@aereus.com;
 *            Copyright (c) 2016 Aereus Corporation. All rights reserved.
 */
'use strict';

var BackendRequest = require("../BackendRequest");
var log = require("../log");

/**
 * Global Objects Loader namespace
 */
var objectsLoader = {};

/**
 * Keep a reference to loaded objects to reduce requests
 *
 * @private
 * @param {Array}
 */
objectsLoader._objects = null;

/**
 * Load the entity objects
 *
 * @param {function} cbLoaded Callback function once definition is loaded
 * @return {Definition|void} If no callback is provded then force a return
 *
 * @public
 */
objectsLoader.get = function (cbLoaded) {

    // Return (or callback callback) cached object if already loaded
    if (objectsLoader._objects) {

        if (cbLoaded) {
            cbLoaded(objectsLoader._objects);
        }

        return objectsLoader._objects;
    }

    // Create an instance of BackendRequest
    var request = new BackendRequest();

    // Log errors
    alib.events.listen(request, "error", function (evt) {
        log.error("Failed to load request", evt);
    });

    if (cbLoaded) {
        alib.events.listen(request, "load", function (evt) {
            objectsLoader._objects = this.getResponse();
            cbLoaded(objectsLoader._objects);
        });
    } else {

        // Set request to be synchronous if no callback is set
        request.setAsync(false);
    }

    // Send request
    request.send("svr/entity/getObjects", "GET");

    // If no callback then construct Definition from request date (synchronous)
    if (!cbLoaded) {
        objectsLoader._objects = request.getResponse();
        return objectsLoader._objects;
    }
}

module.exports = objectsLoader;