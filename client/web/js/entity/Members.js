/**
 * @fileOverview Members model that will handle the actions for member
 *
 *
 * @author:    Marl Tumulak, marl.tumulak@aereus.com;
 *            Copyright (c) 2015 Aereus Corporation. All rights reserved.
 */
'use strict';

/**
 * Creates an instance of Members
 *
 * @param {object} fieldName The actual field name used for the member object
 * @constructor
 */
var Members = function (fieldName) {

    /**
     * The actual field name used for the member object
     *
     * @param {string} fieldName
     */
    this.fieldName = fieldName;

    /**
     * This will contain the instances of definition/Member
     *
     * @type {Array}
     */
    this._members = [];
}

/**
 * Adds a member into the entity
 *
 * @param {Entity/Entity} member Instance of entity with member objType
 * @public
 */
Members.prototype.add = function (member) {

    // Make sure we are adding unique members in the entity
    if(!this.checkIfExist(member.getValue("name"))) {
        this._members.push(member);
    }
}

/**
 * Get the members of this entity
 *
 * @return Array Collection of Entity/Members
 * @public
 */
Members.prototype.getAll = function () {
    return this._members;
}

/**
 * Get the new members by check if the member id is null
 *
 * @return Array Collection of new Entity/Members with id equals to null
 * @public
 */
Members.prototype.getNewMembers = function () {
    var newMembers = [];

    this._members.map(function (member) {
        if (member.id == null) {
            newMembers.push(member.toData())
        }
    })

    return newMembers;
}

/**
 * Remove a member to the member list
 *
 * @param {int} id The Id that will be removed
 * @param {int} name The name that will be removed. if Id is null, then we will use the name to remove the member
 * @public
 */
Members.prototype.remove = function (id, name) {
    for(var idx in this._members) {
        var member = this._members[idx];
        if ((id && member.id == id) || (name && member.getValue("name") == name)) {
            this._members.splice(idx, 1);
            break;
        }
    }
}

/**
 * Function that will check if member already exist for this entity
 *
 * @param {string} name The name of the member that will be checked if it already exists
 * @return bool True if it already exist and False if not
 * @public
 */
Members.prototype.checkIfExist = function (name) {
    var result = false;

    this._members.map(function (member) {

        // Check if we have a match
        if(member.getValue("name") == name) {
            result = true; // member already exist
        }
    })

    return result;
}

module.exports = Members;