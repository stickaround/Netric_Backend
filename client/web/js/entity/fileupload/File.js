/**
 * @fileOverview Define the objects fields of file
 *
 * This class is a client side mirror of /lib/EntityDefinition/File on the server side
 *
 * @author:    Marl Tumulak, marl.tumulak@aereus.com;
 *            Copyright (c) 2013 Aereus Corporation. All rights reserved.
 */
'use strict';

/**
 * Creates an instance of File
 *
 * @param {entity/Entity} entity     The entity definition of the file object
 * @constructor
 */
var File = function (entity) {

    /**
     * The entity of the file object
     *
     * @private
     * @type {netric.entity.Entity}
     */
    this._fileEntity = entity;

    /**
     * The download url link of the file
     *
     * @public
     * @type {string}
     */
    this.url = null;

    /**
     * Determine if the file has tried getting the url
     *
     * @public
     * @type {bool}
     */
    this.urlLoaded = false;

    /**
     * Progress data that is used when uploading a file
     *
     * @public
     * @type {object}
     */
    this.progress = {
        uploaded: 0,
        total: 0,
        progressCompleted: 0,
        errorText: null
    }
}

/**
 * Set the value of a field of the file entity
 *
 * @param {string} name The name of the field to set
 * @param {mixed} value The value to set the field to
 */
File.prototype.setValue = function (name, value) {
    this._fileEntity.setValue(name, value);
}

/**
 * Get the value for an file entity field
 *
 * @public
 * @param {string} name The unique name of the field to get the value for
 */
File.prototype.getValue = function (name) {
    return this._fileEntity.getValue(name);
}

module.exports = File;