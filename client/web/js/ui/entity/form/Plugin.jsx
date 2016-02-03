/**
 * Entity Plugin
 *
 * @jsx React.DOM
 */
'use strict';

var React = require('react');

var _plugins = {
    task: {
        LogTime: require('../plugin/task/LogTime.jsx')
    },
    global: {
        Members: require('../plugin/global/Members.jsx')
    }
}

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

    render: function() {

        var xmlNode = this.props.xmlNode;
        var pluginName = xmlNode.getAttribute('name');;
        var componentName = this.props.entity.def.objType + "." + pluginName;
        var componentGlobal = "global." + pluginName; // Try to get the plugin in the global folder

        var component = netric.getObjectByName(componentName, null, _plugins) || netric.getObjectByName(componentGlobal, null, _plugins);

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