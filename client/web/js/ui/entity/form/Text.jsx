/**
 * Text Label component
 *
 * @jsx React.DOM
 */
'use strict';

var React = require('react');
var ShowIfFilter = require("../../mixins/ShowIfFilter.jsx");

/**
 * Text Label Element
 *
 * This will basically display the field value as label. There will be no input field displayed in this element
 */
var Text = React.createClass({

    mixins: [ShowIfFilter],

    /**
     * Expected props
     */
    propTypes: {
        xmlNode: React.PropTypes.object,
        entity: React.PropTypes.object,
        eventsObj: React.PropTypes.object,
        editMode: React.PropTypes.bool
    },

    /**
     * Render the component
     */
    render: function () {
        var xmlNode = this.props.xmlNode;
        var fieldName = xmlNode.getAttribute('field');
        var fieldValue = this.props.entity.getValue(fieldName);
        var showif = this.props.xmlNode.getAttribute('showif');

        var textDisplay = (<div className="entity-form-field-text">{this.props.children}{fieldValue}</div>);
        if (showif) {

            // If ::evaluateShowIf() returns false, it means that the showif did not match the filter specified
            if(!this.evaluateShowIf(showif)) {
                textDisplay = null;
            }
        }

        return (
            textDisplay
        );
    },
});

module.exports = Text;