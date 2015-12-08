/**
 * @fileOverview Status Update saver
 *
 * @author =    Marl Tumulak; marl.tumulak@aereus.com;
 *            Copyright (c) 2015 Aereus Corporation. All rights reserved.
 */
'use strict';

var netric = require("../base");
var log = require("../log");
var entitySaver = require("./saver");

/**
 * Global Status Update Manager namespace
 */
var StatusUpdateManager = {};

StatusUpdateManager.objReference = null;

/**
 * Send a status update
 *
 * @param {string} comment                  The status update from the user
 * @param {entity/Entity} opt_entity        The optional entity we are providing an update on
 * @param {function} opt_finishedCallback   If set call this function when we are finished adding the status update
 *
 * @public
 */
StatusUpdateManager.send = function (comment, opt_entity, opt_finishedCallback) {

    // Do not save an empty status update/comment
    if (!comment) {
        return;
    }

    var entity = opt_entity || null;

    // If entity is not provided, then we will create a blank entity
    if (!entity) {

        /**
         * We are setting the entity loader inside this function
         * Since need the main entity to finish loading
         * And set the definition of the main objType (not this status update objType)
         */
        var entityLoader = require("./loader");

        // Create a new comment and save it
        var entity = entityLoader.factory('status_update');
    }


    if (comment) {
        entity.setValue("comment", comment);
    }

    // Add the user
    var userId = -3; // -3 is 'current_user' on the backend
    if (netric.getApplication().getAccount().getUser()) {
        userId = netric.getApplication().getAccount().getUser().id;
    }
    entity.setValue("owner_id", userId);

    // Add an object reference
    if (this.objReference) {

        // This is how we associate comments with a specific entity object
        entity.setValue("obj_reference", this.objReference);
    }

    // Save the entity
    entitySaver.save(entity, function () {
        log.info("Saved status update on", this.objReference);

        if (opt_finishedCallback) {
            opt_finishedCallback();
        }
    }.bind(this));
}

module.exports = StatusUpdateManager;