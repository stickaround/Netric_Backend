/**
 * @fileOverview Base entity may be extended
 *
 * @author:	Sky Stebnicki, sky.stebnicki@aereus.com; 
 * 			Copyright (c) 2014 Aereus Corporation. All rights reserved.
 */
alib.declare("netric.entity.Entity");

alib.require("netric");
alib.require("netric.entity.Definition");

/**
 * Make sure entity namespace is initialized
 */
netric.entity = netric.entity || {};

/**
 * Entity represents a netric object
 *
 * @constructor
 * @param {netric.entity.Definition} entityDef Required definition of this entity
 * @param {Object} opt_data Optional data to load into this object
 */
netric.entity.Entity = function(entityDef, opt_data) {

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
	 * @type {netric.entity.Definition}
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
netric.entity.Entity.prototype.loadData = function (data) {
	
	// Data is a required param and we should fail if called without it
	if (!data) {
		throw "'data' is a required param to loadData into an entity";
	}

	// Make sure that the data passed is valid data
	if (!data.id || !data.obj_type) {
		var err = "Data passed is not a valid entity";
		console.log(err + JSON.strigify(data));
		throw err;
	}

	// First set common public properties
	this.id = data.id.toString();
	this.objType = data.obj_type;

	// Now set all the values for this entity
}

/**
 * Set the value of a field of this entity
 *
 * @param {string} name The name of the field to set
 * @param {mixed} value The value to set the field to
 * @param {string} opt_valueName The label if setting an fkey/object value
 */
netric.entity.Entity.prototype.setValue = function(name, value, opt_valueName) {
    
    // Can't set a field without a name
    if(typeof name == "undefined")
        return;

	var valueName = opt_valueName || null;

    var field = this.def.getField(name);
	if (!field)
		return;

	// Check if this is a multi-field
	if (field.type == field.types.fkeyMulti || field.type == field.types.objectMulti) {
		if (value instanceof Array) {
			for (var j in value) {
				this.setMultiValue(name, value[j]);
			}
		} else {
			this.setMultiValue(name, value, valueName);
		}

		return true;
	}

	// Handle bool conversion
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
	alib.events.triggerEvent(this, "fieldchange", {fieldName: name, value:value, valueName:valueName});
    
}

/**
 * Get the value for an object entity field
 * 
 * @public
 * @param {string} name The unique name of the field to get the value for
 */
netric.entity.Entity.prototype.getValue = function(name) {
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
netric.entity.Entity.prototype.getValueName = function(name, opt_val) {
	// Get value from fieldValue
    if (this.fieldValues_[name]) {
    	if (opt_val && this.fieldValues_[name].valueName instanceof Array) {
    		for (var i in this.fieldValues_[name].valueName) {
    			if (this.fieldValues_[name].valueName[i].key == name) {
    				return this.fieldValues_[name].valueName[i].value;
    			}
    		}
    	} else {
    		return this.fieldValues_[name].valueName;    		
    	}
    }
	/*
    var field = this.getFieldByName(name);
    if (field && field.type == "alias")
    {
        if (!val)
            var val = this.getValue(name);
        return this.getValue(val); // Get aliased value
    }

    if (field.type == "object" || field.type == "fkey" || field.type == "object_multi" || field.type == "fkey_multi")
    {
        for (var i = 0; i < this.values.length; i++)
        {
            if (this.values[i][0] == name)
            {
                if (val) // multival
                {
                    for (var m = 0; m < this.values[i][1].length; m++)
                    {
                        if (this.values[i][1][m] == val && this.values[i][2])
                            return this.values[i][2][m];
                    }
                }
                else
                {
                    if (this.values[i][2]!=null && this.values[i][2]!="null")
                        return this.values[i][2];
                }
            }
        }
    }
	else if (field.optional_vals.length)
	{
		for (var i = 0 ; i < field.optional_vals.length; i++)
		{
			if (field.optional_vals[i][0] == this.getValue(name))
			{
				return field.optional_vals[i][1];
			}
		}
	}
    else
    {
        return this.getValue(name);
    }
    */
    
    return "";
}

/**
 * Get the human readable name of this object
 *
 * @return {string} The name of this object based on common name fields like 'name' 'title 'subject'
 */
netric.entity.Entity.prototype.getName = function()
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