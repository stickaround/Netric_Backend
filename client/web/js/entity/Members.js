/**
 * @fileOverview Members model that will handle the actions for member
 *
 *
 * @author:    Marl Tumulak, marl.tumulak@aereus.com;
 *            Copyright (c) 2015 Aereus Corporation. All rights reserved.
 */
'use strict';

var Member = require('./definition/Member');

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
 * @param {object} data Member data that will be created
 * @public
 */
Members.prototype.add = function (data) {
    // TODO Before adding the member, make sure it is unique

    var member = new Member(data);

    this._members.push(member);

    return member;
}

/**
 * Get the members of this entity
 *
 * @return Array Collection of Entity/Members
 * @public
 */
Members.prototype.get = function () {
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
 * Extract the name of member if it is being transformed to [user:userId:userName]
 *
 * @return Object Contains the extracted data of member
 * @public
 */
Members.prototype.extractNameReference = function (name) {
    var memberReference = null;

    // Extract all [<obj_type>:<id>:<name>] tags from string
    var matches = name.match(/\[([a-z_]+)\:(.*?)\:(.*?)\]/);

    if (matches) {

        // Get the member data if we have found a match
        memberReference = {
            objType: matches[1],
            id: matches[2],
            name: matches[3]
        }
    } else {

        // Set the objType and Id to null if there is no match, then just set the provided name as member's name
        memberReference = {
            objType: null,
            id: null,
            name: name
        }
    }

    return memberReference;
}

module.exports = Members;