/**
 * Text component
 *
 * @jsx React.DOM
 */
'use strict';

var React = require('react');

/**
 * Text Element
 */
var Text = React.createClass({

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

        var textDisplay = (<div className="entity-form-field-value">{this.props.children}{fieldValue}</div>);
        if (showif) {

            /*
             * Evaluate the showif if it is provided.
             * Lets split the show if using the "=" delimter.
             * The first part will be the field and the second part will be its value.
             * Sample showif: type=2
             */
            var parts = showif.split("=");
            var refField = parts[0];
            var refValue = parts[1];

            // If refValue has a string value of null, then lets convert it to null value
            if (refValue === "null") {
                refValue = null;
            }

            // If showif is provided and it did not match with the entity field value, then lets not display the row
            if (this.props.entity.getValue(refField) != refValue) {
                textDisplay = null;
            }
        }

        return (
            textDisplay
        );
    },
});

module.exports = Text;