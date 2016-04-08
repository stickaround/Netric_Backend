/**
 * @fileOverview Define the xml node object data
 *
 * Use the public function loadXmlData() to import the xml node data into this node model
 * Node model can also set and get its attributes just like in xml node document
 *  Use public function setAttribute() and getAttribute()
 *
 * @author =    Marl Tumulak; marl.tumulak@aereus.com;
 *            Copyright (c) 2016 Aereus Corporation. All rights reserved.
 */
'use strict';

/**
 * Creates an instance of a node object
 *
 * @param {string} nodeName The name of the node we are creating
 * @constructor
 */
var Node = function (nodeName) {

    /**
     * The name of the node
     *
     * @type {string|null}
     */
    this.nodeName = nodeName || null;

    /**
     * The value/element inside the node
     *
     * e.g. <header>My Node Value</> (this.nodeValue will get the 'My Node Value' string)
     *
     * @type {null}
     */
    this.nodeValue = null;

    /**
     * Here we will store the xml node attributes
     *
     * @type {Array}
     * @private
     */
    this._attributes = new Array;

    /**
     * This is where we store the child nodes if it is available
     *
     * @type {Array}
     */
    this.childNodes = new Array;
}

/**
 * Load xml data from a data object to define this node model
 *
 * @param {object} data The data object where we map its values and assign it to this node model variables
 * @private
 */
Node.prototype.loadXmlData = function (data) {

    // Data is a required param and we should fail if called without it
    if (!data) {
        throw "'data' is a required param to load the xml data";
    }

    // If we have a child node, then let's consider it as the nodeValue
    if (data.childNodes && data.childNodes.length) {
        this.nodeValue = data.childNodes[0].nodeValue;
    }

    // If we have xml data attributes, then let's map it and store in the _attributes
    if (data.attributes && data.attributes.length) {
        for (var idx in data.attributes) {
            let attribute = data.attributes[idx];

            // Make sure that we have and attribute name and value before setting
            if (attribute.name && attribute.value) {
                this.setAttribute(attribute.name, attribute.value);
            }
        }
    }
}

/**
 * Function that will generate a component name based on the node model name
 *
 * Basically, we will just format the nodeName as camel case and remove the underscores
 * This will be used to determine what type of form fields to render
 * e.g. (Row, Tab, Field, StatusUpdate) refer to ui/entity/form for more fields
 *
 * @return {string} The generated component name
 * @public
 */
Node.prototype.generateComponentName = function () {

    // Set the default value of componentName to an empty string
    let componentName = "";

    // Make sure we have a valid nodeName before we generate a component name
    if(!this.nodeName || typeof this.nodeName === 'undefined') {
        return componentName;
    }

    let parts = this.nodeName.split("_");

    for (var idx in parts) {

        // Convert to uc first
        let firstChar = parts[idx].charAt(0).toUpperCase();
        componentName += firstChar + parts[idx].substr(1);
    }

    return componentName;
}

/**
 * Function that will get the attribute value of the node model
 *
 * @param {string} name The name of the attribute where we want to get its value
 * @returns {mixed} The attribute value
 * @public
 */
Node.prototype.getAttribute = function (name) {

    // Make sure that attribute name is provided before getting the attribute value
    if (typeof name == "undefined") {
        return null;
    }

    // Get value from fieldValue
    if (this._attributes[name]) {
        return this._attributes[name];
    }

    return null;
}

/**
 * Function that will set an attribute for this node model
 *
 * @param {string} name The name of the attribute that we are going to save
 * @param {mixed} value The value of the attribute to be saved
 * @public
 */
Node.prototype.setAttribute = function (name, value) {

    // Can't set an attribute without a name
    if (typeof name == "undefined") {
        return;
    }


    this._attributes[name] = value;
}

module.exports = Node;
