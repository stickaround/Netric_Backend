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
 * Load the entity objects
 *
 * @param {function} cbLoaded Callback function once definition is loaded
 * @return {Definition|void} If no callback is provded then force a return
 *
 * @public
 */
objectsLoader.load = function (cbLoaded) {

    var request = new BackendRequest();

    // Log errors
    alib.events.listen(request, "error", function (evt) {
        log.error("Failed to load request", evt);
    });

    if (cbLoaded) {
        alib.events.listen(request, "load", function (evt) {
            var objects = this.getResponse();
            cbLoaded(objects);
        });
    } else {

        // Set request to be synchronous if no callback is set
        request.setAsync(false);
    }

    // Send request
    request.send("svr/entity/getObjects", "GET");

    // If no callback then construct Definition from request date (synchronous)
    if (!cbLoaded) {
        return request.getResponse();
    }
}

module.exports = objectsLoader;