/**
 * @fileOverview Entity for StatusUpdate
 *
 * @author =    Marl Tumulak; marl.tumulak@aereus.com;
 *            Copyright (c) 2014 Aereus Corporation. All rights reserved.
 */
'use strict';

var netric = require("../base");
var log = require("../log");
var entitySaver = require("./saver");

/**
 * Entity represents the StatusUpdate
 *
 * @constructory
 * @param {string} objReference  The object reference that we will associate this status update
 */
var StatusUpdate = function (objReference) {

    /**
     * The object type we are working with
     *
     * @private
     * @const
     * @type {string}
     */
    this.STATUS_OBJ_TYPE = "status_update"

    /**
     * Object reference that we will associate this status update
     *
     * @private
     * @type {string}
     */
    this.objReference = objReference || null;
}

/**
 * Add a status update
 *
 * @param {string} status           The status update from the user
 * @param {function} opt_callback   If set call this function when we are finished adding the status update
 *
 * @public
 */
StatusUpdate.prototype.add = function(status, opt_callback) {

    /**
     * We are setting the entity loader inside this function
     * Since need the main entity to finish loading
     * And set the definition of the main objType (not this status update objType)
     */
    var entityLoader = require("./loader");

    // Create a new comment and save it
    var ent = entityLoader.factory(this.STATUS_OBJ_TYPE);

    if (status) {
        ent.setValue("comment", status);
    }

    // Add the user
    var userId = -3; // -3 is 'current_user' on the backend
    if (netric.getApplication().getAccount().getUser()) {
        userId = netric.getApplication().getAccount().getUser().id;
    }
    ent.setValue("owner_id", userId);

    // Add an object reference
    if (this.objReference) {

        // This is how we associate comments with a specific entity object
        ent.setValue("obj_reference", this.objReference);
    }

    // Save the entity
    entitySaver.save(ent, function () {
        log.info("Saved status update on", this.objReference);

        if(opt_callback) {
            opt_callback();
        }
    }.bind(this));
}

module.exports = StatusUpdate;