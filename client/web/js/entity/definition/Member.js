/**
 * @fileOverview Define the properties of member object
 *
 *
 * @author:    Marl Tumulak, marl.tumulak@aereus.com;
 *            Copyright (c) 2015 Aereus Corporation. All rights reserved.
 */
'use strict';

/**
 * Creates an instance of Member
 *
 * @param {object} opt_data Data of the member
 * @constructor
 */
var Member = function (opt_data) {

    var data = opt_data || new Object();

    /**
     * The id of the member
     *
     * data.key is sometimes used to get the id of file when constructing this from an entity data.
     *
     * @public
     * @type {int}
     */
    this.id = data.id || data.key || null;

    /**
     * The name of the member
     *
     * data.value is sometimes used to get the name of member when constructing this from an entity data.
     *
     * @public
     * @type {string}
     */
    this.name = data.name || data.value || null;

    /**
     * The member object
     *
     * @public
     * @type {string}
     */
    this.objMember = null;

    /**
     * The referenced object for this member.
     *
     * @public
     * @type {object}
     */
    this.objReference = null;

    /**
     * Determines if the member has accepted the invitation
     *
     * As default, we will set this to false
     *
     * @public
     * @type {bool}
     */
    this.accepted = false;
}

/**
 * Get the member data
 *
 * @public
 */
Member.prototype.toData = function () {

    var data = {
        id: this.id,
        name: this.name,
        obj_member: this.objMember,
        obj_reference: this.objReference,
        f_accepted: this.accepted,
    }

    return data;
}

module.exports = Member;