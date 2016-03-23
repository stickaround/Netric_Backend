/**
 * @fileOverview Definition Saver
 *
 * @author:    Sky Stebnicki, sky.stebnicki@aereus.com;
 *            Copyright (c) 2014 Aereus Corporation. All rights reserved.
 */
'use strict';

var BackendRequest = require("../BackendRequest");
var definitionLoader = require("./definitionLoader");
var nertic = require("../base");
var log = require("../log");

/**
 * Entity definition Saver
 */
var definitionSaver = {};

/**
 * Save a field definition
 *
 * @param {string} objType The object type where we will add the new custom field
 * @param {entity/definition/Field} field The custom field that we are going to add
 * @param {function} opt_finishedCallback Optional callback to call when saved
 */
definitionSaver.save = function (objType, field, opt_finishedCallback) {

    if (!objType) {
        throw "entity/definitionSaver/save: objType must be provided";
    }

    if (!field) {
        throw "entity/definitionSaver/save: field must be provided";
    }

    // create the field data
    var fieldData = {
        obj_type: objType,
        data: field.getData()
    }

    // Create a reference to this for tricky callbacks
    var saverObj = this;

    // If we are connected
    if (netric.server.online) {
        // Save the data remotely
        BackendRequest.send("svr/entity/addEntityField", function (resp) {

            // First check to see if there was an error
            if (resp.error) {
                throw "Error saving entity definition field: " + resp.error;
            }

            // Update the definition cache
            let def = definitionLoader.updateDefinition(resp);

            // Invoke callback if set
            if (opt_finishedCallback) {
                opt_finishedCallback(def);
            }

        }, 'POST', JSON.stringify(fieldData));

    } else {
        // TODO: Save the data locally into an "outbox"
        // to be saved on the next connection
    }
}

definitionSaver.remove = function(objType, fieldName, opt_finishedCallback) {

    if (!objType) {
        throw "entity/definitionSaver/remove: objType must be provided";
    }

    if (!fieldName) {
        throw "entity/definitionSaver/remove: fieldName must be provided";
    }

    // Setup request properties
    var data = {obj_type: objType, name: fieldName};

    // Create a reference to this for tricky callbacks
    var saverObj = this;

    // If we are connected
    if (netric.server.online) {
        // Save the data remotely
        BackendRequest.send("svr/entity/deleteEntityField", function(resp) {

            // First check to see if there was an error
            if (resp.error) {
                throw "Error removing field: " + resp.error;
            }

            // Update the definition cache
            let def = definitionLoader.updateDefinition(resp);

            // Invoke callback if set
            if (opt_finishedCallback) {
                opt_finishedCallback(def);
            }

        }, 'POST', JSON.stringify(data));

    } else {
        // TODO: Save the data locally into an "outbox"
        // to be deleted on the next connection
    }
},

module.exports = definitionSaver;