/**
 * @fileoverview Actions for Customer
 */
'use strict';

var ActionModes = require("./actionModes");
var entitySaver = require("../saver");
var DefaultActions = require("./DefaultActions");
var netric = require("../../base");
var log = require("../../log");

var controller = require("../../controller/controller");

/**
 * This is the customer actions class that will display edit, remove, print and convert customer action buttons
 */
var CustomerActions = function () {

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
netric.inherits(CustomerActions, DefaultActions);

/**
 * Default actions when in view mode
 *
 * @protected
 * @type {Array}
 */
CustomerActions.prototype.defaultViewActions = [
    {name: "edit", title: "Edit", iconClassName: "fa fa-pencil"},
    {name: "remove", title: "Delete", iconClassName: "fa fa-trash-o"},
    {name: "print", title: "Print", iconClassName: "fa fa-print"},
    {name: "followup", title: "Follow-Up", iconClassName: "fa fa-street-view"},
];

/**
 * Action that will enable the user to followup a customer
 *
 * @param {string} objType The type of object to perform the action on
 * @param {int[]} selectedEntities The entities to perform the action on
 * @param {function} finishedFunction A funciton to call when finished
 * @return {string} Working text like "Deleting" or "Saving"
 */
CustomerActions.prototype.followup = function (objType, selectedEntities, finishedFunction) {

    if (selectedEntities.length > 1) {
        throw "Can only convert one customer entity at a time.";
    }

    var customerId = selectedEntities[0];

    var EntityPluginController = require("../../controller/EntityPluginController");
    var entityPlugin = new EntityPluginController();


    entityPlugin.load({
        type: controller.types.DIALOG,
        pluginName: "customer.Followup",
        objType: "customer",
        title: "Follow-up Customer",
        eid: customerId,
        onFinishedAction: function (postAction) {
            finishedFunction(false, "Followed up a customer by " + postAction.type + " " + postAction.data.objType, postAction);
        }
    });

    // We do not want any working text since this displays a dialog
    return null;
}

module.exports = CustomerActions;
