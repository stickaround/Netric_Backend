/**
 * @fileOverview Base entity may be extended
 *
 * @author:	Sky Stebnicki, sky.stebnicki@aereus.com; 
 * 			Copyright (c) 2014 Aereus Corporation. All rights reserved.
 */
'use strict';

var Definition = require('./Definition');
var Recurrence = require('./Recurrence');
var File = require('./fileupload/File');
var events = require('../util/events');

/**
 * Entity represents a netric object
 *
 * @constructor
 * @param {Definition} entityDef Required definition of this entity
 * @param {Object} opt_data Optional data to load into this object
 */
var Entity = function (entityDef, opt_data) {

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
    this.objType = entityDef.objType;

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
     * This will be used to save or load the recurrence pattern
     *
     * @private
     * @type {Entity/Recurrence}
     */
    this.recurrencePattern_ = new Recurrence(this.objType);

    /**
     * Security
     *
     * @public
     * @type {Object}
     */
    this.security = {
        view: true,
        edit: true,
        del: true,
        childObject: new Array()
    };

    /**
     * Loading flag is used to indicate if the entity is pending a load
     *
     * This is useful for allowing "promise entities" where a request
     * has been made to fill an entity from the server, by an empty
     * entity with only the id and the objType set is returned to allow
     * the client to continue working.
     *
     * @public
     * @type {bool}
     */
    this.isLoading = false;

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
        console.error(err + JSON.stringify(data));
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

    // Handle Recurrence
    if (data['recurrence_pattern']) {
        this.recurrencePattern_.fromData(data['recurrence_pattern']);
    }

    // Trigger onload event to alert any observers that the data for this entity has loaded (batch)
    events.triggerEvent(this, "load");
}

/**
 * Return an object representing the actual values of this entity
 *
 * @return {}
 */
Entity.prototype.getData = function () {

    // Set the object type
    var retObj = {obj_type: this.objType};

    // Loop through all fields and set the value
    var fields = this.def.getFields();
    for (var i in fields) {
        var field = fields[i];
        var value = this.getValue(field.name);
        var valueNames = this.getValueName(field.name);

        retObj[field.name] = value;

        if (valueNames instanceof Array) {

            retObj[field.name + "_fval"] = {};
            for (var i in valueNames) {
                retObj[field.name + "_fval"][valueNames[i].key] = valueNames[i].value;
            }

        } else if (valueNames) {

            retObj[field.name + "_fval"] = {};
            retObj[field.name + "_fval"][value] = valueNames;

        }
    }

    // Get the recurrence pattern data if available
    if (this.recurrencePattern_ && this.recurrencePattern_.type > 0) {
        retObj.recurrence_pattern = this.recurrencePattern_.toData();
    }

    return retObj;
}

/**
 * Set the value of a field of this entity
 *
 * @param {string} name The name of the field to set
 * @param {mixed} value The value to set the field to
 * @param {string} opt_valueName The label if setting an fkey/object value
 * @return {bool} true on success, false on failure
 */
Entity.prototype.setValue = function (name, value, opt_valueName) {

    // Can't set a field without a name
    if (typeof name == "undefined")
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
    if (name.indexOf(".") != -1) {
        return;
    }

    // If a special property for this object also set
    if (name == "id") {
        this.id = value;
    }

    // A value of this entity is about to change
    this.dirty_ = true;

    // Set the value and optional valueName label for foreign keys
    this.fieldValues_[name] = {
        value: value,
        valueName: (valueName) ? valueName : null
    }

    // Trigger onchange event to alert any observers that this value has changed
    events.triggerEvent(this, "change", {fieldName: name, value: value, valueName: valueName});

}

/**
 * Add a value to a field that supports an array of values
 *
 * @param {string} name The name of the field to set
 * @param {mixed} value The value to set the field to
 * @param {string} opt_valueName The label if setting an fkey/object value
 */
Entity.prototype.addMultiValue = function (name, value, opt_valueName) {

    // Can't set a field without a name
    if (typeof name == "undefined")
        return;

    var valueName = opt_valueName || null;

    var field = this.def.getField(name);
    if (!field)
        return;

    // Handle type conversion
    value = this.normalizeFieldValue_(field, value);

    // Referenced object fields cannot be updated
    if (name.indexOf(".") != -1) {
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

    // First clear any existing values
    this.remMultiValue(name, value);

    // Set the value and optional valueName label for foreign keys
    this.fieldValues_[name].value.push(value);

    if (valueName) {
        this.fieldValues_[name].valueName.push({key: value, value: valueName});
    }

    // Trigger onchange event to alert any observers that this value has changed
    events.triggerEvent(this, "change", {fieldName: name, value: value, valueName: valueName});

}

/**
 * Remove a value to a field that supports an array of values
 *
 * @param {string} name The name of the field to set
 * @param {mixed} value The value to set the field to
 */
Entity.prototype.remMultiValue = function (name, value) {

    // Can't set a field without a name
    if (typeof name == "undefined")
        return;

    var field = this.def.getField(name);
    if (!field)
        return;

    // Handle type conversion
    value = this.normalizeFieldValue_(field, value);

    // Referenced object fields cannot be updated
    if (name.indexOf(".") != -1) {
        return;
    }

    // Look for the value
    if (!this.fieldValues_[name]) {
        return false;
    }

    // Remove the value
    for (var i in this.fieldValues_[name].value) {
        if (this.fieldValues_[name].value[i] == value) {
            // A value of this entity is about to change
            this.dirty_ = true;

            // Remove the value which should invalidate the valueName as well
            this.fieldValues_[name].value.splice(i, 1);

            // Remove the value name
            for (var j in this.fieldValues_[name].valueName) {
                if (this.fieldValues_[name].valueName[j].key == value) {
                    this.fieldValues_[name].valueName.splice(j, 1);
                    break;
                }
            }

            // Trigger onchange event to alert any observers that this value has changed
            events.triggerEvent(this, "change", {
                fieldName: name, value: this.getValue(name), valueName: null
            });

            return true;
        }
    }

    return false;
}

/**
 * Get the value for an object entity field
 *
 * @public
 * @param {string} name The unique name of the field to get the value for
 */
Entity.prototype.getValue = function (name) {
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
Entity.prototype.getValueName = function (name, opt_val) {
    // Get value from fieldValue
    if (this.fieldValues_[name]) {
        if (opt_val && this.fieldValues_[name].valueName instanceof Object) {
            if (this.fieldValues_[name].valueName[opt_val]) {
                return this.fieldValues_[name].valueName[opt_val];
            }
        } else if (opt_val && this.fieldValues_[name].valueName instanceof Array) {
            for (var i in this.fieldValues_[name].valueName) {
                if (this.fieldValues_[name].valueName[i].key == opt_val) {
                    return this.fieldValues_[name].valueName[i].value[opt_val];
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
Entity.prototype.getName = function () {
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
Entity.prototype.getSnippet = function () {
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
 * If there are people interacting with this entity get their names
 *
 * @return {string}
 */
Entity.prototype.getActors = function () {
    return "";
}

/**
 * Get relative timestamp
 *
 * @param {string} field Optional field to use (otherwise autodetect)
 * @param {bool} compress If true the compress to time if today, otherwise only show the date
 * @return {string}
 */
Entity.prototype.getTime = function (field, compress) {

    var fieldName = field || null;
    var compressDate = compress || false;
    var defField = this.def.getField(fieldName);

    var val = null;

    if (fieldName) {
        val = this.getValue(fieldName);
    } else if (this.getValue("ts_updated")) {
        val = this.getValue("ts_updated");
    } else if (this.getValue("ts_entered")) {
        val = this.getValue("ts_entered");
    }

    // Check to see if we should compress the date
    if (val && compressDate) {
        var dtVal = new Date(val);
        var today = new Date();

        if (dtVal.getFullYear() == today.getFullYear() &&
            dtVal.getMonth() == today.getMonth() &&
            dtVal.getDate() == today.getDate()) {

            // Show only the time if this is a timezone
            if (defField.type === "timestamp") {
                var hours = dtVal.getHours();
                var minutes = dtVal.getMinutes();
                var ampm = hours >= 12 ? 'pm' : 'am';
                hours = hours % 12;
                hours = hours ? hours : 12; // the hour '0' should be '12'
                minutes = minutes < 10 ? '0' + minutes : minutes;
                val = hours + ':' + minutes + ' ' + ampm;
            } else {
                val = "Today";
            }


        } else {

            // Show only the date
            val = (dtVal.getMonth() + 1) + "/" + dtVal.getDate() + "/" + dtVal.getFullYear();
        }
    }

    return val;

}

/**
 * Get the attachments saved in this entity
 *
 * @return {array}
 */
Entity.prototype.getAttachments = function () {

	var attachedFiles = [];

	// Check if this is an existing entity, before we load the attachments
	var files = this.getValueName('attachments');

	for (var idx in files) {
		// Create a file object
		if (files[idx].key) {
			attachedFiles[idx] = new File(files[idx])
		}
	}

	return attachedFiles;
}

/**
 * Normalize field values based on type
 *
 * @private
 * @param {EntityField} field The field we are normalizing
 * @param {mixed} value The value we need to normalize
 * @return {mixed}
 */
Entity.prototype.normalizeFieldValue_ = function (field, value) {

    if (field.type == field.types.bool) {
        switch (value) {
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

/**
 * Returns the recurrence pattern instance
 *
 * @public
 * @return {Entity/Recurrence}
 */
Entity.prototype.getRecurrence = function () {
    return this.recurrencePattern_;
}

/**
 * Sets the recurrence pattern
 *
 * @param {Entity/Recurrence}       Object instance of recurrence model
 * @public
 */
Entity.prototype.setRecurrence = function (recurrencePattern) {
    this.recurrencePattern_ = recurrencePattern;
}

module.exports = Entity;
