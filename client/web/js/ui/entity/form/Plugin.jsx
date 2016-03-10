/**
 * Entity Plugin
 *
 */
'use strict';

var React = require('react');
var plugins = require('../../../entity/plugins');

var Plugin = React.createClass({

    /**
     * Expected props
     */
    propTypes: {
        /**
         * Current xml node level
         *
         * @type {XMLNode}
         */
        xmlNode: React.PropTypes.object,

        /**
         * Entity being edited
         *
         * @type {entity\Entity}
         */
        entity: React.PropTypes.object,

        /**
         * Generic object used to pass events back up to controller
         *
         * @type {Object}
         */
        eventsObj: React.PropTypes.object,

        /**
         * Flag indicating if we are in edit mode or view mode
         *
         * @type {bool}
         */
        editMode: React.PropTypes.bool
    },

    render: function () {

        var xmlNode = this.props.xmlNode;
        var pluginName = xmlNode.getAttribute('name');
        var componentName = this.props.entity.def.objType + "." + pluginName;
        var componentGlobal = "global." + pluginName; // Try to get the plugin in the global folder

        // Check if there is a specific plugin for objType or a global plugin for all entities
        var component = netric.getObjectByName(componentName, null, plugins.List) || netric.getObjectByName(componentGlobal, null, plugins.List);

        if (!component) {
            throw "Plugin named " + componentName + " does not exist";
        }

        var reactElement;
        try {
            reactElement = React.createElement(component, {
                xmlNode: this.props.xmlNode,
                eventsObj: this.props.eventsObj,
                entity: this.props.entity,
                editMode: this.props.editMode
            });
        } catch (e) {
            console.error("Could not create plugin component: " + componentName + ":" + e);
        }

        return reactElement;
    }
});

module.exports = Plugin;