/**
 * Render an entity form from UIML
 *
 * @jsx React.DOM
 */
'use strict';

var React = require('react');
var netric = require("../../base.js");
var Chamel = require('chamel');
var Tab = Chamel.Tab;

// Form elements used in the UIML
var formElements = {
    Column: require("./form/Column.jsx"),
    Field: require("./form/Field.jsx"),
    Fieldset: require("./form/Fieldset.jsx"),
    Form: require("./form/Form.jsx"),
    Objectsref: require("./form/Objectsref.jsx"),
    Row: require("./form/Row.jsx"),
    Tab: require("./form/Tab.jsx"),
    Tabs: require("./form/Tabs.jsx"),
    Helptour: require("./form/Helptour.jsx"),
    AllAdditional: require("./form/AllAdditional.jsx"),
    Recurrence: require("./form/Recurrence.jsx"),
    Attachments: require("./form/Attachments.jsx"),
    Comments: require("./form/Comments.jsx"),
    Plugin: require("./form/Plugin.jsx"),
    Header: require("./form/Header.jsx"),
    StatusUpdate: require("./form/StatusUpdate.jsx"),
    Text: require("./form/Text.jsx"),
    Label: require("./form/Label.jsx"),
    Members: require("./form/Members.jsx")
};

/**
 * Convert UIML into a UI Form
 */
var UiXmlElement = React.createClass({

    /**
     * Expected props
     */
    propTypes: {
        xmlNode: React.PropTypes.object,
        entity: React.PropTypes.object,
        eventsObj: React.PropTypes.object,
        editMode: React.PropTypes.bool
    },

    render: function() {

        var xmlNode = this.props.xmlNode;
        var xmlChildNodes = xmlNode.childNodes;

        // Process through children
        var childElements = [];
        for (var i = 0; i < xmlChildNodes.length; i++) {
            var childNode = xmlChildNodes[i];
            if (childNode.nodeType == 1) {
                /*
                 * If we are in a 'tabs' elment, then children should be a tab and
                 * not another UiXmlElement because Chamel tabs only support a chamel.Tab
                 * as children of a chamel.Tabs container.
                 */
                if (xmlNode.nodeName === "tabs") {
                    var label = childNode.getAttribute('name');

                    childElements.push(
                        <Tab key={i} label={label}>
                            <UiXmlElement
                                key={i}
                                xmlNode={childNode}
                                entity={this.props.entity}
                                eventsObj={this.props.eventsObj}
                                editMode={this.props.editMode} />
                        </Tab>
                    );
                } else {
                    childElements.push(
                        <UiXmlElement
                            key={i}
                            xmlNode={childNode}
                            entity={this.props.entity}
                            eventsObj={this.props.eventsObj}
                            editMode={this.props.editMode}
                        />
                    );
                }
            }
        }

        // Process current node and create element at this level
        var componentName = "";
        var parts = xmlNode.nodeName.split("_");
        for (var i in parts) {
            // Convert to uc first
            var f = parts[i].charAt(0).toUpperCase();
            componentName += f + parts[i].substr(1);
        }

        /*
         * Try to render the dynamic component and pass childElements,
         * but if the component is not defined for the given element
         * then throw an exception because this should never happen.
         */
        var component = netric.getObjectByName(componentName, null, formElements);
        var reactElement;
        if (component != null) {
            try {
                reactElement = React.createElement(component, this.props, childElements);
            } catch (e) {
                console.error("Could not create component: " + componentName + ":" + e);
            }
        } else {
            // Let client know we have a problem with the UIML
            throw 'Unsupported element type in UIML: ' + xmlNode.nodeName;
        }

        return reactElement;
    }
});

module.exports = UiXmlElement;
