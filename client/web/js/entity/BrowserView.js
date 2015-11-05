/**
 * @fileOverview A view object used to define how a collection of entities is displayed to users
 *
 * @author: Sky Stebnicki, sky.stebnicki@aereus.com;
 *          Copyright (c) 2015 Aereus Corporation. All rights reserved.
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
    this.id = null;

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
    this.filterKey      = "";
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
    this.system = data.system || data.f_system;
    this.default = (data.default === true) ? true : false;
    this.userId = data.user_id || null;
    this.teamId = data.team_id || null;
    this.reportId = data.report_id || null;
    this.scope = data.scope || "";

    if (data.filter_key) {
        this.filterKey = data.filter_key;
    }

    // Setup columns to display for a table view
    for (var i in data.table_columns) {
        this.tableColumns_.push(data.table_columns[i]);
    }

    for (var i in data.conditions) {
        var where = new Where(data.conditions[i].field_name);
        where.bLogic = data.conditions[i].blogic;
        where.operator = data.conditions[i].operator;
        where.value = data.conditions[i].value;
        this.conditions_.push(where);
    }

    var orderBy = data.sort_order || data.order_by;

    for (var i in orderBy)
    {
        this.orderBy_.push({
            field : orderBy[i].field_name,
            direction : orderBy[i].order || orderBy[i].direction
        });
    }
}

/**
 * Get the object view data
 *
 * @return {Object} Data of Browser View
 */
BrowserView.prototype.getData = function() {

    var data = {
        id: this.id,
        obj_type: this.objType,
        name: this.name,
        description: this.description,
        f_system: this.system,
        f_default: this.default,
        user_id: this.userId,
        team_id: this.teamId,
        report_id: this.reportId,
        scope: this.scope,
        filter_key: this.filterKey,
    };
    
    // Table Columns data
    data.table_columns = [];
    for (var idx in this.tableColumns_) {
        data.table_columns.push(this.tableColumns_[idx]);
    }
    
    // Conditions data
    data.conditions = [];
    for (var idx in this.conditions_) {
        data.conditions.push(this.conditions_[idx].toData());
    }
    
    // Order By data
    data.order_by = [];
    for (var idx in this.orderBy_) {
        data.order_by.push({
            field_name: this.orderBy_[idx].field,
            direction: this.orderBy_[idx].direction
        });
    }
    
    return data;
}

/**
 * Creates a new where object instance and stores it in conditions_
 *
 * @param {string} fieldName    The fieldName of the condition we want to create and store in Conditions_
 * @public
 */
BrowserView.prototype.addCondition = function(fieldName) {
    
    // We do not need to specify the bLogic, operator and value since this will be set by the user in the Advanced Search
    var condition = new Where(fieldName);
    this.conditions_.push(condition);
}

/**
 * Removes the condition based on the index provided
 *
 * @param {int} index       The index of the condition that will be removed
 * @return {Where[]}  
 * @public
 */
BrowserView.prototype.removeCondition = function(index) {
    this.conditions_.splice(index, 1);
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
 * Pushes a new orderBy object in orderBy_
 *
 * @param {string} fieldName    The fieldName of sort order we want to create
 * @param {string} direction    The direction of the sort order we want to create
 * @public
 */
BrowserView.prototype.addOrderBy = function(fieldName, direction) {
    this.orderBy_.push({
        field : fieldName,
        direction : direction
    });
}

/**
 * Removes the orderBy based on the index provided
 *
 * @param {int} index       The index of the orderBy that will be removed  
 * @public
 */
BrowserView.prototype.removeOrderBy = function(index) {
    this.orderBy_.splice(index, 1);
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
 * Pushes a new column in tableColumns_
 *
 * @param {string} fieldName    The column name we want to create
 * @public
 */
BrowserView.prototype.addTableColumn = function(fieldName) {
    this.tableColumns_.push(fieldName);
}

/**
 * Removes the column based on the index provided
 *
 * @param {string} fieldName    Column name that will be saved based on the index provided
 * @param {int} index           The index of column that will be removed  
 * @public
 */
BrowserView.prototype.updateTableColumn = function(fieldName, index) {
    this.tableColumns_[index] = fieldName
}

/**
 * Removes the column based on the index provided
 *
 * @param {int} index       The index of column that will be removed  
 * @public
 */
BrowserView.prototype.removeTableColumn = function(index) {
    this.tableColumns_.splice(index, 1);
}

/**
 * Get the table columns to view
 *
 * @return {string[]}
 */
BrowserView.prototype.getTableColumns = function() {
    return this.tableColumns_;
}

/**
 * Set the Id of the browser view
 *
 * @param {int} id       Id of the browser view
 */
BrowserView.prototype.setId = function(id) {
    this.id = id;
}

/**
 * Set the objType of the browser view
 *
 * @param {string} objType       The name of the object type
 */
BrowserView.prototype.setObjType = function(objType) {
    this.objType = objType;
}

module.exports = BrowserView;
