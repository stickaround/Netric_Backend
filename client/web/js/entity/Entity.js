/**
 * @fileOverview Base entity may be extended
 *
 * @author:	Sky Stebnicki, sky.stebnicki@aereus.com; 
 * 			Copyright (c) 2014 Aereus Corporation. All rights reserved.
 */
'use strict';

var Definition = require("./Definition");

/**
 * Entity represents a netric object
 *
 * @constructor
 * @param {Definition} entityDef Required definition of this entity
 * @param {Object} opt_data Optional data to load into this object
 */
var Entity = function(entityDef, opt_data) {

	/** 
	 * Unique id of this object entity
	 *
	 * @public
	 * @type {string}
	 */
	this.id = "";

	/** 
	 * The object type of this entity
	 *
	 * @public
	 * @type {string}
	 */
	this.objType = "";

	/**
	 * Entity definition
	 *
	 * @public
	 * @type {Definition}
	 */
	this.def = entityDef;

	/**
	 * Flag to indicate fieldValues_ have changed for this entity
	 *
	 * @private
	 * @type {bool}
	 */
	this.dirty_ = false;

	/**
	 * Field values
	 * 
	 * @private
	 * @type {Object}
	 */
	this.fieldValues_ = new Object();

	/**
	 * Security
	 * 
	 * @public
	 * @type {Object}
	 */
	this.security = {
		view : true,
		edit : true,
		del : true,
		childObject : new Array()
	};

	// If data has been passed then load it into this entity
	if (opt_data) {
		this.loadData(opt_data);
	}
}

/**
 * Load data from a data object in array form
 * 
 * If we are loading in array form that means that properties are not camel case
 * 
 * @param {Object} data
 */
Entity.prototype.loadData = function (data) {
	
	// Data is a required param and we should fail if called without it
	if (!data) {
		throw "'data' is a required param to loadData into an entity";
	}

	// Make sure that the data passed is valid data
	if (!data.id || !data.obj_type) {
		var err = "Data passed is not a valid entity";
		console.log(err + JSON.stringify(data));
		throw err;
	}

	// First set common public properties
	this.id = data.id.toString();
	this.objType = data.obj_type;

	// Now set all the values for this entity
	for (var i in data) {

		var field = this.def.getField(i);
		var value = data[i];
		
		// Skip over non existent fields
		if (!field) {
			continue;
		}

		// Check to see if _fval cache was set
		var valueName = (data[i + "_fval"]) ? data[i + "_fval"] : null;

		// Set the field values
		if (field.type == field.types.fkeyMulti || field.type == field.types.objectMulti) {
			if (value instanceof Array) {
				for (var j in value) {
					var vName = (valueName && valueName[value[j]]) ? valueName[value[j]] : null;
					this.addMultiValue(i, value[j], vName);
				}
			} else {
				var vName = (valueName && valueName[value]) ? valueName[value] : null;
				this.addMultiValue(i, value, vName);
			}
		} else {
			this.setValue(i, value, valueName);
		}
		
	}
}

/**
 * Set the value of a field of this entity
 *
 * @param {string} name The name of the field to set
 * @param {mixed} value The value to set the field to
 * @param {string} opt_valueName The label if setting an fkey/object value
 * @return {bool} true on success, false on failure
 */
Entity.prototype.setValue = function(name, value, opt_valueName) {
    
    // Can't set a field without a name
    if(typeof name == "undefined")
        return;

	var valueName = opt_valueName || null;

    var field = this.def.getField(name);
	if (!field)
		return false;

	// Check if this is a multi-value field
	if (field.type == field.types.fkeyMulti || field.type == field.types.objectMulti) {
		throw "Call addMultiValue to handle values for fkey_multi and object_mulit";
	}

	// Handle type conversion
	value = this.normalizeFieldValue_(field, value);
    
    // Referenced object fields cannot be updated
    if (name.indexOf(".")!=-1) {
        return;
    }

    // A value of this entity is about to change
    this.dirty_ = true;

    // Set the value and optional valueName label for foreign keys
    this.fieldValues_[name] = {
    	value: value,
    	valueName: (valueName) ? valueName : null
    }

    // Trigger onchange event to alert any observers that this value has changed
	alib.events.triggerEvent(this, "change", {fieldName: name, value:value, valueName:valueName});
    
}

/**
 * Add a value to a field that supports an array of values
 *
 * @param {string} name The name of the field to set
 * @param {mixed} value The value to set the field to
 * @param {string} opt_valueName The label if setting an fkey/object value
 */
Entity.prototype.addMultiValue = function(name, value, opt_valueName) {
    
    // Can't set a field without a name
    if(typeof name == "undefined")
        return;

	var valueName = opt_valueName || null;

    var field = this.def.getField(name);
	if (!field)
		return;

	// Handle type conversion
	value = this.normalizeFieldValue_(field, value);
    
    // Referenced object fields cannot be updated
    if (name.indexOf(".")!=-1) {
        return;
    }

    // A value of this entity is about to change
    this.dirty_ = true;

    // Initialize arrays if not set
    if (!this.fieldValues_[name]) {
    	this.fieldValues_[name] = {
	    	value: [],
	    	valueName: []
	    }
    }

    // Set the value and optional valueName label for foreign keys    
	this.fieldValues_[name].value.push(value);

	if (valueName) {
		this.fieldValues_[name].valueName.push({key:value, value:valueName});
	}

    // Trigger onchange event to alert any observers that this value has changed
	alib.events.triggerEvent(this, "change", {fieldName: name, value:value, valueName:valueName});
    
}

/**
 * Get the value for an object entity field
 * 
 * @public
 * @param {string} name The unique name of the field to get the value for
 */
Entity.prototype.getValue = function(name) {
    if (!name)
        return null;

    // Get value from fieldValue
    if (this.fieldValues_[name]) {
    	return this.fieldValues_[name].value;
    }  
    
    return null;
}

/**
 * Get the name/lable of a key value
 * 
 * @param {string} name The name of the field
 * @param {val} opt_val If querying *_multi type values the get the label for a specifc key
 * @reutrn {string} the textual representation of the key value
 */
Entity.prototype.getValueName = function(name, opt_val) {
	// Get value from fieldValue
    if (this.fieldValues_[name]) {
    	if (opt_val && this.fieldValues_[name].valueName instanceof Array) {
    		for (var i in this.fieldValues_[name].valueName) {
    			if (this.fieldValues_[name].valueName[i].key == opt_val) {
    				return this.fieldValues_[name].valueName[i].value;
    			}
    		}
    	} else {
    		return this.fieldValues_[name].valueName;    		
    	}
    }
    
    return "";
}

/**
 * Get the human readable name of this object
 *
 * @return {string} The name of this object based on common name fields like 'name' 'title 'subject'
 */
Entity.prototype.getName = function()
{
    if (this.getValue("name")) {
        return this.getValue("name");
    } else if (this.getValue("title")) {
        return this.getValue("title");
    } else if (this.getValue("subject")) {
        return this.getValue("subject");
    } else if (this.getValue("first_name") || this.getValue("last_name")) {
    	return (this.getValue("first_name")) 
    		? this.getValue("first_name") + " " + this.getValue("last_name")
    		: this.getValue("last_name");
    } else if (this.getValue("id")) {
        return this.getValue("id");
    } else {
        return "";
    }
}

/**
 * Get a snippet of this object
 *
 * @return {string}
 */
Entity.prototype.getSnippet = function()
{
	var snippet = "";

    if (this.getValue("notes")) {
        snippet = this.getValue("notes");
    } else if (this.getValue("description")) {
        snippet = this.getValue("description");
    } else if (this.getValue("body")) {
        snippet = this.getValue("body");
    }

    // TODO: strip all tags and new lines

    return snippet;
}

/**
 * Normalize field values based on type
 *
 * @private
 * @param {EntityField} field The field we are normalizing
 * @param {mixed} value The value we need to normalize
 * @return {mixed}
 */
Entity.prototype.normalizeFieldValue_ = function(field, value) {

	if (field.type == field.types.bool) {
		switch (value)
		{
		case 1:
		case 't':
		case 'true':
			value = true;
			break;
		case 0:
		case 'f':
		case 'false':
			value = false;
			break;
		}
	}

	return value;
}

module.exports = Entity;
