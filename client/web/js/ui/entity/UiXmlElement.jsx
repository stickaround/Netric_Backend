/**
 * Render an entity form from UIML
 *

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
};

/**
 * Convert UIML into a UI Form
 */
var UiXmlElement = React.createClass({

    /**
     * Expected props
     */
    propTypes: {
        /**
         * Current element node level
         *
         * @type {entity/form/Node}
         */
        elementNode: React.PropTypes.object.isRequired,

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

        var childElements = [];

        // Process through the child nodes of the elementNode
        this.props.elementNode.childNodes.map(function(childNode, idx) {

            /*
             * If we are in a 'tabs' element, then children should be a tab and
             * not another UiXmlElement because Chamel tabs only support a chamel.Tab
             * as children of a chamel.Tabs container.
             */
            if (this.props.elementNode.nodeName === "tabs") {
                var label = childNode.getAttribute('name');

                childElements.push(
                    <Tab key={idx} label={label}>
                        <UiXmlElement
                            key={idx}
                            elementNode={childNode}
                            entity={this.props.entity}
                            eventsObj={this.props.eventsObj}
                            editMode={this.props.editMode} />
                    </Tab>
                );
            } else {
                childElements.push(
                    <UiXmlElement
                        key={idx}
                        elementNode={childNode}
                        entity={this.props.entity}
                        eventsObj={this.props.eventsObj}
                        editMode={this.props.editMode}
                        />
                );
            }
        }.bind(this))

        /*
         * Try to render the dynamic component and pass childElements,
         * but if the component is not defined for the given element
         * then throw an exception because this should never happen.
         */
        var component = netric.getObjectByName(this.props.elementNode.generateComponentName(), null, formElements);
        var reactElement;
        if (component != null) {
            try {
                reactElement = React.createElement(component, this.props, childElements);
            } catch (e) {
                console.error("Could not create component: " + componentName + ":" + e);
            }
        } else {

            // Let client know we have a problem with the UIML
            throw 'Unsupported element type in UIML: ' + this.props.elementNode.nodeName;
        }

        return reactElement;
    }
});

module.exports = UiXmlElement;
