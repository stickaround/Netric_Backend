/**
 * @fileOverview Groupings for an entity object type
 *
 * @author:	Sky Stebnicki, sky.stebnicki@aereus.com;
 * 			Copyright (c) 2014-2015 Aereus Corporation. All rights reserved.
 */
'use strict';

/**
 * Represents a collection of entities
 *
 * @constructor
 * @param {string} objType The name of the object type that owns the grouping field
 * @param {string} fieldName The name of the grouping field
 * @param {Array} opt_filter Optional filter
 */
var Groupings = function(objType, fieldName, opt_filter) {
    /**
     * The name of the object type we are working with
     *
     * @public
     * @type {string|string}
     */
    this.objType = objType || "";

    /**
     * The grouping field name
     *
     * @public
     * @type {string|string}
     */
    this.fieldName = fieldName || "";

    /**
     * An optional array of filters
     *
     * @public
     * @type {Array}
     */
    this.filter = opt_filter || [];

    /**
     * Array of groupings
     *
     * @type {Array}
     */
    this.groups = null
}

/**
 * Set groupings from an array
 *
 * @type {Function}
 */
Groupings.prototype.fromArray = function(data) {
    this.groups = data.groups;

    // Trigger onchange event to alert any observers that this value has changed
    alib.events.triggerEvent(this, "change", {});
}

/**
 * Get all the groups in this grouping
 *
 * @returns {Array}
 */
Groupings.prototype.getGroups = function() {
    return this.groups;
}

/**
 * Get all the groupings in a hierarchical structure with group.children being populated
 *
 * @param {int} parentId Optional parent ID, other start at the root
 */
Groupings.prototype.getGroupsHierarch = function(parentId) {
    var output = [];
    if (typeof parentId == "undefined") {
        parentId == null;
    }

    for (var i in this.groups) {
        if (this.groups[i].parent_id == parentId) {

            // Copy to variable
            var group = this.groups[i];

            // If this is not a new group, then get the children
            if (group.id) {
                group.children = this.getGroupsHierarch(group.id);
            }

            // Add to the output buffer
            output.push(group);
        }
    }

    return output;
}

module.exports = Groupings;
