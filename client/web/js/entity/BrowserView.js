/**
 * @fileOverview A view object used to define how a collection of entities is displayed to users
 *
 * TODO: This is a work in progress...
 *
 * @author:	Sky Stebnicki, sky.stebnicki@aereus.com;
 * 			Copyright (c) 2015 Aereus Corporation. All rights reserved.
 */
'use strict';

/**
 * Define the view of an entity collection
 *
 * @constructor
 * @param {string} objType The name of the object type that owns the grouping field
 */
var BrowserView = function(objType) {

    /**
     * The name of the object type we are working with
     *
     * @public
     * @type {string|string}
     */
    this.objType = objType || "";

    /**
     * Array of where conditions
     *
     * @type {Where[]}
     * @private
     */
    this.conditions_ = new Array();
}

/**
 * Set groupings from an array
 *
 * @type {Function}
 */
BrowserView.prototype.fromArray = function(data) {

}

module.exports = BrowserView;
