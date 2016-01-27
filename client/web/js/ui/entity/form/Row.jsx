/**
 * A row
 *
 * @jsx React.DOM
 */
'use strict';

var React = require('react');

/**
 * Row element
 */
var Row = React.createClass({

    render: function () {

        var displayRow = (
            <div className="entity-form-row">
                {this.props.children}
            </div>
        );
        var showif = this.props.xmlNode.getAttribute('showif');

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
            if(refValue === "null") {
                refValue = null;
            }

            // If showif is provided and it did not match with the entity field value, then lets not display the row
            if(this.props.entity.getValue(refField) != refValue) {
                displayRow = null;
            }
        }

        return (
            displayRow
        );
    }

});

module.exports = Row;