/**
 * @fileOverview A view object used to define how a collection of entities is displayed to users
 *
 * @author:	Sky Stebnicki, sky.stebnicki@aereus.com;
 * 			Copyright (c) 2015 Aereus Corporation. All rights reserved.
 */
'use strict';

var Where = require("./Where");

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
    this.conditions_ = [];

    /**
     * Description of this view
     *
     * @type {string}
     */
    this.description = "";

    /**
     * Unique id if this is a saved custom view
     *
     * @type {string}
     */
    this.id	= null;

    /**
     * Short name or label for this view
     *
     * @type {string}
     */
    this.name = "";

    /**
     * Boolean to indicate if this is a system or custom view
     *
     * System views cannot be edited so it is important for the client
     * to know whether or not to allow a user to make changes to the view.
     *
     * @type {boolean}
     */
    this.system = false;

    /**
     * Array of sort order objects
     *
     * @type {Array}
     * @private
     */
    this.orderBy_ = [];

    /**
     * Which fields to display in a table view
     *
     * @type {string[]}
     * @private
     */
    this.tableColumns_ = [];

    /**
     * The scope of the view
     *
     * TODO: define
     *
     * @type {string}
     */
    this.scope = "";

    /**
     * The user id that owns this view
     *
     * @type {string}
     */
    this.userId = null;

    /**
     * The team that owns this view
     *
     * @type {string}
     */
    this.teamId = null;

    /**
     * Each report has it's own unique view to define the filter
     *
     * @type {string}
     */
    this.reportId = "";

    /**
     * Default flag for the current user
     *
     * This should only be set for one view. It is managed by the server.
     *
     * @type {bool}
     */
    this.default = false;

    // TODO: Document
    this.filterKey		= "";
}

/**
 * Set the view from a data object
 *
 * @param {Object} data
 */
BrowserView.prototype.fromData = function(data) {

    this.id = data.id;
    this.name = data.name;
    this.description = data.description;
    this.system = data.f_system;
    this.default = (data.f_default === true || data.f_default == 't') ? true : false;
    this.userId = data.user_id || null;
    this.teamId = data.team_id || null;
    this.reportId = data.report_id || null;
    this.scope = data.scope || "";

    if (data.filter_key)
        this.filterKey = data.filter_key;

    // Setup columns to display for a table view
    for (var i in data.view_fields) {
        this.tableColumns_.push(data.view_fields[i]);
    }

    for (var i in data.conditions) {
        var where = new Where(data.conditions[i].field_name);
        where.bLogic = data.conditions[i].blogic;
        this.operator = data.conditions[i].operator;
        where.value = data.conditions[i].value;
        this.conditions_.push(where);
    }

    for (var i in data.sort_order)
    {
        this.orderBy_.push({
            field : data.sort_order[i].field_name,
            direction : data.sort_order[i].order
        });
    }
}

/**
 * Get where conditions
 *
 * @return {Where[]}
 */
BrowserView.prototype.getConditions = function() {
    return this.conditions_;
}

/**
 * Get the sort order
 *
 * @return {string[]}
 */
BrowserView.prototype.getOrderBy = function() {
    return this.orderBy_;
}

/**
 * Get the table columns to view
 *
 * @return {string[]}
 */
BrowserView.prototype.getTableColumns = function() {
    return this.tableColumns_;
}

module.exports = BrowserView;
