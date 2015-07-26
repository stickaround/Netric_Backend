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

				console.log(resp);

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
			// Save the data locally into an "outbox" 
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

	}

}

module.exports = saver;
