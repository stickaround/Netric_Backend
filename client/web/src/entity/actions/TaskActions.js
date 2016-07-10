/**
 * @fileoverview Default actions
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
var TaskActions = function() {

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
netric.inherits(TaskActions, DefaultActions);

/**
 * Default actions when in view mode
 *
 * @protected
 * @type {Array}
 */
TaskActions.prototype.defaultViewActions = [
    { name: "edit", title: "Edit", iconClassName: "fa fa-pencil" },
    { name: "remove", title: "Delete", iconClassName: "fa fa-trash-o" },
    { name: "print", title: "Print", iconClassName: "fa fa-print"},
    { name: "addtime", title: "Add Time", iconClassName: "fa fa-clock-o" },
];

/**
 * Add time entity action
 *
 * @param {string} objType The type of object to perform the action on
 * @param {int[]} selectedEntities The entities to perform the action on
 * @param {function} finishedFunction A funciton to call when finished
 * @return {string} Working text like "Deleting" or "Saving"
 */
TaskActions.prototype.addtime = function(objType, selectedEntities, finishedFunction) {

    log.notice("Action called: addtime");

    var taskId = selectedEntities[0];

    var EntityController = require("../../controller/EntityController");
    var timeEntity = new EntityController();
    timeEntity.load({
        objType: "time",
        type: controller.types.DIALOG,
        title: "Add Time",
        entityData: {
            task_id: taskId,
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

module.exports = TaskActions;
