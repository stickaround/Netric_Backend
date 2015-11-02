/**
 * @fileOverview Define entity definition fields
 *
 * This class is a client side mirror of /lib/EntityDefinition/Field on the server side
 *
 * @author:	Sky Stebnicki, sky.stebnicki@aereus.com; 
 * 			Copyright (c) 2013 Aereus Corporation. All rights reserved.
 */
'use strict';

/**
 * Creates an instance of an entity definition field
 *
 * @param {Object} opt_data The definition data
 * @constructor
 */
var Field = function(opt_data) {

	var data = opt_data || new Object();

	/**
	 * Unique id if the field was loaded from a database
	 *
	 * @public
	 * @type {string}
	 */
	this.id = data.id || "";

	/**
	 * Field name (REQUIRED)
	 *
	 * No spaces or special characters allowed. Only alphanum up to 32 characters in length.
	 *
	 * @public
	 * @type {string}
	 */
	this.name = data.name || "";

	/**
	 * Human readable title
	 *
	 * If not set then $this->name will be used:
	 *
	 * @public
	 * @type {string}
	 */
	this.title = data.title || "";

	/**
	 * The type of field (REQUIRED)
	 *
	 * @public
	 * @type {string}
	 */
	this.type = data.type || "";

	/**
	 * The subtype
	 *
	 * @public
	 * @type {string}
	 */
	this.subtype = data.subtype || "";

	/**
	 * Optional mask for formatting value
	 *
	 * @public
	 * @type {string}
	 */
	this.mask = data.mask || "";

	/**
	 * Is this a required field?
	 *
	 * @public
	 * @var bool
	 */
	this.required = data.required || false;

	/**
	 * Is this a system defined field
	 *
	 * Only user fields can be deleted or edited
	 *
	 * @public
	 * @var bool
	 */
	this.system = data.system || false;

	/**
	 * If read only the user cannot set this value
	 *
	 * @public
	 * @var bool
	 */
	this.readonly = data.readonly || false;

	/**
	 * This field value must be unique across all objects
	 *
	 * @public
	 * @var bool
	 */
	this.unique = data.unique || false;

	/**
	 * Optional use_when condition will only display field when condition is met
	 *
	 * This is used for things like custom fields for posts where each feed will have special
	 * custom fields on a global object - posts.
	 *
	 * @public
	 * @type {string}
	 */
	this.useWhen = data.use_when || "";

	/**
	 * Default value to use with this field
	 *
	 * @public
	 * @var {array('on', 'value')}
	 */
	this.defaultVal = data.default_val || null;

	/**
	 * Optional values
	 *
	 * If an associative array then the id is the key, otherwise the value is used
	 *
	 * @public
	 * @var {Array}
	 */
	this.optionalValues = data.optional_values || null;

	/**
	 * Sometimes we need to automatically create foreign reference
	 *
	 * @public
	 * @type {bool}
	 */
	this.autocreate = data.autocreate || false;

	/**
	 * If autocreate then the base is used to define where to put the new referenced object
	 *
	 * @public
	 * @type {string}
	 */
	this.autocreatebase = data.autocreatebase || "";

	/**
	 * If autocreate then which field should we use for the name of the new object
	 *
	 * @public
	 * @type {string}
	 */
	this.autocreatename = data.autocreatename || "";

	/** 
	 * Add static types to a variable in 'this'
	 *
	 * @public
	 * @type {Object}
	 */
	this.types = Field.types;
}

/**
 * Static definition of all field types
 */
Field.types = {
	fkey : "fkey",
	fkeyMulti : "fkey_multi",
	object : "object",
	objectMulti : "object_multi",
	text : "text",
	bool : "bool",
	timestamp: "timestamp",
	number: "number"
}

/**
 * Get the default value for this vield
 *
 * @param {string} on The event to set default value on - default to null
 * @return {string}
 */
Field.prototype.getDefault = function(on)
{
	if (!this.defaultVal)
		return "";

	if (this.defaultVal.on == on)
	{
		if (this.defaultVal.value)
			return this.defaultVal.value;
	}

	return "";
}

module.exports = Field;