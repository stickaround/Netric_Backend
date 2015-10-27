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
     * Array of temporary where conditions. This is used in Advanced Search.
     * We need to use temp conditions because we dont want to update directly the conditions_
     *
     * @type {Where[]}
     * @private
     */
    this.tempConditions_ = [];

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
     * Array of temporary sort order objects. This is used in Advanced Search
     * We need to use temp sort order because we dont want to update directly the orderBy_
     *
     * @type {Array}
     * @private
     */
    this.tempOrderBy_ = [];

    /**
     * Which fields to display in a table view
     *
     * @type {string[]}
     * @private
     */
    this.tableColumns_ = [];
    
    /**
     * Temporary fields that will be in advanced search.
     * We need to use temp columns because we dont want to update directly the tableColumns_ 
     *
     * @type {string[]}
     * @private
     */
    this.tempTableColumns_ = [];

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
        var where = this.applyAdvancedSearch_(data.conditions[i]);
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
 * Creates an instance of Where Object using the condition data provided
 * 
 * @param {object} condition    Object data that has the info of the saved condition
 * @private
 */
BrowserView.prototype.applyAdvancedSearch_ = function(condition) {
    var where = new Where(condition.field_name || condition.fieldName);
    
    where.bLogic = condition.blogic;
    where.operator = condition.operator;
    where.value = condition.value;
    
    return where;
}

/**
 * Applies the temp conditions, sort order, and column view to the browserView actual data
 * This function is executed in Advanced Search.
 *  
 * @public
 */
BrowserView.prototype.applyAdvancedSearch = function() {
    this.conditions_ = this.tempConditions_;
    this.orderBy_ = this.tempOrderBy_;
    
    // We need to loop thru the table columns since it has different structure
    this.tableColumns_ =[];
    
    for(var idx in this.tempTableColumns_) {
        this.tableColumns_.push(this.tempTableColumns_[idx].fieldName);
    }
}

/**
 * Applies the temp conditions, sort order, and column view to the actual browserView and updates the display list
 * This function is executed in Advanced Search.
 *  
 * @public
 */
BrowserView.prototype.populateTempData = function() {
    // Clear out temp data
    this.tempConditions_ = [];
    this.tempOrderBy_ = [];
    this.tempTableColumns_ = [];
    
    // Copy the current conditions.
    for(var idx in this.conditions_) {
        var where = this.applyAdvancedSearch_(this.conditions_[idx]);
        this.tempConditions_.push(where);
    }
    
    // Copy the current sort order.
    for(var idx in this.orderBy_) {
        this.tempOrderBy_.push({
                field: this.orderBy_[idx].field,
                direction: this.orderBy_[idx].direction
        });
    }
    
    // Copy the current table columns.
    for(var idx in this.tableColumns_) {
        var fieldName = this.tableColumns_[idx];
        this.tempTableColumns_.push({
            fieldName: fieldName
        });
    }
}

/**
 * Gets the temp conditions.
 * 
 * @return {Where[]}
 * @public
 */
BrowserView.prototype.getTempConditions = function() {
    return this.tempConditions_;
}

/**
 * Creates a new where object instance and stores it in tempConditions_
 *
 * @param {string} fieldName    The fieldName of the condition we want to create and store in tempConditions_
 * @public
 */
BrowserView.prototype.addTempCondition = function(fieldName) {
    
    // We do not need to specify the bLogic, operator and value since this will be set by the user in the Advanced Search
    var condition = new Where(fieldName);
    this.tempConditions_.push(condition);
}

/**
 * Removes the condition based on the index provided
 *
 * @param {int} index       The index of the condition that will be removed
 * @return {Where[]}  
 * @public
 */
BrowserView.prototype.removeTempCondition = function(index) {
    this.tempConditions_.splice(index, 1);
}

/**
 * Get where conditions
 *
 * @return {Where[]}
 * @public
 */
BrowserView.prototype.getConditions = function() {
    return this.conditions_;
}

/**
 * Get the sort order
 *
 * @return {string[]}
 * @public
 */
BrowserView.prototype.getOrderBy = function() {
    return this.orderBy_;
}

/**
 * Gets the temp conditions.
 * 
 * @return {string[]}
 * @public
 */
BrowserView.prototype.getTempOrderBy = function() {
    return this.tempOrderBy_;
}

/**
 * Pushes a new sort order object in tempConditions_
 *
 * @param {string} fieldName    The fieldName of sort order we want to create
 * @param {string} direction    The direction of the sort order we want to create
 * @public
 */
BrowserView.prototype.addTempOrderBy = function(fieldName, direction) {
    this.tempOrderBy_.push({
        field : fieldName,
        direction : direction
    });
}

/**
 * Removes the sort order based on the index provided
 *
 * @param {int} index       The index of the sort order that will be removed  
 * @public
 */
BrowserView.prototype.removeTempOrderBy = function(index) {
    this.tempOrderBy_.splice(index, 1);
}

/**
 * Get the table columns to view
 *
 * @return {string[]}
 * @public
 */
BrowserView.prototype.getTableColumns = function() {
    return this.tableColumns_;
}

/**
 * Get the table columns to view
 *
 * @return {string[]}
 * @public
 */
BrowserView.prototype.getTempColumns = function() {
    return this.tempTableColumns_;
}

/**
 * Pushes a new column in tempTableColumns_
 *
 * @param {string} fieldName    The fieldName of sort order we want to create
 * @public
 */
BrowserView.prototype.addTempColumn = function(fieldName) {
    this.tempTableColumns_.push({
        fieldName: fieldName
    });
}

/**
 * Removes the column based on the index provided
 *
 * @param {int} index       The index of the sort order that will be removed  
 * @public
 */
BrowserView.prototype.removeTempColumn = function(index) {
    this.tempTableColumns_.splice(index, 1);
}

module.exports = BrowserView;
