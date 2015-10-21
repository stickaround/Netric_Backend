/**
 * @fileOverview Handle defintion of entities.
 *
 * This class is a client side mirror of /lib/EntityDefinition
 *
 * @author:	Sky Stebnicki, sky.stebnicki@aereus.com; 
 * 			Copyright (c) 2014 Aereus Corporation. All rights reserved.
 */
'use strict';

var Field = require("./definition/Field");
var BrowserView = require("./BrowserView");

/**
 * Creates an instance of EntityDefinition
 *
 * @constructor
 * @param {Object} opt_data The definition data
 */
var Definition = function(opt_data) {

	var data = opt_data || new Object();
    if (!data.forms) data.forms = {};

	/**
	 * The object type for this definition
	 *
	 * @public
	 * @type {string}
	 */
	this.objType = data.obj_type || "";;

	/**
	 * The object type title
	 *
	 * @public
	 * @type {string}
	 */
	this.title = data.title || "";

	/**
	 * Recurrence rules
	 *
	 * @public
	 * @type {string}
	 */
	this.recurRules = data.recur_rules || null;

	/**
	 * Unique id of this object type
	 *
	 * @public
	 * @type {string}
	 */
	this.id = data.id || "";

	/**
	 * The current schema revision
	 *
	 * @public
	 * @type {int}
	 */
	this.revision = data.revision || "";

	/**
	 * Determine if this object type is private
	 *
	 * @public
	 * @type {bool}
	 */
	this.isPrivate = data.is_private || false;

	/**
	 * If object is heirarchial then this is the field that will store a reference to the parent
	 *
	 * @public
	 * @type {string}
	 */
	this.parentField = data.parent_field || "";

	/**
	 * Default field used for printing the name/title of objects of this type
	 *
	 * @public
	 * @type {string}
	 */
	this.listTitle = data.list_title || "";

	/**
	 * The base icon name used for this object.
	 *
	 * This may be over-ridden by individual objects for more dynamic icons, but this serves
	 * as the base in case the individual object did not yet define an icon.
	 *
	 * @public
	 * @type {string}
	 */
	this.icon = data.icon || "";

	/**
	 * Browser mode for the current user
	 *
	 * @public
	 * @type {string}
	 */
	this.browserMode = data.browser_mode || "";

	/**
	 * Is this a system level object
	 *
	 * @public
	 * @type {bool}
	 */
	this.system = data.system || "";;

	/**
	 * Fields associated with this object type
	 *
	 * For definition see EntityDefinition_Field::toArray on backend
	 *
	 * @private
	 * @type {Field[]}
	 */
	this.fields = new Array();

	/**
	 * Array of object views
	 *
	 * @private
	 * @type {BrowserView[]}
	 */
	this.views = new Array();

	/**
	 * Browser list blank state content
	 *
	 * This is used when there are no objects
	 *
	 * @private
	 * @type {string}
	 */
	this.browserBlankContent = data.browser_blank_content || "";

    /**
     * Forms for different devices and use cases
     *
     * These forms will UIML which is just XML
     * with netric specific tags for rendering an entity
     * form.
     *
     * @see https://aereus.netric.com/obj/infocenter_document/241
     * @public
     * @type {Object}
     */
    this.forms = {
		xlarge: data.forms.xlarge || "",
        large: data.forms.large || "",
        medium: data.forms.medium || "",
        small: data.forms.small || "",
        infobox: data.forms.infobox || ""
    };

	/*
	 * Initialize fields if set in the data object
	 */
	if (data.fields) {
		for (var fname in data.fields) {
			var field = new Field(data.fields[fname]);
			this.fields.push(field);
		}
	}

	/*
	 * Initialize views for this object definition
	 */
	if (data.views) {
		for (var i in data.views) {
			var view = new BrowserView();
			view.fromData(data.views[i]);
			this.views.push(view);
		}
	}

}

/**
 * Get a field by name
 *
 * @public
 * @param {Object} data Initialize values of this defintion based on data
 */
Definition.prototype.getField = function(fname) {
	for (var i in this.fields)
	{
		if (this.fields[i].name == fname)
			return this.fields[i];
	}
	return false;
}

/**
 * Get fields
 *
 * @public
 * @return {Field[]}
 */
Definition.prototype.getFields = function() {
	return this.fields;
}

/**
 * Get the field used for the name/list title
 *
 * @public
 * @return {string}
 */
Definition.prototype.getNameField = function() {
    return this.listTitle;
}

/**
 * Get views
 *
 * @public
 * @return {AntObjectBrowserView[]}
 */
Definition.prototype.getViews = function() {
	return this.views;
}

/**
 * Get browser blank state content
 *
 * @public
 * @return {string}
 */
Definition.prototype.getBrowserBlankContent = function() {
	return this.browserBlankContent;
}


/**
 * Get the default browser view
 *
 * @returns {BrowserView}
 */
Definition.prototype.getDefaultView = function() {

    // First check to see if there is a default view already setup
    for (var i in this.views) {
        if (this.views[i].default) {
            return this.views[i];
        }
    }

    // No default was found, construct a default
    var view = new BrowserView(this.objType);
    view.tableColumns_.push("id");
    // TODO: add a few more here
    return view;
}

module.exports = Definition;