/**
* @fileOverview Entity saver
*
* @author:	Sky Stebnicki, sky.stebnicki@aereus.com; 
* 			Copyright (c) 2015 Aereus Corporation. All rights reserved.
*/
'use strict';

var definitionLoader = require("./definitionLoader");
var BackendRequest = require("../BackendRequest");
var Entity = require("./Entity");
var nertic = require("../base");

var saver = {
	
	/**
	 * Save an entity
	 *
	 * @param {netric\entity\Entity} entity The entity to save
	 * @param {function} opt_finishedCallback Optional callback to call when saved
	 */
	save: function(entity, opt_finishedCallback) {

		if (!entity) {
			throw "entity/saver/save: First param must be an entity";
		}

		// Get data object from the entity properties
		var data = entity.getData();

		// Create a reference to this for tricky callbacks
		var saverObj = this;

		// If we are connected
		if (netric.server.online) {
			// Save the data remotely
			BackendRequest.send("svr/entity/save", function(resp) {

				// First check to see if there was an error
				if (resp.error) {
					throw "Error saving entity: " + resp.error;
				}

				// Update the data in the original entity
				entity.loadData(resp);

				// Save locally (no callback because we don't care if it fails)
				saverObj.saveLocal(entity);

				// Invoke callback if set
				if (opt_finishedCallback) {
					opt_finishedCallback();
				}

			}, 'POST', JSON.stringify(data));
			
		} else {
			// TODO: Save the data locally into an "outbox"
			// to be saved on the next connection
		}
	},

	/**
	 * Save an entity
	 *
	 * @param {netric\entity\Entity} entity The entity to save
	 * @param {function} opt_finishedCallback Optional callback to call when saved
	 */
	saveLocal: function(entity, opt_finishedCallback) {
		
		// TODO: save locally

	},

	/**
	 * Delete an entity
	 *
	 * @param {string} objType The type of entity we are deleting
	 * @param {string[]} iDs The id or ids of entities we are deleting
	 * @param {function} opt_finishedCallback Optional callback to call when deleted
	 */
	remove: function(objType, iDs, opt_finishedCallback) {

		if (!objType) {
			throw "entity/saver/remove: First param must be an object type";
		}

		if (!iDs) {
			throw "entity/saver/remove: Second param must be an entity id or array if IDs";
		}

		// Setup request properties
		var data = {obj_type: objType, id: iDs};

		// Create a reference to this for tricky callbacks
		var saverObj = this;

		// If we are connected
		if (netric.server.online) {
			// Save the data remotely
			BackendRequest.send("svr/entity/remove", function(resp) {

				// First check to see if there was an error
				if (resp.error) {
					throw "Error removing entity: " + resp.error;
				}

				// Remove all IDs locally
				for (var i in resp) {
					saverObj.removeLocal(objType, resp[i]);
				}

				// Invoke callback if set
				if (opt_finishedCallback) {
					opt_finishedCallback(resp);
				}

			}, 'POST', data);

		} else {
			// TODO: Save the data locally into an "outbox"
			// to be deleted on the next connection
		}
	},

	/**
	 * Queue an entity locally for removal
	 *
	 * @param {string} objType The type of entity we are deleting
	 * @param {string[]} iDs The id or ids of entities we are deleting
	 * @param {function} opt_finishedCallback Optional callback to call when deleted
	 */
	removeLocal: function(objType, iDs, opt_finishedCallback) {

		// TODO: remove locally
	}

}

module.exports = saver;
