/**
 * @fileOverview Define the objects fields of file
 *
 * This class is a client side mirror of /lib/EntityDefinition/File on the server side
 *
 * @author:	Marl Tumulak, marl.tumulak@aereus.com;
 * 			Copyright (c) 2013 Aereus Corporation. All rights reserved.
 */
'use strict';

/**
 * Creates an instance of File
 *
 * @param {Object} opt_data The definition the file
 * @constructor
 */
var File = function(opt_data) {

    var data = opt_data || new Object();

    /**
     * Unique id if the File was loaded from a database
     *
     * @public
     * @type {string}
     */
    this.id = data.id || null;

    /**
     * File name (REQUIRED)
     *
     * @public
     * @type {string}
     */
    this.name = data.name || "";

    /**
     * Data type of the file
     *
     * @public
     * @type {string}
     */
    this.type = data.type || "";

    /**
     * Size of the file
     *
     * @public
     * @type {int}
     */
    this.size = data.size || "";

    /**
     * The download url link of the file
     *
     * @public
     * @type {string}
     */
    this.url = data.url || null;

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
    };
}

module.exports = File;