/**
 * @fileoverview Default actions for Lead
 */
'use strict';

var ActionModes = require("./actionModes");
var entitySaver = require("../saver");
var DefaultActions = require("./DefaultActions");
var netric = require("../../base");
var log = require("../../log");

var controller = require("../../controller/controller");

/**
 * This is the base/default actions class that all other object types will inherit
 */
var LeadActions = function() {

    /**
     * Optional setup local confirm messages
     *
     * @type {Object}
     */
    this.confirmMessages = {};
}

/**
 * Extend base actions class
 */
netric.inherits(LeadActions, DefaultActions);

/**
 * Default actions when in view mode
 *
 * @protected
 * @type {Array}
 */
LeadActions.prototype.defaultViewActions = [
    { name: "edit", title: "Edit", iconClassName: "fa fa-pencil" },
    { name: "remove", title: "Delete", iconClassName: "fa fa-trash-o" },
    { name: "print", title: "Print", iconClassName: "fa fa-print"},
    { name: "convertlead", title: "Convert Lead", iconClassName: "fa fa-exchange" },
];

/**
 * Add time entity action
 *
 * @param {string} objType The type of object to perform the action on
 * @param {int[]} selectedEntities The entities to perform the action on
 * @param {function} finishedFunction A funciton to call when finished
 * @return {string} Working text like "Deleting" or "Saving"
 */
LeadActions.prototype.convertlead = function(objType, selectedEntities, finishedFunction) {

    log.notice("Action called: addtime");

    var leadId = selectedEntities[0];

    var EntityController = require("../../controller/EntityController");
    var timeEntity = new EntityController();
    timeEntity.load({
        objType: "time",
        type: controller.types.DIALOG,
        title: "Add Time",
        entityData: {
            lead_id: leadId,
            owner_id: {key: "-3", value: "Me"},
            creator_id: {key: "-3", value: "Me"}
        },
        onSave: function(timeEntity) {
            finishedFunction(false, "Time Added");
        }
    });

    /*
     entitySaver.remove(objType, selectedEntities, function(removedIds) {
     finishedFunction(false, removedIds.length + " Items Deleted");
     });

     return "Deleting";
     */

    // We do not want any working text since this displays a dialog
    return null;
}

module.exports = LeadActions;
