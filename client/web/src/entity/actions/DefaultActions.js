/**
 * @fileoverview Default actions
 */
'use strict';

var ActionModes = require("./actionModes");
var entitySaver = require("../saver");

/**
 * This is the base/default actions class that all other object types will inherit
 */
var DefaultActions = function() {

	/**
	 * Optional setup local confirm messages
	 *
	 * @type {Object}
	 */
	this.confirmMessages = {};
}

/**
 * Example of any derrived classes extending this
 */
//netric.inherits(TaskActions, DefaultActions);

/**
 * Default actions when in browse mode
 *
 * @protected
 * @type {Array}
 */
DefaultActions.prototype.defaultBrowseActions = [
	{ name: "remove", title: "Delete", iconClassName: "fa fa-trash-o" },
	//{ name: "print", title: "Print", iconClassName: "fa fa-print"}
];

/**
 * Default actions when in view mode
 *
 * @protected
 * @type {Array}
 */
DefaultActions.prototype.defaultViewActions = [
	{ name: "edit", title: "Edit", iconClassName: "fa fa-pencil" },
	{ name: "remove", title: "Delete", iconClassName: "fa fa-trash-o" },
	{ name: "print", title: "Print", iconClassName: "fa fa-print"}
];

/**
 * Default actions when in edit mode
 *
 * @protected
 * @type {Array}
 */
DefaultActions.prototype.defaultEditActions = [
	{ name: "save", title: "Save", iconClassName: "fa fa-check" }
];

/**
 * Get available actions depending on whether or not we have selected entities
 *
 * The first action is always assumed to be the PRIMARY action and will be given
 * visual precedence on all devices.
 *
 * @param {int[]} selectedEntities Array of selected entity IDs
 * @return {Array} TODO: Define
 */
DefaultActions.prototype.getActions = function(mode, selectedEntities) {

	var numSelected = (typeof selectedEntities != "undefined") ? selectedEntities.length : 0;
	
	// We return an array of actions filtered based on the mode
	var retActions = new Array();

	switch (mode) {
		case ActionModes.BROWSE:
			retActions = this.defaultBrowseActions;
			break;
		case ActionModes.VIEW:
			retActions = this.defaultViewActions;
			break;
		case ActionModes.EDIT:
			retActions = this.defaultEditActions;
			break;
		default:
			// TODO: Return nothing and log an error
			break;
	}

	return retActions;
}

/**
 * Check to see if we need to prompt the user for a confirmation before perfomring action
 *
 * @param {string} actionName
 * @param {array} selectedEntities
 */
DefaultActions.prototype.getConfirmMessage = function(actionName, selectedEntities) {
	var messages = this.confirmMessages || {};
	return (messages[actionName]) ? messages[actionName] : null;
}

/**
 * Perform an action on the selected entities
 *
 * @param {string} actionName The unique name of the action to perform
 * @param {string} objType The type of object we re performing actions on
 * @param {int[]} selectedEntities The entities to perform the action on
 * @param {function} finishedFunction A funciton to call when finished
 * @return {string} Working text like "Deleting" or "Saving"
 */
DefaultActions.prototype.performAction = function(actionName, objType, selectedEntities, finishedFunction) {

	if (typeof finishedFunction === "undefined") {
		finishedFunction = function() {};
	}

	// Check to see if the handler exists
	if (typeof this[actionName] === "function") {
		var funct = this[actionName];
		return funct(objType, selectedEntities, finishedFunction);
	} else {
		throw "Action function " + actionName + " not defined";
	}

	/*
	file
		upload: file upload dialog
		move: folder open dialog

	folder
		move: folder open dialog

	email:
		reply: open compose window
		replyAll: open compose window
		forward: open compose window

	email_thread
		addToGroup: add a value to multivalue & save

	customer:
		followUp: open follow-up window
	*/

	/*
	TODO:
	Figure out how to deal with actions that require input and/or a dialog like uploading files
	or replying to an email.

	RETURN:
		error: bool
		message: string

	*/
}

/**
 * Entity delete action
 *
 * @param {string} objType The type of object to perform the action on
 * @param {int[]} selectedEntities The entities to perform the action on
 * @param {function} finishedFunction A funciton to call when finished
 * @return {string} Working text like "Deleting" or "Saving"
 */
DefaultActions.prototype.remove = function(objType, selectedEntities, finishedFunction) {

	entitySaver.remove(objType, selectedEntities, function(removedIds) {
		finishedFunction(false, removedIds.length + " Items Deleted");
	});

	return "Deleting";
}

/**
 * Entity delete action
 *
 * @param {string} objType The type of object to perform the action on
 * @param {int[]} selectedEntities The entities to perform the action on
 * @param {function} finishedFunction A funciton to call when finished
 * @return {string} Working text like "Deleting" or "Saving"
 */
DefaultActions.prototype.print = function(objType, selectedEntities, finishedFunction) {

    log.notice("Printing " + selectedEntities.length + " " + objType);

    // TODO: still not implemented

    return "";
}

module.exports = DefaultActions;
