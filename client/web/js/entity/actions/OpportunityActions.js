/**
 * @fileoverview Actions for Opportunity
 */
'use strict';

var ActionModes = require("./actionModes");
var entitySaver = require("../saver");
var DefaultActions = require("./DefaultActions");
var netric = require("../../base");
var log = require("../../log");

var controller = require("../../controller/controller");

/**
 * This is the opportunity actions class that will display edit, remove, print and followup opportunity action buttons
 */
var OpportunityActions = function () {

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
netric.inherits(OpportunityActions, DefaultActions);

/**
 * Default actions when in view mode
 *
 * @protected
 * @type {Array}
 */
OpportunityActions.prototype.defaultViewActions = [
    {name: "edit", title: "Edit", iconClassName: "fa fa-pencil"},
    {name: "remove", title: "Delete", iconClassName: "fa fa-trash-o"},
    {name: "print", title: "Print", iconClassName: "fa fa-print"},
    {name: "followup", title: "Follow-Up", iconClassName: "fa fa-street-view"},
];

/**
 * Action that will enable the user to followup an opportunity
 *
 * @param {string} objType The type of object to perform the action on
 * @param {int[]} selectedEntities The entities to perform the action on
 * @param {function} finishedFunction A funciton to call when finished
 * @return {string} Working text like "Deleting" or "Saving"
 */
OpportunityActions.prototype.followup = function (objType, selectedEntities, finishedFunction) {

    if (selectedEntities.length > 1) {
        throw "Can only convert one opportunity entity at a time.";
    }

    var opportunityId = selectedEntities[0];

    var EntityPluginController = require("../../controller/EntityPluginController");
    var entityPlugin = new EntityPluginController();


    entityPlugin.load({
        type: controller.types.DIALOG,
        title: "Follow-up Opportunity",
        pluginName: "crm.Followup",
        objType: "opportunity",
        eid: opportunityId,
        onFinishedAction: function (postAction) {
            finishedFunction(false, "Followed up a opportunity by " + postAction.type + " " + postAction.data.objType, postAction);
        }
    });

    // We do not want any working text since this displays a dialog
    return null;
}

module.exports = OpportunityActions;
